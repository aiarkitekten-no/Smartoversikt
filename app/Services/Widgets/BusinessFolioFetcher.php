<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BusinessFolioFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'business.folio';
    
    /**
     * Refresh interval: 300 seconds (5 min)
     */
    protected int $refreshIntervalSeconds = 300;
    
    /**
     * Fetch Folio account data
     */
    protected function fetchData(): array
    {
        // Get credentials from environment variables directly
        $cookie = env('FOLIO_COOKIE');
        $orgNumber = env('FOLIO_ORG');
        
        if (!$cookie || !$orgNumber) {
            Log::warning('Folio credentials not configured in .env', [
                'has_cookie' => !empty($cookie),
                'has_org' => !empty($orgNumber)
            ]);
            return $this->getMockData();
        }
        
        try {
            return $this->fetchRealFolioData($cookie, $orgNumber);
        } catch (\Exception $e) {
            Log::error('Folio API fetch failed: ' . $e->getMessage());
            return $this->getMockData();
        }
    }
    
    /**
     * Fetch real data from Folio GraphQL API
     */
    protected function fetchRealFolioData(string $cookie, string $orgNumber): array
    {
        $headers = [
            'Cookie' => "folioSession={$cookie}",
            'folio-org-number' => $orgNumber,
            'content-type' => 'application/json',
            'accept' => 'application/json',
            'origin' => 'https://app.folio.no',
        ];
        
        // Fetch balance
        $balanceQuery = <<<'GQL'
        {
          organization {
            accountsInfo {
              totalBalance
              accounts {
                accountNumber
                name
                balanceNok {
                  asNumericString
                }
              }
            }
          }
        }
        GQL;
        
        $balanceResponse = Http::withHeaders($headers)
            ->timeout(30)
            ->post('https://app.folio.no/graphql', [
                'query' => $balanceQuery,
            ]);
        
        if (!$balanceResponse->successful()) {
            throw new \Exception('Failed to fetch balance: ' . $balanceResponse->status());
        }
        
        $balanceData = $balanceResponse->json();
        
        // Fetch activities (last 30 days)
        $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        
        $activitiesQuery = <<<GQL
        query Activities {
          activities(between: { startDate: "{$startDate}", endDate: "{$endDate}" }) {
            items {
              id
              startedAt
              merchant {
                name
              }
              nokAmount {
                asNumericString
              }
              strings {
                description
              }
            }
          }
        }
        GQL;
        
        $activitiesResponse = Http::withHeaders($headers)
            ->timeout(30)
            ->post('https://app.folio.no/graphql', [
                'query' => $activitiesQuery,
            ]);
        
        if (!$activitiesResponse->successful()) {
            throw new \Exception('Failed to fetch activities: ' . $activitiesResponse->status());
        }
        
        $activitiesData = $activitiesResponse->json();
        
        // Process data
        $accountsInfo = $balanceData['data']['organization']['accountsInfo'] ?? [];
        $totalBalance = $accountsInfo['totalBalance'] ?? '0';
        $accounts = $accountsInfo['accounts'] ?? [];
        
        $activities = $activitiesData['data']['activities']['items'] ?? [];
        
        // Separate incoming and outgoing transactions
        $incoming = [];
        $outgoing = [];
        
        foreach ($activities as $activity) {
            $amount = floatval(str_replace(',', '', $activity['nokAmount']['asNumericString'] ?? '0'));
            
            $transaction = [
                'date' => $activity['startedAt'] ?? '',
                'merchant' => $activity['merchant']['name'] ?? 'Ukjent',
                'description' => $activity['strings']['description'] ?? '',
                'amount' => $activity['nokAmount']['asNumericString'] ?? '0',
                'amount_numeric' => $amount,
            ];
            
            if ($amount > 0) {
                $incoming[] = $transaction;
            } else {
                $outgoing[] = $transaction;
            }
        }
        
        // Sort by date desc and take top 2
        usort($incoming, fn($a, $b) => strcmp($b['date'], $a['date']));
        usort($outgoing, fn($a, $b) => strcmp($b['date'], $a['date']));
        
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'balance' => [
                'total' => $totalBalance,
                'accounts_count' => count($accounts),
            ],
            'recent_incoming' => array_slice($incoming, 0, 2),
            'recent_outgoing' => array_slice($outgoing, 0, 2),
            'total_transactions' => count($activities),
        ];
    }
    
    /**
     * Get mock data for demo
     */
    protected function getMockData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'balance' => [
                'total' => '245678.50',
                'accounts_count' => 2,
            ],
            'recent_incoming' => [
                [
                    'date' => Carbon::now()->subDays(1)->toIso8601String(),
                    'merchant' => 'Kunde AS',
                    'description' => 'Faktura #1234',
                    'amount' => '15000.00',
                    'amount_numeric' => 15000.00,
                ],
                [
                    'date' => Carbon::now()->subDays(3)->toIso8601String(),
                    'merchant' => 'ABC Bedrift',
                    'description' => 'Betaling',
                    'amount' => '8500.00',
                    'amount_numeric' => 8500.00,
                ],
            ],
            'recent_outgoing' => [
                [
                    'date' => Carbon::now()->subDays(2)->toIso8601String(),
                    'merchant' => 'Leverandør XYZ',
                    'description' => 'Materialkjøp',
                    'amount' => '-4200.00',
                    'amount_numeric' => -4200.00,
                ],
                [
                    'date' => Carbon::now()->subDays(5)->toIso8601String(),
                    'merchant' => 'Strømselskap',
                    'description' => 'Månedlig strøm',
                    'amount' => '-1850.00',
                    'amount_numeric' => -1850.00,
                ],
            ],
            'total_transactions' => 47,
        ];
    }
}
