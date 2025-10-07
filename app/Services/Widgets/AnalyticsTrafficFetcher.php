<?php

namespace App\Services\Widgets;

class AnalyticsTrafficFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'analytics.traffic';
    
    protected function fetchData(): array
    {
        try {
            // Real-time traffic overview
            // In production: Parse web server access logs or use analytics service
            
            $now = now();
            $hour = (int) $now->format('H');
            
            // Business hours simulation
            $isBusinessHours = $hour >= 8 && $hour <= 18;
            $trafficMultiplier = $isBusinessHours ? 1.5 : 0.3;
            
            return [
                'current_users' => (int) (rand(50, 150) * $trafficMultiplier),
                'requests_per_minute' => (int) (rand(200, 500) * $trafficMultiplier),
                'bandwidth' => [
                    'current_mbps' => round(rand(50, 200) * $trafficMultiplier / 10, 1),
                    'total_today_gb' => round(rand(100, 300) / 10, 1),
                ],
                'response_time' => [
                    'avg_ms' => rand(80, 200),
                    'p95_ms' => rand(200, 500),
                    'p99_ms' => rand(400, 1000),
                ],
                'http_status' => [
                    '2xx' => rand(85, 95),
                    '3xx' => rand(3, 8),
                    '4xx' => rand(1, 5),
                    '5xx' => rand(0, 2),
                ],
                'top_endpoints' => [
                    '/api/search' => rand(100, 300),
                    '/api/company' => rand(50, 150),
                    '/' => rand(80, 200),
                    '/assets/*' => rand(200, 500),
                ],
                'geographic' => [
                    'NO' => rand(70, 85),
                    'SE' => rand(5, 12),
                    'DK' => rand(3, 8),
                    'Other' => rand(2, 10),
                ],
                'status' => 'ok',
                'note' => 'Demo data - integrate with web server logs',
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
