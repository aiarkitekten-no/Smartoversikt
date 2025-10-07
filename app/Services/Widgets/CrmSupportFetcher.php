<?php

namespace App\Services\Widgets;

class CrmSupportFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'crm.support';
    
    protected function fetchData(): array
    {
        try {
            // Mock support ticket data
            // In production: Integrate with Zendesk, Freshdesk, or custom support system
            
            $hour = (int) now()->format('H');
            $isBusinessHours = $hour >= 8 && $hour <= 17;
            
            return [
                'tickets' => [
                    'open' => rand(8, 25),
                    'pending' => rand(3, 12),
                    'on_hold' => rand(1, 5),
                    'solved_today' => rand(5, 15),
                    'new_today' => rand(3, 12),
                ],
                'priority' => [
                    'urgent' => rand(0, 3),
                    'high' => rand(2, 8),
                    'normal' => rand(5, 15),
                    'low' => rand(1, 5),
                ],
                'response_time' => [
                    'avg_first_response_hours' => round(rand(1, 4) + rand(0, 59) / 60, 1),
                    'avg_resolution_hours' => round(rand(4, 24) + rand(0, 59) / 60, 1),
                ],
                'satisfaction' => [
                    'score' => rand(85, 98),
                    'responses_this_week' => rand(15, 40),
                ],
                'recent_tickets' => [
                    [
                        'id' => '#' . rand(1000, 9999),
                        'subject' => 'Innloggingsproblem',
                        'priority' => 'high',
                        'status' => 'open',
                        'age_hours' => rand(1, 24),
                    ],
                    [
                        'id' => '#' . rand(1000, 9999),
                        'subject' => 'Fakturaspørsmål',
                        'priority' => 'normal',
                        'status' => 'pending',
                        'age_hours' => rand(1, 48),
                    ],
                    [
                        'id' => '#' . rand(1000, 9999),
                        'subject' => 'Feature request',
                        'priority' => 'low',
                        'status' => 'open',
                        'age_hours' => rand(24, 120),
                    ],
                ],
                'agents' => [
                    'online' => $isBusinessHours ? rand(3, 6) : rand(0, 2),
                    'total' => 6,
                ],
                'status' => 'ok',
                'note' => 'Demo data - integrate with support system API',
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
