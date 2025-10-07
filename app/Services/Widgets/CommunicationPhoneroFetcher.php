<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommunicationPhoneroFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'communication.phonero';
    
    /**
     * Refresh interval: 60 seconds for real-time call data
     */
    protected int $refreshIntervalSeconds = 60;
    
    /**
     * Fetch Phonero data
     */
    protected function fetchData(): array
    {
        $apiUrl = config('phonero.api_url', 'https://bedriftsnett-api.phonero.net');
        $username = config('phonero.username');
        $password = config('phonero.password');
        $customerId = config('phonero.customer_id', 1);

        if (!$username || !$password) {
            return $this->getMockData();
        }

        try {
            // Create HTTP client with cookie jar for session persistence
            $jar = new \GuzzleHttp\Cookie\CookieJar();
            $client = Http::withOptions(['cookies' => $jar]);
            
            // Authenticate
            $this->authenticate($client, $apiUrl, $username, $password);
            
            // Get queue status first (needed for calllog)
            $queueStatus = $this->getQueueStatus($client, $apiUrl, $customerId);
            $queueId = $queueStatus['queue_id'] ?? null;
            
            return [
                'timestamp' => Carbon::now()->toIso8601String(),
                'recent_calls' => $queueId ? $this->getRecentCalls($client, $apiUrl, $customerId, $queueId) : [],
                'queue_status' => $queueStatus,
                'main_numbers' => ['69020070', '69020071'],
                'ready' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Phonero API error: ' . $e->getMessage());
            return $this->getMockData();
        }
    }
    
    /**
     * Authenticate with Phonero API
     */
    protected function authenticate($client, string $apiUrl, string $username, string $password): void
    {
        $response = $client->timeout(10)->post("{$apiUrl}/authenticate", [
            'username' => $username,
            'password' => $password,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Phonero authentication failed');
        }

        $data = $response->json();
        $sessionId = $data['sessionId'] ?? '';
        
        if (empty($sessionId)) {
            throw new \Exception('No sessionId received from Phonero API');
        }
        
        // Session is now stored in cookie jar, no need to return
    }
    
    /**
     * Get recent calls from queue calllog
     */
    protected function getRecentCalls($client, string $apiUrl, int $customerId, string $queueId): array
    {
        // Get queue call log
        $response = $client->timeout(10)->get("{$apiUrl}/queues/calllog/{$customerId}/{$queueId}");

        if (!$response->successful()) {
            Log::warning('Phonero calllog fetch failed', ['status' => $response->status()]);
            return [];
        }

        $callLog = $response->json()['result'] ?? [];
        $recentCalls = [];

        foreach ($callLog as $call) {
            $duration = 0;
            if (isset($call['answerDate'], $call['exitDate'])) {
                $answerTime = strtotime($call['answerDate']);
                $exitTime = strtotime($call['exitDate']);
                $duration = max(0, $exitTime - $answerTime);
            }
            
            $recentCalls[] = [
                'direction' => $call['direction'] ?? 'unknown',
                'from' => $call['callingParty'] ?? '',
                'to' => $call['queueId'] ?? '',
                'result' => $call['callResult'] ?? 'unknown',
                'timestamp' => $call['enterDate'] ?? Carbon::now()->toIso8601String(),
                'duration' => $duration,
            ];
        }

        // Sort by timestamp desc and take last 3
        usort($recentCalls, fn($a, $b) => strcmp($b['timestamp'], $a['timestamp']));
        return array_slice($recentCalls, 0, 3);
    }
    
    /**
     * Get queue status
     */
    protected function getQueueStatus($client, string $apiUrl, int $customerId): array
    {
        $response = $client->timeout(10)->get("{$apiUrl}/queues/{$customerId}");

        if (!$response->successful()) {
            return [];
        }

        $queues = $response->json()['result'] ?? [];
        
        // Return first queue (usually reception/switchboard)
        if (!empty($queues)) {
            $queue = $queues[0];
            return [
                'name' => $queue['description'] ?? 'Sentralbord',
                'queue_id' => $queue['queueId'] ?? null,
                'short_number' => $queue['shortnumber'] ?? '',
                'members_count' => count($queue['members'] ?? []),
                'ready_members' => count(array_filter($queue['members'] ?? [], fn($m) => $m['ready'] ?? false)),
            ];
        }

        return [];
    }
    
    /**
     * Get mock data for demo
     */
    protected function getMockData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'recent_calls' => [
                [
                    'direction' => 'inbound',
                    'from' => '98765432',
                    'to' => '69020070',
                    'state' => 'answered',
                    'timestamp' => Carbon::now()->subMinutes(5)->toIso8601String(),
                    'type' => 'external',
                ],
                [
                    'direction' => 'outbound',
                    'from' => '69020071',
                    'to' => '22334455',
                    'state' => 'completed',
                    'timestamp' => Carbon::now()->subMinutes(15)->toIso8601String(),
                    'type' => 'external',
                ],
            ],
            'queue_status' => [
                'name' => 'Sentralbord',
                'queue_id' => '1',
                'short_number' => '200',
                'members_count' => 3,
                'ready_members' => 2,
            ],
            'main_numbers' => ['69020070', '69020071'],
            'ready' => false,
        ];
    }
}
