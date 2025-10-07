<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;

class SecurityEventsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'security.events';

    public function fetchData(): array
    {
        return [
            'events' => $this->getSecurityEvents(),
            'summary' => $this->getSummary(),
            'fail2ban' => $this->getFail2banStatus(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getSecurityEvents(): array
    {
        $events = [];

        // SSH failed login attempts
        $sshEvents = $this->getSshFailedLogins();
        $events = array_merge($events, $sshEvents);

        // Web authentication failures (Laravel)
        $webEvents = $this->getWebAuthFailures();
        $events = array_merge($events, $webEvents);

        // Suspicious IP activity
        $suspiciousIps = $this->getSuspiciousActivity();
        $events = array_merge($events, $suspiciousIps);

        // Sort by timestamp (newest first)
        usort($events, fn($a, $b) => strtotime($b['timestamp'] ?? 'now') <=> strtotime($a['timestamp'] ?? 'now'));

        // Limit to last 30 events
        return array_slice($events, 0, 30);
    }

    protected function getSshFailedLogins(): array
    {
        $events = [];
        
        // Check auth.log for failed SSH attempts
        $result = ReadonlyCommand::run("grep 'Failed password' /var/log/auth.log 2>/dev/null | tail -n 20");
        
        if (!$result['success']) {
            return $events;
        }

        $lines = explode("\n", $result['output']);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Parse SSH failed login
            // Format: "Jan 15 12:34:56 hostname sshd[12345]: Failed password for username from 1.2.3.4 port 12345 ssh2"
            if (preg_match('/(\w+\s+\d+\s+\d{2}:\d{2}:\d{2}).*Failed password for (\w+) from ([\d.]+)/', $line, $matches)) {
                $timestamp = $this->parseAuthLogTimestamp($matches[1]);
                
                $events[] = [
                    'type' => 'ssh_failed_login',
                    'severity' => 'warning',
                    'user' => $matches[2],
                    'ip' => $matches[3],
                    'message' => "SSH login feilet for bruker '{$matches[2]}'",
                    'timestamp' => $timestamp->toIso8601String(),
                    'timestamp_formatted' => $timestamp->format('d.m.Y H:i:s'),
                    'relative_time' => $timestamp->diffForHumans(),
                ];
            }
        }

        return $events;
    }

    protected function getWebAuthFailures(): array
    {
        $events = [];
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return $events;
        }

        // Look for authentication failures in Laravel log
        $result = ReadonlyCommand::run("grep -i 'authentication\\|login.*fail\\|unauthorized' {$logPath} 2>/dev/null | tail -n 20");
        
        if (!$result['success']) {
            return $events;
        }

        $lines = explode("\n", $result['output']);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Parse Laravel log entry
            if (preg_match('/\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2})/', $line, $matches)) {
                $timestamp = Carbon::parse($matches[1]);
                
                // Try to extract IP if present
                $ip = 'Unknown';
                if (preg_match('/([\d.]+)/', $line, $ipMatches)) {
                    $ip = $ipMatches[1];
                }

                $events[] = [
                    'type' => 'web_auth_failure',
                    'severity' => 'warning',
                    'ip' => $ip,
                    'message' => 'Web autentisering feilet',
                    'timestamp' => $timestamp->toIso8601String(),
                    'timestamp_formatted' => $timestamp->format('d.m.Y H:i:s'),
                    'relative_time' => $timestamp->diffForHumans(),
                ];
            }
        }

        return $events;
    }

    protected function getSuspiciousActivity(): array
    {
        $events = [];
        
        // Check for 404 spam, SQL injection attempts, etc in access log
        $patterns = [
            'sql' => '(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|--)',
            'xss' => '(<script|javascript:|onerror=)',
            'traversal' => '\\.\\.\/',
        ];

        foreach ($patterns as $type => $pattern) {
            $result = ReadonlyCommand::run("grep -E '{$pattern}' /var/log/nginx/access.log 2>/dev/null | tail -n 5");
            
            if ($result['success'] && !empty($result['output'])) {
                $lines = explode("\n", $result['output']);
                
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;

                    // Parse nginx access log
                    if (preg_match('/([\d.]+) - - \[([^\]]+)\]/', $line, $matches)) {
                        $ip = $matches[1];
                        $timestamp = Carbon::parse($matches[2]);
                        
                        $events[] = [
                            'type' => 'suspicious_request',
                            'severity' => 'critical',
                            'ip' => $ip,
                            'message' => "Mistenkelig forespørsel ({$type} forsøk)",
                            'timestamp' => $timestamp->toIso8601String(),
                            'timestamp_formatted' => $timestamp->format('d.m.Y H:i:s'),
                            'relative_time' => $timestamp->diffForHumans(),
                        ];
                    }
                }
            }
        }

        return $events;
    }

    protected function getFail2banStatus(): ?array
    {
        // Check if fail2ban is installed and running
        $result = ReadonlyCommand::run('fail2ban-client status 2>/dev/null');
        
        if (!$result['success']) {
            return [
                'installed' => false,
                'running' => false,
            ];
        }

        $bannedTotal = 0;
        $jails = [];

        // Get jail list
        if (preg_match('/Jail list:\s+(.+)/', $result['output'], $matches)) {
            $jailNames = array_map('trim', explode(',', $matches[1]));
            
            foreach ($jailNames as $jail) {
                $jailStatus = ReadonlyCommand::run("fail2ban-client status {$jail} 2>/dev/null");
                
                if ($jailStatus['success']) {
                    // Parse banned IPs
                    if (preg_match('/Currently banned:\s+(\d+)/', $jailStatus['output'], $bannedMatches)) {
                        $banned = (int)$bannedMatches[1];
                        $bannedTotal += $banned;
                        
                        if ($banned > 0) {
                            $jails[] = [
                                'name' => $jail,
                                'banned' => $banned,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'installed' => true,
            'running' => true,
            'total_banned' => $bannedTotal,
            'jails' => $jails,
        ];
    }

    protected function getSummary(): array
    {
        $events = $this->getSecurityEvents();
        
        $summary = [
            'total' => count($events),
            'last_hour' => 0,
            'last_24h' => 0,
            'by_severity' => [
                'critical' => 0,
                'warning' => 0,
                'info' => 0,
            ],
            'by_type' => [
                'ssh_failed_login' => 0,
                'web_auth_failure' => 0,
                'suspicious_request' => 0,
            ],
            'unique_ips' => [],
        ];

        $oneHourAgo = Carbon::now()->subHour();
        $oneDayAgo = Carbon::now()->subDay();

        foreach ($events as $event) {
            $timestamp = Carbon::parse($event['timestamp'] ?? 'now');
            
            if ($timestamp->isAfter($oneHourAgo)) {
                $summary['last_hour']++;
            }
            if ($timestamp->isAfter($oneDayAgo)) {
                $summary['last_24h']++;
            }

            $severity = $event['severity'] ?? 'info';
            if (isset($summary['by_severity'][$severity])) {
                $summary['by_severity'][$severity]++;
            }

            $type = $event['type'] ?? 'unknown';
            if (isset($summary['by_type'][$type])) {
                $summary['by_type'][$type]++;
            }

            if (isset($event['ip']) && !in_array($event['ip'], $summary['unique_ips'])) {
                $summary['unique_ips'][] = $event['ip'];
            }
        }

        $summary['unique_ip_count'] = count($summary['unique_ips']);
        unset($summary['unique_ips']); // Remove full list, just keep count

        return $summary;
    }

    protected function parseAuthLogTimestamp(string $dateString): Carbon
    {
        // Auth.log format: "Jan 15 12:34:56"
        // Need to add current year
        $year = Carbon::now()->year;
        return Carbon::parse("{$dateString} {$year}");
    }
}
