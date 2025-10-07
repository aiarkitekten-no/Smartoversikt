<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;

class SystemErrorLogFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.error-log';

    public function fetchData(): array
    {
        return [
            'errors' => $this->getRecentErrors(),
            'summary' => $this->getErrorSummary(),
            'customer_errors' => $this->getCustomerErrors(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getRecentErrors(): array
    {
        $errors = [];

        // Laravel logs
        $laravelErrors = $this->getLaravelErrors();
        $errors = array_merge($errors, $laravelErrors);

        // PHP-FPM errors
        $phpErrors = $this->getPhpErrors();
        $errors = array_merge($errors, $phpErrors);

        // Nginx errors
        $nginxErrors = $this->getNginxErrors();
        $errors = array_merge($errors, $nginxErrors);

        // Sort by timestamp (newest first)
        usort($errors, fn($a, $b) => strtotime($b['timestamp'] ?? 'now') <=> strtotime($a['timestamp'] ?? 'now'));

        // Limit to last 50 errors
        return array_slice($errors, 0, 50);
    }

    protected function getLaravelErrors(): array
    {
        $errors = [];
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return $errors;
        }

        // Read last 200 lines
        $result = ReadonlyCommand::run("tail -n 200 {$logPath}");
        
        if (!$result['success']) {
            return $errors;
        }

        $lines = explode("\n", $result['output']);
        $currentError = null;

        foreach ($lines as $line) {
            // Check if this is a new error entry
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.+)/', $line, $matches)) {
                // Save previous error if exists
                if ($currentError) {
                    $errors[] = $currentError;
                }

                $timestamp = $matches[1];
                $level = strtoupper($matches[3]);
                $message = $matches[4];

                // Only include ERROR, CRITICAL, EMERGENCY
                if (in_array($level, ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'])) {
                    $currentError = [
                        'source' => 'Laravel',
                        'level' => $level,
                        'message' => $this->cleanMessage($message),
                        'timestamp' => str_replace('T', ' ', $timestamp),
                        'type' => 'application',
                    ];
                } else {
                    $currentError = null;
                }
            } elseif ($currentError && !empty(trim($line))) {
                // Append stack trace or additional info (limit length)
                if (!isset($currentError['details'])) {
                    $currentError['details'] = [];
                }
                if (count($currentError['details']) < 3) {
                    $currentError['details'][] = trim($line);
                }
            }
        }

        // Add last error
        if ($currentError) {
            $errors[] = $currentError;
        }

        return $errors;
    }

    protected function getPhpErrors(): array
    {
        $errors = [];
        
        // Common PHP-FPM log locations
        $possiblePaths = [
            '/var/log/php-fpm/error.log',
            '/var/log/php8.2-fpm.log',
            '/var/log/php8.1-fpm.log',
            '/var/log/php-fpm.log',
        ];

        foreach ($possiblePaths as $logPath) {
            $result = ReadonlyCommand::run("test -f {$logPath} && tail -n 50 {$logPath} 2>/dev/null");
            
            if (!$result['success'] || empty($result['output'])) {
                continue;
            }

            $lines = explode("\n", $result['output']);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                // Parse PHP error format
                if (preg_match('/\[([^\]]+)\]\s+(WARNING|NOTICE|ERROR|FATAL):\s+(.+)/', $line, $matches)) {
                    $errors[] = [
                        'source' => 'PHP',
                        'level' => $matches[2],
                        'message' => $this->cleanMessage($matches[3]),
                        'timestamp' => $matches[1],
                        'type' => 'runtime',
                    ];
                }
            }

            break; // Use first found log
        }

        return $errors;
    }

    protected function getNginxErrors(): array
    {
        $errors = [];
        $logPath = '/var/log/nginx/error.log';

        $result = ReadonlyCommand::run("test -f {$logPath} && tail -n 50 {$logPath} 2>/dev/null");
        
        if (!$result['success'] || empty($result['output'])) {
            return $errors;
        }

        $lines = explode("\n", $result['output']);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Parse nginx error format
            if (preg_match('/(\d{4}\/\d{2}\/\d{2}\s+\d{2}:\d{2}:\d{2})\s+\[(\w+)\]\s+(.+)/', $line, $matches)) {
                $level = strtoupper($matches[2]);
                
                // Only include error, crit, alert, emerg
                if (in_array($level, ['ERROR', 'CRIT', 'ALERT', 'EMERG'])) {
                    $timestamp = str_replace('/', '-', $matches[1]);
                    
                    $errors[] = [
                        'source' => 'Nginx',
                        'level' => $level === 'CRIT' ? 'CRITICAL' : $level,
                        'message' => $this->cleanMessage($matches[3]),
                        'timestamp' => $timestamp,
                        'type' => 'webserver',
                    ];
                }
            }
        }

        return $errors;
    }

    protected function getErrorSummary(): array
    {
        $errors = $this->getRecentErrors();
        
        $summary = [
            'total' => count($errors),
            'last_hour' => 0,
            'last_24h' => 0,
            'by_source' => [
                'Laravel' => 0,
                'PHP' => 0,
                'Nginx' => 0,
            ],
            'by_level' => [
                'CRITICAL' => 0,
                'ERROR' => 0,
                'WARNING' => 0,
            ],
        ];

        $oneHourAgo = Carbon::now()->subHour();
        $oneDayAgo = Carbon::now()->subDay();

        foreach ($errors as $error) {
            $timestamp = Carbon::parse($error['timestamp'] ?? 'now');
            
            if ($timestamp->isAfter($oneHourAgo)) {
                $summary['last_hour']++;
            }
            if ($timestamp->isAfter($oneDayAgo)) {
                $summary['last_24h']++;
            }

            $source = $error['source'] ?? 'Unknown';
            if (isset($summary['by_source'][$source])) {
                $summary['by_source'][$source]++;
            }

            $level = $error['level'] ?? 'ERROR';
            if (isset($summary['by_level'][$level])) {
                $summary['by_level'][$level]++;
            } elseif (isset($summary['by_level']['ERROR'])) {
                $summary['by_level']['ERROR']++;
            }
        }

        return $summary;
    }

    protected function cleanMessage(string $message): string
    {
        // Remove file paths that are too long
        $message = preg_replace('/\/var\/www\/[^\s]+/', '...', $message);
        
        // Truncate very long messages
        if (strlen($message) > 200) {
            $message = substr($message, 0, 200) . '...';
        }

        return $message;
    }

    /**
     * Get customer-specific errors from vhosts
     */
    protected function getCustomerErrors(): array
    {
        $customerErrors = [];
        $vhostsPath = '/var/www/vhosts';

        // Get list of customer domains
        $result = ReadonlyCommand::run("find {$vhostsPath} -maxdepth 1 -type d");
        
        if (!$result['success']) {
            return [];
        }

        $domains = explode("\n", trim($result['output']));
        
        foreach ($domains as $domainPath) {
            if (empty($domainPath) || $domainPath === $vhostsPath) {
                continue;
            }

            $domain = basename($domainPath);
            
            // Skip system directories and current site
            if (in_array($domain, ['system', 'default', 'smartesider.no'])) {
                continue;
            }

            // Check for Laravel logs
            $laravelLog = "{$domainPath}/httpdocs/storage/logs/laravel.log";
            $errors = $this->getCustomerLaravelErrors($domain, $laravelLog);
            
            if (!empty($errors)) {
                $customerErrors[$domain] = $errors;
            }

            // Check for PHP error logs in logs directory
            $phpLog = "{$domainPath}/logs/error_log";
            $phpErrors = $this->getCustomerPhpErrors($domain, $phpLog);
            
            if (!empty($phpErrors)) {
                if (!isset($customerErrors[$domain])) {
                    $customerErrors[$domain] = [];
                }
                $customerErrors[$domain] = array_merge($customerErrors[$domain], $phpErrors);
            }
        }

        // Detect recurring patterns
        return $this->analyzeRecurringErrors($customerErrors);
    }

    /**
     * Get Laravel errors for a specific customer
     */
    protected function getCustomerLaravelErrors(string $domain, string $logPath): array
    {
        $errors = [];
        
        $result = ReadonlyCommand::run("test -f {$logPath} && tail -n 100 {$logPath} 2>/dev/null");
        
        if (!$result['success'] || empty($result['output'])) {
            return $errors;
        }

        $lines = explode("\n", $result['output']);
        
        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+\w+\.(\w+):\s+(.+)/', $line, $matches)) {
                $level = strtoupper($matches[2]);
                
                if (in_array($level, ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'])) {
                    $errors[] = [
                        'domain' => $domain,
                        'timestamp' => str_replace('T', ' ', $matches[1]),
                        'level' => $level,
                        'message' => $this->cleanMessage($matches[3]),
                        'source' => 'Laravel',
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * Get PHP errors for a specific customer
     */
    protected function getCustomerPhpErrors(string $domain, string $logPath): array
    {
        $errors = [];
        
        $result = ReadonlyCommand::run("test -f {$logPath} && tail -n 100 {$logPath} 2>/dev/null");
        
        if (!$result['success'] || empty($result['output'])) {
            return $errors;
        }

        $lines = explode("\n", $result['output']);
        
        foreach ($lines as $line) {
            if (preg_match('/\[([^\]]+)\]\s+PHP\s+(Fatal error|Parse error|Warning|Notice):\s+(.+)/', $line, $matches)) {
                $errors[] = [
                    'domain' => $domain,
                    'timestamp' => $matches[1],
                    'level' => strpos($matches[2], 'Fatal') !== false ? 'CRITICAL' : 'ERROR',
                    'message' => $this->cleanMessage($matches[3]),
                    'source' => 'PHP',
                ];
            }
        }

        return $errors;
    }

    /**
     * Analyze and group recurring errors
     */
    protected function analyzeRecurringErrors(array $customerErrors): array
    {
        $analyzed = [];

        foreach ($customerErrors as $domain => $errors) {
            if (empty($errors)) {
                continue;
            }

            // Group errors by message signature
            $grouped = [];
            foreach ($errors as $error) {
                $signature = $this->getErrorSignature($error['message']);
                
                if (!isset($grouped[$signature])) {
                    $grouped[$signature] = [
                        'count' => 0,
                        'first_seen' => $error['timestamp'],
                        'last_seen' => $error['timestamp'],
                        'level' => $error['level'],
                        'message' => $error['message'],
                        'source' => $error['source'],
                    ];
                }
                
                $grouped[$signature]['count']++;
                $grouped[$signature]['last_seen'] = $error['timestamp'];
            }

            // Filter to only recurring errors (>= 3 occurrences)
            $recurring = array_filter($grouped, fn($g) => $g['count'] >= 3);
            
            if (!empty($recurring)) {
                // Sort by count (most frequent first)
                uasort($recurring, fn($a, $b) => $b['count'] <=> $a['count']);
                
                $analyzed[$domain] = [
                    'total_errors' => count($errors),
                    'unique_errors' => count($grouped),
                    'recurring_errors' => count($recurring),
                    'top_recurring' => array_slice($recurring, 0, 5), // Top 5 recurring
                ];
            }
        }

        return $analyzed;
    }

    /**
     * Create error signature for grouping similar errors
     */
    protected function getErrorSignature(string $message): string
    {
        // Remove variable parts (numbers, IDs, timestamps, etc.)
        $signature = preg_replace('/\d+/', 'N', $message);
        $signature = preg_replace('/[a-f0-9]{32,}/', 'HASH', $signature);
        $signature = preg_replace('/\s+/', ' ', $signature);
        
        return substr(md5($signature), 0, 16);
    }
}
