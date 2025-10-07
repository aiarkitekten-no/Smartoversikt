<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;

class MailLogFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'mail.log';
    
    protected function fetchData(): array
    {
        try {
            $cmd = new ReadonlyCommand();
            
            // Parse mail log for statistics (last 1000 lines)
            $logFile = $this->findMailLog();
            
            if (!$logFile) {
                return $this->getUnavailableResponse();
            }

            $stats = $this->parseMailLog($cmd, $logFile);
            
            return [
                'sent' => $stats['sent'],
                'received' => $stats['received'],
                'bounced' => $stats['bounced'],
                'rejected' => $stats['rejected'],
                'deferred' => $stats['deferred'],
                'total' => $stats['sent'] + $stats['received'] + $stats['bounced'] + $stats['rejected'],
                'log_file' => basename($logFile),
                'status' => $stats['bounced'] > 50 ? 'warning' : 'ok',
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return $this->getUnavailableResponse($e->getMessage());
        }
    }

    private function findMailLog(): ?string
    {
        $possibleLogs = [
            '/var/log/mail.log',
            '/var/log/maillog',
            '/var/log/postfix.log',
        ];

        foreach ($possibleLogs as $log) {
            if (file_exists($log) && is_readable($log)) {
                return $log;
            }
        }

        return null;
    }

    private function parseMailLog(ReadonlyCommand $cmd, string $logFile): array
    {
        try {
            // Get last 1000 lines of mail log
            $output = $cmd->exec("tail -1000 {$logFile} 2>/dev/null");
            
            $stats = [
                'sent' => 0,
                'received' => 0,
                'bounced' => 0,
                'rejected' => 0,
                'deferred' => 0,
            ];

            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                if (stripos($line, 'status=sent') !== false) {
                    $stats['sent']++;
                } elseif (stripos($line, 'status=bounced') !== false) {
                    $stats['bounced']++;
                } elseif (stripos($line, 'status=deferred') !== false) {
                    $stats['deferred']++;
                } elseif (stripos($line, 'reject:') !== false || stripos($line, 'rejected') !== false) {
                    $stats['rejected']++;
                } elseif (stripos($line, 'from=<') !== false) {
                    $stats['received']++;
                }
            }

            return $stats;
        } catch (\Exception $e) {
            return [
                'sent' => 0,
                'received' => 0,
                'bounced' => 0,
                'rejected' => 0,
                'deferred' => 0,
            ];
        }
    }

    private function getUnavailableResponse(?string $error = null): array
    {
        return [
            'sent' => 0,
            'received' => 0,
            'bounced' => 0,
            'rejected' => 0,
            'deferred' => 0,
            'total' => 0,
            'status' => 'unavailable',
            'error' => $error ?? 'Mail log not accessible',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
