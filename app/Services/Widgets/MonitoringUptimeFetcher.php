<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonitoringUptimeFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'monitoring.uptime';
    
    /**
     * Refresh interval: 60 seconds
     */
    protected int $refreshIntervalSeconds = 60;
    
    /**
     * Fetch uptime data for monitored websites
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        $websites = $this->getMonitoredWebsites();
        $results = [];
        $totalUptime = 0;
        $uptimeCount = 0;
        
        foreach ($websites as $site) {
            $check = $this->checkWebsite($site);
            $results[] = $check;
            
            if ($check['is_up']) {
                $uptimeCount++;
            }
            $totalUptime += $check['uptime_24h'];
        }
        
        $averageUptime = count($websites) > 0 ? round($totalUptime / count($websites), 2) : 100;
        
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'websites' => $results,
            'total_monitored' => count($websites),
            'currently_up' => $uptimeCount,
            'currently_down' => count($websites) - $uptimeCount,
            'average_uptime_24h' => $averageUptime,
            'overall_status' => $this->determineOverallStatus($results),
        ];
    }
    
    /**
     * Get list of websites to monitor from settings
     * 
     * @return array
     */
    protected function getMonitoredWebsites(): array
    {
        // Get from user settings
        $websites = $this->userWidget->settings['websites'] ?? null;
        $timeout = $this->userWidget->settings['timeout'] ?? 5;
        
        // If new format (array of websites)
        if (is_array($websites) && count($websites) > 0) {
            return array_map(function($site) use ($timeout) {
                return [
                    'name' => $site['name'] ?? parse_url($site['url'], PHP_URL_HOST),
                    'url' => $site['url'],
                    'expected_status' => 200,
                    'timeout' => $timeout,
                ];
            }, $websites);
        }
        
        // Fallback to old single URL format for backwards compatibility
        $url = $this->userWidget->settings['url'] ?? 'https://smartesider.no';
        $parsed = parse_url($url);
        $name = $parsed['host'] ?? $url;
        
        return [
            [
                'name' => $name,
                'url' => $url,
                'expected_status' => 200,
                'timeout' => $timeout,
            ],
        ];
    }
    
    /**
     * Check website status
     * 
     * @param array $site
     * @return array
     */
    protected function checkWebsite(array $site): array
    {
        $startTime = microtime(true);
        $isUp = false;
        $statusCode = 0;
        $responseTime = 0;
        $error = null;
        
        $timeout = $site['timeout'] ?? 5;
        
        try {
            $response = Http::timeout($timeout)->get($site['url']);
            $responseTime = round((microtime(true) - $startTime) * 1000); // milliseconds
            $statusCode = $response->status();
            $isUp = $statusCode == ($site['expected_status'] ?? 200);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $responseTime = round((microtime(true) - $startTime) * 1000);
        }
        
        // Get or initialize uptime history
        $historyKey = 'uptime_history_' . md5($site['url']);
        $history = Cache::get($historyKey, []);
        
        // Add current check to history
        $history[] = [
            'timestamp' => Carbon::now()->timestamp,
            'is_up' => $isUp,
            'response_time' => $responseTime,
        ];
        
        // Keep only last 24 hours of data (1440 minutes)
        $history = array_filter($history, function($check) {
            return $check['timestamp'] > (Carbon::now()->subDay()->timestamp);
        });
        
        Cache::put($historyKey, array_values($history), 86400); // Keep for 24 hours
        
        // Calculate 24h uptime percentage
        $upCount = count(array_filter($history, fn($c) => $c['is_up']));
        $uptime24h = count($history) > 0 ? round(($upCount / count($history)) * 100, 2) : 100;
        
        // Calculate average response time
        $avgResponseTime = count($history) > 0 
            ? round(array_sum(array_column($history, 'response_time')) / count($history), 0)
            : $responseTime;
        
        return [
            'name' => $site['name'],
            'url' => $site['url'],
            'is_up' => $isUp,
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'avg_response_time_24h' => $avgResponseTime,
            'uptime_24h' => $uptime24h,
            'last_check' => Carbon::now()->toIso8601String(),
            'error' => $error,
            'checks_24h' => count($history),
        ];
    }
    
    /**
     * Determine overall status based on all websites
     * 
     * @param array $results
     * @return string
     */
    protected function determineOverallStatus(array $results): string
    {
        $downCount = count(array_filter($results, fn($r) => !$r['is_up']));
        $totalCount = count($results);
        
        if ($totalCount === 0) {
            return 'unknown';
        }
        
        if ($downCount === 0) {
            return 'all_up';
        } elseif ($downCount < $totalCount) {
            return 'partial_down';
        } else {
            return 'all_down';
        }
    }
}
