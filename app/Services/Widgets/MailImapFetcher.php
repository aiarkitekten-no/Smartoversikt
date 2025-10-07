<?php

namespace App\Services\Widgets;

use App\Models\MailAccount;

class MailImapFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'mail.imap';
    
    protected function fetchData(): array
    {
        try {
            // Get all active IMAP accounts from database
            $accounts = MailAccount::imap()->active()->get();
            
            if ($accounts->isEmpty()) {
                return [
                    'error' => 'IMAP ikke konfigurert - legg til innstillinger',
                    'status' => 'not_configured',
                    'timestamp' => now()->toIso8601String(),
                ];
            }
            
            $accountStats = [];
            $totalMessages = 0;
            $totalUnread = 0;
            
            foreach ($accounts as $account) {
                try {
                    // Build mailbox string
                    $mailboxString = $this->buildMailboxString($account);
                    
                    // Connect to IMAP
                    $connection = $this->connectImap(
                        $mailboxString,
                        $account->username,
                        $account->password
                    );
                    
                    if (!$connection) {
                        $accountStats[] = [
                            'name' => $account->name,
                            'error' => 'Kunne ikke koble til',
                            'status' => 'connection_failed',
                        ];
                        continue;
                    }
                    
                    // Get mailbox statistics
                    $stats = $this->getMailboxStats($connection, $mailboxString);
                    
                    imap_close($connection);
                    
                    $accountStats[] = array_merge($stats, [
                        'name' => $account->name,
                        'username' => $account->username,
                        'status' => 'ok',
                    ]);
                    
                    $totalMessages += $stats['total_messages'] ?? 0;
                    $totalUnread += $stats['unread'] ?? 0;
                    
                } catch (\Exception $e) {
                    $accountStats[] = [
                        'name' => $account->name,
                        'error' => $e->getMessage(),
                        'status' => 'error',
                    ];
                }
            }
            
            return [
                'accounts' => $accountStats,
                'total_messages' => $totalMessages,
                'total_unread' => $totalUnread,
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'status' => 'error',
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }
    
    private function buildMailboxString(MailAccount $account): string
    {
        $options = [];
        
        // Handle self-signed certificates for trollhagen.no
        if (str_contains($account->host, 'trollhagen.no')) {
            // For self-signed certs, just skip validation
            $options[] = $account->encryption;
            $options[] = 'novalidate-cert';
        } else {
            // Normal SSL/TLS
            $options[] = $account->encryption;
            if (!$account->validate_cert) {
                $options[] = 'novalidate-cert';
            }
        }
        
        $optionString = implode('/', $options);
        return '{' . $account->host . ':' . $account->port . '/imap/' . $optionString . '}INBOX';
    }
    
    private function getAccountStats(MailAccount $account): array
    {
        try {
            // Build mailbox string
            $mailboxString = '{' . $account->host . ':' . $account->port . '/imap/' . $account->encryption . '}INBOX';
            
            // Connect to IMAP
            $connection = $this->connectImap(
                $mailboxString,
                $account->username,
                $account->password
            );
            
            if (!$connection) {
                return [
                    'name' => $account->name,
                    'status' => 'connection_failed',
                    'error' => 'Kunne ikke koble til',
                    'total_messages' => 0,
                    'unread' => 0,
                ];
            }
            
            // Get mailbox statistics
            $stats = $this->getMailboxStats($connection, $mailboxString);
            
            imap_close($connection);
            
            return array_merge($stats, [
                'name' => $account->name,
                'server' => $account->host,
                'status' => 'ok',
            ]);
            
        } catch (\Exception $e) {
            return [
                'name' => $account->name,
                'status' => 'error',
                'error' => $e->getMessage(),
                'total_messages' => 0,
                'unread' => 0,
            ];
        }
    }
    
    private function connectImap(string $mailboxString, string $username, string $password): mixed
    {
        try {
            // Suppress IMAP warnings
            $connection = @imap_open($mailboxString, $username, $password, 0, 1);
            
            if (!$connection) {
                return null;
            }
            
            return $connection;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function getMailboxStats($connection, string $mailboxString): array
    {
        try {
            // Reopen the connection to ensure we're in the right mailbox
            imap_reopen($connection, $mailboxString);
            
            $check = imap_check($connection);
            
            // Get unread count - search for UNSEEN messages
            $unread = imap_search($connection, 'UNSEEN', SE_UID);
            $unreadCount = $unread ? count($unread) : 0;
            
            // Get today's messages
            $today = date('d-M-Y');
            $todayMessages = imap_search($connection, "SINCE \"$today\"", SE_UID);
            $todayCount = $todayMessages ? count($todayMessages) : 0;
            
            // Get recent count from check
            $recentCount = $check->Recent ?? 0;
            
            return [
                'total_messages' => $check->Nmsgs ?? 0,
                'unread' => $unreadCount,
                'recent' => $recentCount,
                'today' => $todayCount,
                'mailbox_size_mb' => round(($check->Nmsgs ?? 0) * 0.05, 2), // Estimate: ~50KB per message
            ];
        } catch (\Exception $e) {
            \Log::error("IMAP stats error: " . $e->getMessage());
            return [
                'total_messages' => 0,
                'unread' => 0,
                'recent' => 0,
                'today' => 0,
                'mailbox_size_mb' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
}
