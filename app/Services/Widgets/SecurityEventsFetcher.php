<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class SecurityEventsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'security.events';

    public function fetchData(): array
    {
        $events = $this->getSecurityEvents();
        // Auto-block attackers outside Norway for 30 minutes (idempotent via cache)
        try {
            $this->autoBlockNonNorwayAttackers($events);
        } catch (\Throwable $e) {
            Log::warning('Auto-block process failed', ['error' => $e->getMessage()]);
        }
        $summary = $this->getSummary();
        $riskScore = $this->calculateRiskScore($summary);
        
        // Send notifications if thresholds exceeded
        $this->checkAndNotify($summary, $riskScore);
        
        // Generate analytics
        $analytics = [
            'top_countries' => $this->getTopCountries($events),           // #1
            'top_ips' => $this->getTopAttackingIps($events),              // #3
            'attack_distribution' => $this->getAttackDistribution($summary), // #4
            'targeted_services' => $this->getTargetedServices(),           // #7
            'last_critical' => $this->getLastCriticalEvent($events),      // #8
            'fail2ban_efficiency' => $this->getFail2banEfficiency(),      // #9
            'attempted_usernames' => $this->getAttemptedUsernames($events), // #11
        ];
        
        return [
            'events' => $events,
            'summary' => $summary,
            'fail2ban' => $this->getFail2banStatus(),
            'risk_score' => $riskScore,
            'analytics' => $analytics,
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

        // Suspicious web requests / IP activity
        $suspicious = $this->getSuspiciousActivity();
        $events = array_merge($events, $suspicious);

        // Sort by timestamp (newest first) and limit
        usort($events, fn($a, $b) => strtotime($b['timestamp'] ?? 'now') <=> strtotime($a['timestamp'] ?? 'now'));
        return array_slice($events, 0, 30);
    }

    protected function getSuspiciousActivity(): array
    {
        $events = [];

        // Safely read suspicious lines from nginx access log
        $result = ReadonlyCommand::run('sudo /usr/local/bin/security-log-reader.sh nginx-suspicious');
        if (!$result['success']) {
            return $events;
        }

        $lines = explode("\n", $result['output'] ?? '');

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Combined log format example:
            // 1.2.3.4 - - [15/Oct/2025:12:34:56 +0200] "GET /index.php?id=1 HTTP/1.1" 403 1234 "https://example.com/" "UA"
            if (!preg_match('/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"([A-Z]+)\s+([^\"\s]+)[^\"]*"\s+(\d{3})\s+(\d+)\s+"([^"]*)"\s+"([^"]*)"/i', $line, $m)) {
                // Fallback: try a simpler form without bytes/referer/UA
                if (!preg_match('/^(\S+)\s+\S+\s+\S+\s+\[([^\]]+)\]\s+"([A-Z]+)\s+([^\"\s]+)[^\"]*"\s+(\d{3})/i', $line, $m)) {
                    continue;
                }
            }

            $ip = $m[1];
            $rawTs = $m[2];
            $method = strtoupper($m[3]);
            $path = $m[4];
            $status = (int)($m[5] ?? 0);
            $bytes = isset($m[6]) ? (int)$m[6] : null;
            $referer = isset($m[7]) && $m[7] !== '-' ? $m[7] : null;
            $userAgent = isset($m[8]) && $m[8] !== '-' ? $m[8] : null;

            $timestamp = $this->parseNginxTimestamp($rawTs);

            // Pattern detection from full line
            $matchedPatterns = [];
            if (preg_match('/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP)\b/i', $line)) {
                $matchedPatterns[] = 'sql_keyword';
            }
            if (preg_match('/(<script|javascript:|onerror=)/i', $line)) {
                $matchedPatterns[] = 'xss_payload';
            }
            if (preg_match('/(\.\.\/|%2e%2e%2f|%00)/i', $line)) {
                $matchedPatterns[] = 'path_traversal';
            }

            $attackType = 'unknown';
            if (in_array('sql_keyword', $matchedPatterns, true)) {
                $attackType = 'sql_injection';
            } elseif (in_array('xss_payload', $matchedPatterns, true)) {
                $attackType = 'xss';
            } elseif (in_array('path_traversal', $matchedPatterns, true)) {
                $attackType = 'path_traversal';
            }

            // Outcome based on status code
            $outcome = 'attempted';
            if ($status >= 500) {
                $outcome = 'server_error';
            } elseif ($status === 401 || $status === 403) {
                $outcome = 'blocked';
            } elseif ($status === 404) {
                $outcome = 'not_found';
            }

            // Try to extract host (vhost); fallback to referer host if present
            $host = null;
            if (preg_match('/\s"Host:\s*([^"\s]+)"/i', $line, $hm)) {
                $host = $hm[1];
            }
            if (!$host && $referer) {
                $hostFromRef = is_string($referer) ? parse_url($referer, PHP_URL_HOST) : null;
                if ($hostFromRef) $host = $hostFromRef;
            }

            // Clean path and derive directory and target file
            $cleanPath = $path ?: '/';
            $qPos = strpos($cleanPath, '?');
            if ($qPos !== false) {
                $cleanPath = substr($cleanPath, 0, $qPos);
            }
            $segments = array_values(array_filter(explode('/', trim($cleanPath, '/'))));
            $targetFile = !empty($segments) ? end($segments) : null;
            $pathDir = null;
            if (count($segments) > 1) {
                $pathDir = '/' . implode('/', array_slice($segments, 0, -1));
            } elseif (!empty($segments)) {
                $pathDir = '/';
            }

            $country = $this->getCountryForIp($ip);
            $reputation = $this->checkIpReputation($ip);
            $severity = ($attackType !== 'unknown' || $status >= 500) ? 'critical' : 'warning';

            $message = sprintf('%s %s', $method, $path);

            $events[] = [
                'type' => 'suspicious_request',
                'severity' => $severity,
                'ip' => $ip,
                'country' => $country,
                'reputation' => $reputation,
                'method' => $method,
                'path' => $path,
                'path_dir' => $pathDir,
                'target_file' => $targetFile,
                'host' => $host,
                'status' => $status,
                'attack_type' => $attackType,
                'matched_patterns' => $matchedPatterns,
                'outcome' => $outcome,
                'referer' => $referer,
                'user_agent' => $userAgent,
                'bytes' => $bytes,
                'message' => $message,
                'timestamp' => $timestamp->toIso8601String(),
                'timestamp_formatted' => $timestamp->format('d.m.Y H:i:s'),
                'relative_time' => $timestamp->diffForHumans(),
            ];
        }

        return $events;
    }

    protected function getSshFailedLogins(): array
    {
        $events = [];
        $result = ReadonlyCommand::run("sudo /usr/local/bin/security-log-reader.sh ssh-failed");
        if (!$result['success']) return $events;
        $lines = explode("\n", $result['output']);
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            if (preg_match('/(\w+\s+\d+\s+\d{2}:\d{2}:\d{2}).*Failed password for (\w+) from ([\d.]+)/', $line, $m)) {
                $timestamp = $this->parseAuthLogTimestamp($m[1]);
                $ip = $m[3];
                $country = $this->getCountryForIp($ip);
                $reputation = $this->checkIpReputation($ip);
                $events[] = [
                    'type' => 'ssh_failed_login',
                    'severity' => 'warning',
                    'user' => $m[2],
                    'ip' => $ip,
                    'country' => $country,
                    'reputation' => $reputation,
                    'message' => "SSH login feilet for bruker '{$m[2]}'",
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
        if (!file_exists($logPath)) return $events;
        $result = ReadonlyCommand::run("grep -i 'authentication\\|login.*fail\\|unauthorized' {$logPath} 2>/dev/null | tail -n 20");
        if (!$result['success']) return $events;
        $lines = explode("\n", $result['output']);
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            if (preg_match('/\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2})/', $line, $matches)) {
                $timestamp = Carbon::parse($matches[1]);
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

    protected function autoBlockNonNorwayAttackers(array &$events): void
    {
        foreach ($events as &$event) {
            if (($event['type'] ?? '') !== 'suspicious_request') continue;
            $ip = $event['ip'] ?? null;
            if (!$ip || $this->isPrivateIp($ip)) continue;

            $countryCode = $event['country']['code'] ?? 'XX';
            if ($countryCode === 'NO') continue;

            $cacheKey = "security:auto_blocked:" . $ip;
            if (Cache::get($cacheKey)) {
                // already auto-blocked within TTL
                $event['auto_blocked'] = true;
                $event['block_ttl_minutes'] = 30;
                continue;
            }

            // Attempt firewall block for 30 minutes
            $blocked = $this->blockIpForMinutes($ip, 30, 'Auto-block: non-NO suspicious request');
            if ($blocked) {
                Cache::put($cacheKey, true, now()->addMinutes(30));
                $event['auto_blocked'] = true;
                $event['block_ttl_minutes'] = 30;
                $event['block_expires_at'] = now()->addMinutes(30)->toIso8601String();
                Log::warning('Auto-blocked IP (30m) outside NO', ['ip' => $ip, 'country' => $countryCode]);
            }
        }
        unset($event);
    }

    protected function blockIpForMinutes(string $ip, int $minutes, string $reason = 'Auto-block'): bool
    {
        try {
            // Ensure chain exists and is attached
            $commands = [
                "sudo -n iptables -N DASHBOARD_BLOCKS 2>/dev/null || true",
                "sudo -n iptables -C INPUT -j DASHBOARD_BLOCKS 2>/dev/null || sudo -n iptables -I INPUT 1 -j DASHBOARD_BLOCKS",
                // Add drop rule
                "sudo -n iptables -I DASHBOARD_BLOCKS 1 -s {$ip} -j DROP",
                // Schedule auto-unblock after N minutes
                "echo 'sudo -n iptables -D DASHBOARD_BLOCKS -s {$ip} -j DROP 2>/dev/null' | at now + {$minutes} minutes 2>/dev/null",
            ];

            foreach ($commands as $cmd) {
                $res = ReadonlyCommand::run($cmd);
                if (!$res['success']) {
                    Log::warning('Auto-block command failed', ['cmd' => $cmd, 'output' => $res['output'] ?? '']);
                }
            }

            // Also add to fail2ban as reinforcement
            ReadonlyCommand::run("sudo -n fail2ban-client set recidive banip {$ip} 2>/dev/null");

            Log::info('IP auto-block scheduled', [
                'ip' => $ip,
                'minutes' => $minutes,
                'reason' => $reason,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to auto-block IP', ['ip' => $ip, 'error' => $e->getMessage()]);
            return false;
        }
    }

    protected function isPrivateIp(string $ip): bool
    {
        // IPv4 private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ranges = [
                '10.0.0.0/8',
                '172.16.0.0/12',
                '192.168.0.0/16',
                '127.0.0.0/8',
            ];

            $ipLong = ip2long($ip);
            foreach ($ranges as $range) {
                [$subnet, $mask] = explode('/', $range);
                $subnetLong = ip2long($subnet);
                $mask = (int)$mask;
                if ($mask < 0 || $mask > 32) continue;
                $maskLong = $mask === 0 ? 0 : (-1 << (32 - $mask));
                if (($ipLong & $maskLong) === ($subnetLong & $maskLong)) {
                    return true;
                }
            }
            return false;
        }

        // IPv6 private/unique local and loopback
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if ($ip === '::1') return true; // loopback
            $bin = inet_pton($ip);
            if ($bin === false) return false;
            $hex = bin2hex($bin);
            // Unique local addresses fc00::/7 => prefix fc or fd
            $prefix = strtolower(substr($hex, 0, 2));
            if ($prefix === 'fc' || $prefix === 'fd') {
                return true;
            }
            return false;
        }

        return false;
    }

    protected function getFail2banStatus(): ?array
    {
        // Use sudo wrapper to check fail2ban status
        $result = ReadonlyCommand::run('sudo /usr/local/bin/fail2ban-status.sh status');
        
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
                // Use sudo wrapper for individual jail status
                $jailStatus = ReadonlyCommand::run("sudo /usr/local/bin/fail2ban-status.sh jail-status {$jail}");
                
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

    protected function parseNginxTimestamp(string $dateString): Carbon
    {
        // Nginx access log format: 15/Oct/2025:12:34:56 +0200
        try {
            return Carbon::createFromFormat('d/M/Y:H:i:s O', $dateString);
        } catch (\Exception $e) {
            return Carbon::now();
        }
    }

    /**
     * Tillegg #3: GeoIP Tracking
     * Get country for IP address using GeoIP
     */
    protected function getCountryForIp(string $ip): array
    {
        if (!config('security.geoip.enabled', true)) {
            return ['code' => 'XX', 'name' => 'Unknown', 'flag' => '🏳️'];
        }

        // Use geoiplookup command
        $result = ReadonlyCommand::run("geoiplookup {$ip}");
        
        if ($result['success'] && preg_match('/Country Edition: ([A-Z]{2}), (.+)/', $result['output'], $matches)) {
            $code = $matches[1];
            $name = $matches[2];
            
            return [
                'code' => $code,
                'name' => $name,
                'flag' => $this->getCountryFlag($code),
            ];
        }

        return ['code' => 'XX', 'name' => 'Unknown', 'flag' => '🏳️'];
    }

    protected function getCountryFlag(string $countryCode): string
    {
        // Convert country code to flag emoji
        $flags = [
            'US' => '🇺🇸', 'CN' => '🇨🇳', 'RU' => '🇷🇺', 'BR' => '🇧🇷',
            'IN' => '🇮🇳', 'DE' => '🇩🇪', 'FR' => '🇫🇷', 'GB' => '🇬🇧',
            'NO' => '🇳🇴', 'SE' => '🇸🇪', 'DK' => '🇩🇰', 'FI' => '🇫🇮',
            'NL' => '🇳🇱', 'BE' => '🇧🇪', 'ES' => '🇪🇸', 'IT' => '🇮🇹',
            'PL' => '🇵🇱', 'UA' => '🇺🇦', 'TR' => '🇹🇷', 'JP' => '🇯🇵',
            'KR' => '🇰🇷', 'AU' => '🇦🇺', 'CA' => '🇨🇦', 'MX' => '🇲🇽',
            'AR' => '🇦🇷', 'CL' => '🇨🇱', 'ZA' => '🇿🇦', 'EG' => '🇪🇬',
            'XX' => '🏳️',
        ];

        return $flags[$countryCode] ?? '🌍';
    }

    /**
     * Tillegg #4: Risikovurdering  
     * Calculate risk score 0-100 based on security events
     */
    protected function calculateRiskScore(array $summary): array
    {
        $score = 0;
        $factors = [];
        $recommendations = [];

        // SSH brute force (weight: 30)
        $sshFailed = $summary['by_type']['ssh_failed_login'] ?? 0;
        if ($sshFailed > 20) {
            $score += 30;
            $factors[] = "Høy SSH brute-force aktivitet ({$sshFailed} forsøk)";
            $recommendations[] = "Vurder å endre SSH port eller enable key-only auth";
        } elseif ($sshFailed > 10) {
            $score += 20;
            $factors[] = "Moderat SSH brute-force ({$sshFailed} forsøk)";
        } elseif ($sshFailed > 0) {
            $score += 10;
            $factors[] = "Noen SSH failed logins ({$sshFailed})";
        }

        // Suspicious requests - SQL/XSS (weight: 40)
        $suspicious = $summary['by_type']['suspicious_request'] ?? 0;
        if ($suspicious > 10) {
            $score += 40;
            $factors[] = "Aktive web-angrep (SQL/XSS: {$suspicious})";
            $recommendations[] = "Sjekk WAF-regler og vurder å blokkere angripende IP-er";
        } elseif ($suspicious > 5) {
            $score += 30;
            $factors[] = "Mistenkelige web-forespørsler ({$suspicious})";
        } elseif ($suspicious > 0) {
            $score += 15;
            $factors[] = "Noen mistenkelige requests ({$suspicious})";
        }

        // Distributed attack - unique IPs (weight: 20)
        $uniqueIps = $summary['unique_ip_count'] ?? 0;
        if ($uniqueIps > 20) {
            $score += 20;
            $factors[] = "Distribuert angrep ({$uniqueIps} unike IP-er)";
            $recommendations[] = "Vurder rate limiting og geografisk blokkering";
        } elseif ($uniqueIps > 10) {
            $score += 15;
            $factors[] = "Mange angripende IP-er ({$uniqueIps})";
        } elseif ($uniqueIps > 5) {
            $score += 8;
            $factors[] = "Flere angripende IP-er ({$uniqueIps})";
        }

        // Recent activity - last hour (weight: 10)
        $lastHour = $summary['last_hour'] ?? 0;
        if ($lastHour > 15) {
            $score += 10;
            $factors[] = "Høy aktivitet NÅ ({$lastHour} events siste time)";
            $recommendations[] = "Overvåk situasjonen aktivt";
        } elseif ($lastHour > 8) {
            $score += 7;
            $factors[] = "Økt aktivitet ({$lastHour} events/time)";
        }

        // Determine risk level
        $level = 'LOW';
        $color = 'green';
        $action = 'Monitor';
        
        if ($score >= 70) {
            $level = 'CRITICAL';
            $color = 'red';
            $action = 'IMMEDIATE ACTION REQUIRED';
        } elseif ($score >= 40) {
            $level = 'HIGH';
            $color = 'orange';
            $action = 'Investigate and take action';
        } elseif ($score >= 20) {
            $level = 'MEDIUM';
            $color = 'yellow';
            $action = 'Monitor closely';
        }

        return [
            'score' => min(100, $score),
            'level' => $level,
            'color' => $color,
            'action' => $action,
            'factors' => $factors,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Tillegg #5: IP Reputation Check
     * Check IP reputation using AbuseIPDB API
     */
    protected function checkIpReputation(string $ip): array
    {
        if (!config('security.abuseipdb.enabled', false)) {
            return ['checked' => false];
        }

        $apiKey = config('security.abuseipdb.api_key');
        if (!$apiKey) {
            return ['checked' => false, 'error' => 'No API key'];
        }

        // Cache results to avoid API spam
        $cacheKey = "ip_reputation:{$ip}";
        $cacheTtl = config('security.abuseipdb.cache_ttl', 3600);
        
        return Cache::remember($cacheKey, $cacheTtl, function() use ($ip, $apiKey) {
            try {
                $response = Http::withHeaders([
                    'Key' => $apiKey,
                    'Accept' => 'application/json',
                ])->get('https://api.abuseipdb.com/api/v2/check', [
                    'ipAddress' => $ip,
                    'maxAgeInDays' => 90,
                    'verbose' => true,
                ]);

                if ($response->successful()) {
                    $data = $response->json()['data'] ?? [];
                    
                    return [
                        'checked' => true,
                        'abuse_score' => $data['abuseConfidenceScore'] ?? 0,
                        'is_whitelisted' => $data['isWhitelisted'] ?? false,
                        'is_public' => $data['isPublic'] ?? true,
                        'country_code' => $data['countryCode'] ?? 'Unknown',
                        'isp' => $data['isp'] ?? 'Unknown',
                        'domain' => $data['domain'] ?? '',
                        'total_reports' => $data['totalReports'] ?? 0,
                        'last_reported_at' => $data['lastReportedAt'] ?? null,
                    ];
                }

                return ['checked' => false, 'error' => 'API error'];
            } catch (\Exception $e) {
                Log::error('AbuseIPDB check failed', [
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
                
                return ['checked' => false, 'error' => $e->getMessage()];
            }
        });
    }

    /**
     * Tillegg #7: Notifications
     * Send notifications if critical thresholds exceeded
     */
    protected function checkAndNotify(array $summary, array $riskScore): void
    {
        if (!config('security.notifications.enabled', false)) {
            return;
        }

        $criticalThresholds = config('security.notifications.thresholds.critical', []);
        $isCritical = false;
        $alertMessage = [];

        // Check critical thresholds
        if (($summary['last_hour'] ?? 0) > ($criticalThresholds['events_per_hour'] ?? 50)) {
            $isCritical = true;
            $alertMessage[] = "🚨 {$summary['last_hour']} security events in last hour (threshold: {$criticalThresholds['events_per_hour']})";
        }

        if (($summary['unique_ip_count'] ?? 0) > ($criticalThresholds['unique_ips'] ?? 20)) {
            $isCritical = true;
            $alertMessage[] = "🚨 {$summary['unique_ip_count']} unique attacking IPs detected";
        }

        if (($summary['by_type']['suspicious_request'] ?? 0) > ($criticalThresholds['sql_injection_attempts'] ?? 5)) {
            $isCritical = true;
            $alertMessage[] = "🚨 {$summary['by_type']['suspicious_request']} SQL/XSS injection attempts";
        }

        if ($riskScore['level'] === 'CRITICAL') {
            $isCritical = true;
            $alertMessage[] = "🔴 CRITICAL Risk Score: {$riskScore['score']}/100";
        }

        if ($isCritical) {
            $this->sendSecurityAlert($alertMessage, $summary, $riskScore);
        }
    }

    protected function sendSecurityAlert(array $messages, array $summary, array $riskScore): void
    {
        $channels = config('security.notifications.channels', ['email']);
        $message = implode("\n", $messages);

        // Send to configured channels
        if (in_array('email', $channels)) {
            $this->sendEmailAlert($message, $summary, $riskScore);
        }

        if (in_array('slack', $channels)) {
            $this->sendSlackAlert($message, $summary, $riskScore);
        }

        // Log alert
        Log::critical('Security Alert Triggered', [
            'messages' => $messages,
            'risk_score' => $riskScore['score'],
            'summary' => $summary,
        ]);
    }

    protected function sendEmailAlert(string $message, array $summary, array $riskScore): void
    {
        $to = config('security.notifications.email.to');
        if (!$to) return;

        try {
            \Illuminate\Support\Facades\Mail::raw(
                $this->formatAlertEmail($message, $summary, $riskScore),
                function ($mail) use ($to) {
                    $mail->to($to)
                         ->subject('🚨 Security Alert - Smartesider.no')
                         ->from(config('security.notifications.email.from', config('mail.from.address')));
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send security email alert', ['error' => $e->getMessage()]);
        }
    }

    protected function sendSlackAlert(string $message, array $summary, array $riskScore): void
    {
        $webhookUrl = config('security.notifications.slack.webhook_url');
        if (!$webhookUrl) return;

        try {
            Http::post($webhookUrl, [
                'channel' => config('security.notifications.slack.channel', '#security-alerts'),
                'username' => config('security.notifications.slack.username', 'Security Bot'),
                'icon_emoji' => config('security.notifications.slack.icon', ':shield:'),
                'text' => "🚨 *Security Alert - Smartesider.no*",
                'attachments' => [[
                    'color' => $riskScore['level'] === 'CRITICAL' ? 'danger' : 'warning',
                    'fields' => [
                        ['title' => 'Risk Score', 'value' => "{$riskScore['score']}/100 ({$riskScore['level']})", 'short' => true],
                        ['title' => 'Events (1h)', 'value' => $summary['last_hour'] ?? 0, 'short' => true],
                        ['title' => 'Events (24h)', 'value' => $summary['last_24h'] ?? 0, 'short' => true],
                        ['title' => 'Unique IPs', 'value' => $summary['unique_ip_count'] ?? 0, 'short' => true],
                        ['title' => 'Details', 'value' => $message, 'short' => false],
                    ],
                    'footer' => 'Security Events Widget',
                    'ts' => time(),
                ]],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Slack security alert', ['error' => $e->getMessage()]);
        }
    }

    protected function formatAlertEmail(string $message, array $summary, array $riskScore): string
    {
        return <<<EMAIL
==============================================
🚨 SECURITY ALERT - Smartesider.no
==============================================

{$message}

RISK ASSESSMENT:
---------------
Risk Score: {$riskScore['score']}/100
Risk Level: {$riskScore['level']}
Action Required: {$riskScore['action']}

SUMMARY (Last 24 hours):
------------------------
Total Events: {$summary['total']}
Events (Last Hour): {$summary['last_hour']}
Unique Attacking IPs: {$summary['unique_ip_count']}

By Type:
- SSH Failed Logins: {$summary['by_type']['ssh_failed_login']}
- Web Auth Failures: {$summary['by_type']['web_auth_failure']}
- Suspicious Requests: {$summary['by_type']['suspicious_request']}

By Severity:
- Critical: {$summary['by_severity']['critical']}
- Warning: {$summary['by_severity']['warning']}

RISK FACTORS:
-------------
EMAIL;
        
        foreach ($riskScore['factors'] as $factor) {
            $email .= "\n- {$factor}";
        }

        if (!empty($riskScore['recommendations'])) {
            $email .= "\n\nRECOMMENDATIONS:\n---------------";
            foreach ($riskScore['recommendations'] as $rec) {
                $email .= "\n- {$rec}";
            }
        }

        $email .= "\n\n==============================================\n";
        $email .= "Dashboard: https://nytt.smartesider.no/dashboard\n";
        $email .= "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n";
        $email .= "==============================================\n";

        return $email;
    }

    /**
     * Analytics Method #1: Top 5 Attacking Countries
     * Returns countries with most attacks, sorted by count
     */
    protected function getTopCountries(array $events): array
    {
        $countryCounts = [];
        
        foreach ($events as $event) {
            if (isset($event['country']) && isset($event['country']['code'])) {
                $code = $event['country']['code'];
                
                // Skip unknown countries
                if ($code === 'XX' || $code === 'Unknown') {
                    continue;
                }
                
                if (!isset($countryCounts[$code])) {
                    $countryCounts[$code] = [
                        'code' => $code,
                        'name' => $event['country']['name'] ?? 'Unknown',
                        'flag' => $event['country']['flag'] ?? '🌍',
                        'count' => 0,
                    ];
                }
                
                $countryCounts[$code]['count']++;
            }
        }
        
        // Sort by count descending
        usort($countryCounts, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        // Return top 5
        return array_slice($countryCounts, 0, 5);
    }

    /**
     * Analytics Method #3: Top 5 Attacking IPs
     * Returns IPs with most attack attempts
     */
    protected function getTopAttackingIps(array $events): array
    {
        $ipCounts = [];
        
        foreach ($events as $event) {
            if (!isset($event['ip'])) {
                continue;
            }
            
            $ip = $event['ip'];
            
            if (!isset($ipCounts[$ip])) {
                $ipCounts[$ip] = [
                    'ip' => $ip,
                    'count' => 0,
                    'country' => $event['country'] ?? ['code' => 'XX', 'flag' => '🏳️', 'name' => 'Unknown'],
                    'reputation' => $event['reputation'] ?? ['checked' => false],
                    'last_seen' => $event['timestamp'] ?? null,
                ];
            }
            
            $ipCounts[$ip]['count']++;
            
            // Update last_seen to most recent
            if (isset($event['timestamp'])) {
                $current = Carbon::parse($ipCounts[$ip]['last_seen'] ?? 'now');
                $new = Carbon::parse($event['timestamp']);
                if ($new->isAfter($current)) {
                    $ipCounts[$ip]['last_seen'] = $event['timestamp'];
                }
            }
        }
        
        // Sort by count descending
        usort($ipCounts, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        // Return top 5
        return array_slice($ipCounts, 0, 5);
    }

    /**
     * Analytics Method #4: Attack Type Distribution
     * Returns percentage breakdown of attack types
     */
    protected function getAttackDistribution(array $summary): array
    {
        $total = $summary['total'] ?? 0;
        
        if ($total === 0) {
            return [];
        }
        
        $distribution = [];
        
        foreach ($summary['by_type'] as $type => $count) {
            if ($count > 0) {
                $percentage = round(($count / $total) * 100, 1);
                
                $label = match($type) {
                    'ssh_failed_login' => 'SSH Brute-Force',
                    'web_auth_failure' => 'Web Auth',
                    'suspicious_request' => 'Web Attack (SQL/XSS)',
                    default => ucfirst(str_replace('_', ' ', $type)),
                };
                
                $icon = match($type) {
                    'ssh_failed_login' => '🔐',
                    'web_auth_failure' => '🌐',
                    'suspicious_request' => '⚠️',
                    default => '•',
                };
                
                $distribution[] = [
                    'type' => $type,
                    'label' => $label,
                    'icon' => $icon,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            }
        }
        
        // Sort by count descending
        usort($distribution, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        return $distribution;
    }

    /**
     * Analytics Method #7: Most Targeted Services
     * Returns services being attacked based on fail2ban jails
     */
    protected function getTargetedServices(): array
    {
        $fail2ban = $this->getFail2banStatus();
        
        if (!isset($fail2ban['jails']) || empty($fail2ban['jails'])) {
            return [];
        }
        
        $services = [];
        
        foreach ($fail2ban['jails'] as $jail) {
            $name = $jail['name'];
            $banned = $jail['banned'];
            
            // Map jail names to service descriptions
            $serviceMap = [
                'sshd' => ['name' => 'SSH (port 22)', 'icon' => '🔐', 'severity' => 'high'],
                'plesk-postfix' => ['name' => 'Email (SMTP)', 'icon' => '📧', 'severity' => 'medium'],
                'plesk-modsecurity' => ['name' => 'Web Application Firewall', 'icon' => '🛡️', 'severity' => 'high'],
                'wp-login' => ['name' => 'WordPress Login', 'icon' => '🔑', 'severity' => 'high'],
                'wp-xmlrpc' => ['name' => 'WordPress XML-RPC', 'icon' => '⚡', 'severity' => 'medium'],
                'nginx-http-auth' => ['name' => 'Nginx Auth', 'icon' => '🌐', 'severity' => 'medium'],
                'plesk-apache' => ['name' => 'Apache Web Server', 'icon' => '🌐', 'severity' => 'medium'],
            ];
            
            $service = $serviceMap[$name] ?? [
                'name' => ucfirst(str_replace('-', ' ', $name)),
                'icon' => '•',
                'severity' => 'low'
            ];
            
            $services[] = [
                'jail' => $name,
                'name' => $service['name'],
                'icon' => $service['icon'],
                'banned_count' => $banned,
                'severity' => $service['severity'],
            ];
        }
        
        // Sort by banned count descending
        usort($services, function($a, $b) {
            return $b['banned_count'] - $a['banned_count'];
        });
        
        return array_slice($services, 0, 5);
    }

    /**
     * Analytics Method #8: Last Critical Event
     * Returns most recent critical security event
     */
    protected function getLastCriticalEvent(array $events): ?array
    {
        $criticalEvents = array_filter($events, function($event) {
            return ($event['severity'] ?? '') === 'critical';
        });
        
        if (empty($criticalEvents)) {
            return null;
        }
        
        // Sort by timestamp descending (most recent first)
        usort($criticalEvents, function($a, $b) {
            $timeA = Carbon::parse($a['timestamp'] ?? 'now');
            $timeB = Carbon::parse($b['timestamp'] ?? 'now');
            return $timeB->timestamp - $timeA->timestamp;
        });
        
        return $criticalEvents[0];
    }

    /**
     * Analytics Method #9: Fail2ban Efficiency
     * Calculates how effective fail2ban is at blocking threats
     */
    protected function getFail2banEfficiency(): array
    {
        $fail2ban = $this->getFail2banStatus();
        
        if (!$fail2ban['installed'] || !$fail2ban['running']) {
            return [
                'enabled' => false,
                'message' => 'Fail2ban ikke aktiv',
            ];
        }
        
        $totalBanned = $fail2ban['total_banned'] ?? 0;
        
        // Get total attack attempts from events
        $events = $this->getSecurityEvents();
        $uniqueAttackingIps = [];
        
        foreach ($events as $event) {
            if (isset($event['ip'])) {
                $uniqueAttackingIps[$event['ip']] = true;
            }
        }
        
        $totalAttackingIps = count($uniqueAttackingIps);
        
        // Estimate total potential threats (banned + currently attacking)
        $totalThreats = $totalBanned + $totalAttackingIps;
        
        if ($totalThreats === 0) {
            return [
                'enabled' => true,
                'banned_count' => $totalBanned,
                'blocked_percentage' => 100,
                'message' => 'Ingen aktive trusler',
            ];
        }
        
        $blockedPercentage = round(($totalBanned / $totalThreats) * 100, 1);
        
        return [
            'enabled' => true,
            'banned_count' => $totalBanned,
            'total_threats' => $totalThreats,
            'current_attacking' => $totalAttackingIps,
            'blocked_percentage' => $blockedPercentage,
            'message' => "Blokkert {$blockedPercentage}% av trusler ({$totalBanned} av {$totalThreats})",
        ];
    }

    /**
     * Analytics Method #11: Attempted Usernames (SSH)
     * Returns most commonly attempted usernames in SSH attacks
     */
    protected function getAttemptedUsernames(array $events): array
    {
        $usernameCounts = [];
        
        foreach ($events as $event) {
            if ($event['type'] === 'ssh_failed_login' && isset($event['user'])) {
                $username = $event['user'];
                
                if (!isset($usernameCounts[$username])) {
                    $usernameCounts[$username] = [
                        'username' => $username,
                        'count' => 0,
                    ];
                }
                
                $usernameCounts[$username]['count']++;
            }
        }
        
        if (empty($usernameCounts)) {
            return [];
        }
        
        // Sort by count descending
        usort($usernameCounts, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        // Return top 5
        return array_slice($usernameCounts, 0, 5);
    }
}

