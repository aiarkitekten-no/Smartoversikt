<?php

namespace App\Services\Widgets;

class CrmPipedriveFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'crm.pipedrive';
    
    protected function fetchData(): array
    {
        try {
            // Mock Pipedrive data
            // In production: Use Pipedrive API with token from config
            
            return [
                'deals' => [
                    'open' => rand(15, 35),
                    'won_this_month' => rand(5, 15),
                    'lost_this_month' => rand(2, 8),
                    'value_open' => rand(500000, 1500000),
                    'value_won_this_month' => rand(200000, 800000),
                ],
                'pipeline' => [
                    [
                        'stage' => 'Kvalifisering',
                        'count' => rand(5, 12),
                        'value' => rand(100000, 400000),
                    ],
                    [
                        'stage' => 'Forhandling',
                        'count' => rand(3, 8),
                        'value' => rand(200000, 600000),
                    ],
                    [
                        'stage' => 'Forslag sendt',
                        'count' => rand(2, 6),
                        'value' => rand(150000, 500000),
                    ],
                ],
                'activities' => [
                    'overdue' => rand(0, 5),
                    'today' => rand(3, 10),
                    'this_week' => rand(15, 35),
                ],
                'recent_wins' => [
                    ['name' => 'Acme Corp - Nettside', 'value' => 45000, 'date' => now()->subDays(1)->format('Y-m-d')],
                    ['name' => 'Nordic AS - SEO pakke', 'value' => 35000, 'date' => now()->subDays(3)->format('Y-m-d')],
                    ['name' => 'Smartdata - Konsulent', 'value' => 120000, 'date' => now()->subDays(5)->format('Y-m-d')],
                ],
                'conversion_rate' => round(rand(20, 40), 1),
                'status' => 'ok',
                'note' => 'Demo data - integrate with Pipedrive API',
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'status' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }
}
