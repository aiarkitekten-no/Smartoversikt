<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SystemMegaBoxFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.megabox';

    protected int $cacheTTL = 30; // 30 seconds - frequent updates for system stats

    /**
     * Fetch comprehensive system performance data
     */
    protected function fetchData(): array
    {
        try {
            $data = [
                'cpu' => $this->getCpuStats(),
                'memory' => $this->getMemoryStats(),
                'disk' => $this->getDiskStats(),
                'network' => $this->getNetworkStats(),
                'processes' => $this->getProcessStats(),
                'timestamp' => now()->toIso8601String(),
            ];
            
            // Store history for graph (7 days)
            $this->storeHistory($data);
            
            // Add history to response
            $data['history'] = $this->getHistory();
            
            return $data;
        } catch (\Exception $e) {
            Log::error('MegaBox fetch failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'error' => 'Failed to fetch system stats',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Store current stats in history
     */
    protected function storeHistory(array $data): void
    {
        $cacheKey = 'megabox.history';
        $history = Cache::get($cacheKey, []);
        
        // Add current data point
        $history[] = [
            'timestamp' => now()->toIso8601String(),
            'cpu_load' => $data['cpu']['loadavg']['1min_percent'] ?? 0,
            'memory_used' => $data['memory']['used_percent'] ?? 0,
            'day' => now()->format('Y-m-d'),
        ];
        
        // Keep only last 7 days of hourly data
        $sevenDaysAgo = now()->subDays(7);
        $history = array_filter($history, function($item) use ($sevenDaysAgo) {
            return strtotime($item['timestamp']) >= $sevenDaysAgo->timestamp;
        });
        
        // Keep only one entry per hour to avoid overflow
        $hourlyHistory = [];
        foreach ($history as $item) {
            $hour = date('Y-m-d H:00', strtotime($item['timestamp']));
            if (!isset($hourlyHistory[$hour])) {
                $hourlyHistory[$hour] = $item;
            }
        }
        
        Cache::put($cacheKey, array_values($hourlyHistory), 60 * 60 * 24 * 8); // 8 days
    }

    /**
     * Get daily averages for graph
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
     * Get CPU statistics per core
     */
    protected function getCpuStats(): array
    {
        $cpuInfo = [];
        $cores = [];
        
        // Get number of CPU cores
        if (file_exists('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/processor\s+:\s+(\d+)/', $cpuinfo, $matches);
            $coreCount = count($matches[1]);
            
            // Get CPU model
            preg_match('/model name\s+:\s+(.+)/', $cpuinfo, $modelMatch);
            $cpuModel = $modelMatch[1] ?? 'Unknown CPU';
        } else {
            $coreCount = 12; // Fallback
            $cpuModel = 'Unknown CPU';
        }

        // Get load average
        if (file_exists('/proc/loadavg')) {
            $loadavg = file_get_contents('/proc/loadavg');
            $loads = explode(' ', $loadavg);
            
            $load1 = (float)($loads[0] ?? 0);
            $load5 = (float)($loads[1] ?? 0);
            $load15 = (float)($loads[2] ?? 0);
            
            // Calculate percentage per core
            $load1Percent = ($load1 / $coreCount) * 100;
            $load5Percent = ($load5 / $coreCount) * 100;
            $load15Percent = ($load15 / $coreCount) * 100;
        } else {
            $load1 = $load5 = $load15 = 0;
            $load1Percent = $load5Percent = $load15Percent = 0;
        }

        // Get per-core usage from /proc/stat (current snapshot)
        if (file_exists('/proc/stat')) {
            $stat = file_get_contents('/proc/stat');
            preg_match_all('/cpu(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $stat, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $coreId = (int)$match[1];
                $user = (int)$match[2];
                $nice = (int)$match[3];
                $system = (int)$match[4];
                $idle = (int)$match[5];
                
                $total = $user + $nice + $system + $idle;
                $usage = $total > 0 ? (($total - $idle) / $total) * 100 : 0;
                
                $cores[] = [
                    'core' => $coreId,
                    'usage' => round($usage, 1),
                ];
            }
        }

        return [
            'model' => trim($cpuModel),
            'cores' => $coreCount,
            'loadavg' => [
                '1min' => $load1,
                '5min' => $load5,
                '15min' => $load15,
                '1min_percent' => round($load1Percent, 1),
                '5min_percent' => round($load5Percent, 1),
                '15min_percent' => round($load15Percent, 1),
            ],
            'per_core' => $cores,
        ];
    }

    /**
     * Get memory statistics
     */
    protected function getMemoryStats(): array
    {
        if (!file_exists('/proc/meminfo')) {
            return ['error' => 'Cannot read /proc/meminfo'];
        }

        $meminfo = file_get_contents('/proc/meminfo');
        
        // Parse memory info
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemFree:\s+(\d+)/', $meminfo, $free);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        preg_match('/Buffers:\s+(\d+)/', $meminfo, $buffers);
        preg_match('/Cached:\s+(\d+)/', $meminfo, $cached);
        preg_match('/SwapTotal:\s+(\d+)/', $meminfo, $swapTotal);
        preg_match('/SwapFree:\s+(\d+)/', $meminfo, $swapFree);
        
        $totalKb = (int)($total[1] ?? 0);
        $freeKb = (int)($free[1] ?? 0);
        $availableKb = (int)($available[1] ?? 0);
        $buffersKb = (int)($buffers[1] ?? 0);
        $cachedKb = (int)($cached[1] ?? 0);
        $swapTotalKb = (int)($swapTotal[1] ?? 0);
        $swapFreeKb = (int)($swapFree[1] ?? 0);
        
        $usedKb = $totalKb - $freeKb - $buffersKb - $cachedKb;
        $usedPercent = $totalKb > 0 ? ($usedKb / $totalKb) * 100 : 0;
        $availablePercent = $totalKb > 0 ? ($availableKb / $totalKb) * 100 : 0;
        
        $swapUsedKb = $swapTotalKb - $swapFreeKb;
        $swapUsedPercent = $swapTotalKb > 0 ? ($swapUsedKb / $swapTotalKb) * 100 : 0;

        return [
            'total_mb' => round($totalKb / 1024, 0),
            'total_gb' => round($totalKb / 1024 / 1024, 1),
            'used_mb' => round($usedKb / 1024, 0),
            'used_gb' => round($usedKb / 1024 / 1024, 1),
            'free_mb' => round($freeKb / 1024, 0),
            'available_mb' => round($availableKb / 1024, 0),
            'available_gb' => round($availableKb / 1024 / 1024, 1),
            'buffers_mb' => round($buffersKb / 1024, 0),
            'cached_mb' => round($cachedKb / 1024, 0),
            'used_percent' => round($usedPercent, 1),
            'available_percent' => round($availablePercent, 1),
            'swap' => [
                'total_mb' => round($swapTotalKb / 1024, 0),
                'used_mb' => round($swapUsedKb / 1024, 0),
                'free_mb' => round($swapFreeKb / 1024, 0),
                'used_percent' => round($swapUsedPercent, 1),
            ],
        ];
    }

    /**
     * Get disk statistics
     */
    protected function getDiskStats(): array
    {
        $disks = [];
        
        // Get disk usage for root partition
        $rootStats = [
            'mount' => '/',
            'total_gb' => round(disk_total_space('/') / 1024 / 1024 / 1024, 1),
            'free_gb' => round(disk_free_space('/') / 1024 / 1024 / 1024, 1),
            'used_gb' => 0,
            'used_percent' => 0,
        ];
        
        $rootStats['used_gb'] = round($rootStats['total_gb'] - $rootStats['free_gb'], 1);
        $rootStats['used_percent'] = $rootStats['total_gb'] > 0 
            ? round(($rootStats['used_gb'] / $rootStats['total_gb']) * 100, 1) 
            : 0;
        
        $disks[] = $rootStats;

        // Get I/O stats if available
        $iostat = ['reads' => 0, 'writes' => 0];
        if (file_exists('/proc/diskstats')) {
            $diskstats = file_get_contents('/proc/diskstats');
            // Parse main disk (usually sda or vda)
            if (preg_match('/\s+((?:s|v|xv)da)\s+\d+\s+\d+\s+(\d+)\s+\d+\s+\d+\s+\d+\s+(\d+)/', $diskstats, $match)) {
                $iostat['reads'] = (int)$match[2];
                $iostat['writes'] = (int)$match[3];
            }
        }

        return [
            'partitions' => $disks,
            'io' => $iostat,
        ];
    }

    /**
     * Get network statistics
     */
    protected function getNetworkStats(): array
    {
        $interfaces = [];
        
        if (file_exists('/proc/net/dev')) {
            $netdev = file_get_contents('/proc/net/dev');
            $lines = explode("\n", $netdev);
            
            foreach ($lines as $line) {
                // Skip header lines
                if (strpos($line, ':') === false) {
                    continue;
                }
                
                // Parse interface data
                preg_match('/^\s*([^:]+):\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $match);
                if ($match) {
                    $interface = trim($match[1]);
                    $rxBytes = (int)$match[2];
                    $txBytes = (int)$match[3];
                    
                    // Skip loopback
                    if ($interface === 'lo') {
                        continue;
                    }
                    
                    $interfaces[] = [
                        'name' => $interface,
                        'rx_mb' => round($rxBytes / 1024 / 1024, 1),
                        'tx_mb' => round($txBytes / 1024 / 1024, 1),
                        'rx_gb' => round($rxBytes / 1024 / 1024 / 1024, 2),
                        'tx_gb' => round($txBytes / 1024 / 1024 / 1024, 2),
                    ];
                }
            }
        }

        return [
            'interfaces' => $interfaces,
        ];
    }

    /**
     * Get process statistics
     */
    protected function getProcessStats(): array
    {
        $stats = [
            'total' => 0,
            'running' => 0,
            'sleeping' => 0,
            'zombie' => 0,
        ];

        if (file_exists('/proc/stat')) {
            $stat = file_get_contents('/proc/stat');
            if (preg_match('/procs_running\s+(\d+)/', $stat, $match)) {
                $stats['running'] = (int)$match[1];
            }
            if (preg_match('/procs_blocked\s+(\d+)/', $stat, $match)) {
                $stats['blocked'] = (int)$match[1];
            }
        }

        // Count total processes
        if (is_dir('/proc')) {
            $procs = glob('/proc/[0-9]*', GLOB_ONLYDIR);
            $stats['total'] = count($procs);
        }

        return $stats;
    }
}
