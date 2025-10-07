<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AnalyticsSmartesiderFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'analytics.smartesider';
    
    protected function fetchData(): array
    {
        try {
            // Cache for 5 minutes
            $data = Cache::remember('analytics_smartesider', 300, function () {
                return $this->fetchAnalytics();
            });
            
            return $data;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'status' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    private function fetchAnalytics(): array
    {
        // Mock realistic data for Smartesider.no
        // In production, this would fetch from your analytics API/database
        
        $now = now();
        $hour = (int) $now->format('H');
        
        // Simulate traffic patterns (higher during business hours)
        $baseVisitors = 150;
        $hourlyFactor = $hour >= 9 && $hour <= 17 ? 2.5 : 0.5;
        $currentVisitors = (int) ($baseVisitors * $hourlyFactor * (0.8 + rand(0, 40) / 100));
        
        return [
            'visitors' => [
                'current' => $currentVisitors,
                'today' => rand(1500, 2500),
                'yesterday' => rand(1400, 2400),
                'this_month' => rand(35000, 45000),
            ],
            'pageviews' => [
                'today' => rand(3000, 5000),
                'per_visitor' => round(rand(250, 350) / 100, 1),
            ],
            'top_pages' => [
                ['url' => '/sok', 'views' => rand(800, 1200)],
                ['url' => '/bedrift', 'views' => rand(400, 800)],
                ['url' => '/kart', 'views' => rand(300, 600)],
                ['url' => '/kontakt', 'views' => rand(200, 400)],
            ],
            'traffic_sources' => [
                'organic' => rand(45, 55),
                'direct' => rand(25, 35),
                'social' => rand(5, 15),
                'referral' => rand(5, 10),
            ],
            'performance' => [
                'avg_load_time' => round(rand(180, 350) / 100, 2),
                'bounce_rate' => rand(35, 45),
            ],
            'status' => 'ok',
            'note' => 'Demo data - integrate with real analytics API',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
