<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SystemLoadGraphFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.loadgraph';

    protected int $cacheTTL = 3600; // 1 hour - historical data doesn't change often

    /**
     * Fetch 7-day load average graph data
     */
    protected function fetchData(): array
    {
        try {
            // Get history from MegaBox cache or build our own
            $history = $this->getHistory();
            
            return [
                'history' => $history,
                'max_cpu' => $this->getMaxValue($history, 'avg_cpu'),
                'max_memory' => $this->getMaxValue($history, 'avg_memory'),
                'avg_cpu' => $this->getAverageValue($history, 'avg_cpu'),
                'avg_memory' => $this->getAverageValue($history, 'avg_memory'),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Load Graph fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to fetch load graph',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get historical data (reuse from MegaBox)
     */
    protected function getHistory(): array
    {
        $history = Cache::get('megabox.history', []);
        
        if (empty($history)) {
            return [];
        }
        
        // Group by day and calculate averages
        $dailyStats = [];
        foreach ($history as $item) {
            $day = $item['day'];
            if (!isset($dailyStats[$day])) {
                $dailyStats[$day] = [
                    'day' => $day,
                    'cpu_loads' => [],
                    'memory_usages' => [],
                ];
            }
            $dailyStats[$day]['cpu_loads'][] = $item['cpu_load'];
            $dailyStats[$day]['memory_usages'][] = $item['memory_used'];
        }
        
        // Calculate averages
        $result = [];
        foreach ($dailyStats as $day => $stats) {
            $result[] = [
                'day' => $day,
                'avg_cpu' => round(array_sum($stats['cpu_loads']) / count($stats['cpu_loads']), 1),
                'avg_memory' => round(array_sum($stats['memory_usages']) / count($stats['memory_usages']), 1),
            ];
        }
        
        // Sort by day and return last 7 days
        usort($result, fn($a, $b) => strcmp($a['day'], $b['day']));
        return array_slice($result, -7);
    }

    /**
     * Get maximum value from history
     */
    protected function getMaxValue(array $history, string $key): float
    {
        if (empty($history)) {
            return 0;
        }
        
        $values = array_column($history, $key);
        return max($values);
    }

    /**
     * Get average value from history
     */
    protected function getAverageValue(array $history, string $key): float
    {
        if (empty($history)) {
            return 0;
        }
        
        $values = array_column($history, $key);
        return round(array_sum($values) / count($values), 1);
    }
}
