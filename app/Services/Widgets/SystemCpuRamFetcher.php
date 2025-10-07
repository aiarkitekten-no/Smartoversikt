<?php
# START 3c6e1a8f4b9d / System CPU and RAM widget fetcher
# Hash: 3c6e1a8f4b9d
# Purpose: Fetch CPU and RAM usage data using ReadonlyCommand

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemCpuRamFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.cpu-ram';
    
    /**
     * Refresh interval: 30 seconds
     */
    protected int $refreshIntervalSeconds = 30;
    
    /**
     * Fetch CPU and RAM data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'memory' => $this->getMemoryInfo(),
            'load_average' => $this->getLoadAverage(),
            'cpu_cores' => $this->getCpuCores(),
            'cpu_usage' => $this->getCpuUsage(),
            'top_processes' => $this->getTopProcesses(),
            'disk_io' => $this->getDiskIO(),
        ];
    }
    
    /**
     * Get memory information from /proc/meminfo
     * 
     * @return array
     */
    protected function getMemoryInfo(): array
    {
        $result = ReadonlyCommand::run('cat /proc/meminfo');
        
        if (!$result['success']) {
            Log::warning('Failed to read /proc/meminfo', ['error' => $result['error']]);
            return [
                'total' => 0,
                'free' => 0,
                'available' => 0,
                'used' => 0,
                'used_percent' => 0,
                'formatted' => [
                    'total' => '0 MB',
                    'free' => '0 MB',
                    'available' => '0 MB',
                    'used' => '0 MB',
                ],
            ];
        }
        
        // Parse /proc/meminfo
        $lines = explode("\n", trim($result['output']));
        $meminfo = [];
        
        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s+kB/', $line, $matches)) {
                $meminfo[$matches[1]] = (int) $matches[2] * 1024; // Convert kB to bytes
            }
        }
        
        $total = $meminfo['MemTotal'] ?? 0;
        $free = $meminfo['MemFree'] ?? 0;
        $available = $meminfo['MemAvailable'] ?? $free;
        $used = $total - $available;
        $usedPercent = $total > 0 ? round(($used / $total) * 100, 1) : 0;
        
        return [
            'total' => $total,
            'free' => $free,
            'available' => $available,
            'used' => $used,
            'used_percent' => $usedPercent,
            'buffers' => $meminfo['Buffers'] ?? 0,
            'cached' => $meminfo['Cached'] ?? 0,
            'swap_total' => $meminfo['SwapTotal'] ?? 0,
            'swap_free' => $meminfo['SwapFree'] ?? 0,
            'swap_used' => ($meminfo['SwapTotal'] ?? 0) - ($meminfo['SwapFree'] ?? 0),
            'formatted' => [
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'available' => $this->formatBytes($available),
                'used' => $this->formatBytes($used),
                'swap_total' => $this->formatBytes($meminfo['SwapTotal'] ?? 0),
                'swap_used' => $this->formatBytes(($meminfo['SwapTotal'] ?? 0) - ($meminfo['SwapFree'] ?? 0)),
            ],
        ];
    }
    
    /**
     * Get load average from /proc/loadavg
     * 
     * @return array
     */
    protected function getLoadAverage(): array
    {
        $result = ReadonlyCommand::run('cat /proc/loadavg');
        
        if (!$result['success']) {
            Log::warning('Failed to read /proc/loadavg', ['error' => $result['error']]);
            return [
                '1min' => 0.0,
                '5min' => 0.0,
                '15min' => 0.0,
                'running_processes' => 0,
                'total_processes' => 0,
            ];
        }
        
        // /proc/loadavg format: "0.52 0.58 0.59 1/234 12345"
        $parts = explode(' ', trim($result['output']));
        $processParts = explode('/', $parts[3] ?? '0/0');
        
        return [
            '1min' => (float) ($parts[0] ?? 0.0),
            '5min' => (float) ($parts[1] ?? 0.0),
            '15min' => (float) ($parts[2] ?? 0.0),
            'running_processes' => (int) ($processParts[0] ?? 0),
            'total_processes' => (int) ($processParts[1] ?? 0),
        ];
    }
    
    /**
     * Format bytes to human-readable string
     * 
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        
        $value = $bytes / pow(1024, $power);
        
        return round($value, 2) . ' ' . $units[$power];
    }
    
    /**
     * Get number of CPU cores
     * 
     * @return int
     */
    protected function getCpuCores(): int
    {
        $result = ReadonlyCommand::run('nproc');
        
        if (!$result['success']) {
            // Fallback: try counting from /proc/cpuinfo
            $cpuInfoResult = ReadonlyCommand::run('grep -c ^processor /proc/cpuinfo');
            if ($cpuInfoResult['success']) {
                return (int) trim($cpuInfoResult['output']);
            }
            
            Log::warning('Failed to get CPU core count', ['error' => $result['error']]);
            return 2; // Default fallback
        }
        
        return (int) trim($result['output']);
    }
    
    /**
     * Get CPU usage percentage
     * 
     * @return array
     */
    protected function getCpuUsage(): array
    {
        // Read CPU stats from /proc/stat twice with small delay
        $result1 = ReadonlyCommand::run('cat /proc/stat | grep "^cpu "');
        
        if (!$result1['success']) {
            return [
                'total' => 0,
                'user' => 0,
                'system' => 0,
                'idle' => 0,
            ];
        }
        
        // Parse first measurement
        $stats1 = $this->parseCpuStats($result1['output']);
        
        // Small delay
        usleep(100000); // 100ms
        
        // Second measurement
        $result2 = ReadonlyCommand::run('cat /proc/stat | grep "^cpu "');
        
        if (!$result2['success']) {
            return [
                'total' => 0,
                'user' => 0,
                'system' => 0,
                'idle' => 0,
            ];
        }
        
        $stats2 = $this->parseCpuStats($result2['output']);
        
        // Calculate differences
        $total_diff = $stats2['total'] - $stats1['total'];
        
        if ($total_diff == 0) {
            return [
                'total' => 0,
                'user' => 0,
                'system' => 0,
                'idle' => 100,
            ];
        }
        
        return [
            'total' => round((($total_diff - ($stats2['idle'] - $stats1['idle'])) / $total_diff) * 100, 1),
            'user' => round((($stats2['user'] - $stats1['user']) / $total_diff) * 100, 1),
            'system' => round((($stats2['system'] - $stats1['system']) / $total_diff) * 100, 1),
            'idle' => round((($stats2['idle'] - $stats1['idle']) / $total_diff) * 100, 1),
        ];
    }
    
    /**
     * Parse CPU stats from /proc/stat line
     * 
     * @param string $line
     * @return array
     */
    protected function parseCpuStats(string $line): array
    {
        // Format: cpu  user nice system idle iowait irq softirq steal guest guest_nice
        $parts = preg_split('/\s+/', trim($line));
        array_shift($parts); // Remove "cpu" label
        
        $user = (int) ($parts[0] ?? 0);
        $nice = (int) ($parts[1] ?? 0);
        $system = (int) ($parts[2] ?? 0);
        $idle = (int) ($parts[3] ?? 0);
        $iowait = (int) ($parts[4] ?? 0);
        $irq = (int) ($parts[5] ?? 0);
        $softirq = (int) ($parts[6] ?? 0);
        
        $total = $user + $nice + $system + $idle + $iowait + $irq + $softirq;
        
        return [
            'user' => $user,
            'system' => $system,
            'idle' => $idle,
            'total' => $total,
        ];
    }
    
    /**
     * Get top processes by CPU and memory usage
     * 
     * @return array
     */
    protected function getTopProcesses(): array
    {
        // Get top 5 processes by CPU
        $cpuResult = ReadonlyCommand::run('ps aux --sort=-%cpu | head -6 | tail -5');
        
        $topCpu = [];
        if ($cpuResult['success']) {
            $lines = explode("\n", trim($cpuResult['output']));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line), 11);
                if (count($parts) >= 11) {
                    $topCpu[] = [
                        'user' => $parts[0],
                        'pid' => $parts[1],
                        'cpu' => (float) $parts[2],
                        'mem' => (float) $parts[3],
                        'command' => $this->cleanCommand($parts[10]),
                    ];
                }
            }
        }
        
        // Get top 5 processes by memory
        $memResult = ReadonlyCommand::run('ps aux --sort=-%mem | head -6 | tail -5');
        
        $topMem = [];
        if ($memResult['success']) {
            $lines = explode("\n", trim($memResult['output']));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line), 11);
                if (count($parts) >= 11) {
                    $topMem[] = [
                        'user' => $parts[0],
                        'pid' => $parts[1],
                        'cpu' => (float) $parts[2],
                        'mem' => (float) $parts[3],
                        'command' => $this->cleanCommand($parts[10]),
                    ];
                }
            }
        }
        
        return [
            'by_cpu' => array_slice($topCpu, 0, 3), // Top 3
            'by_memory' => array_slice($topMem, 0, 3), // Top 3
        ];
    }
    
    /**
     * Clean command string for display
     * 
     * @param string $command
     * @return string
     */
    protected function cleanCommand(string $command): string
    {
        // Remove long paths, keep just the command name
        $command = basename($command);
        
        // Truncate if too long
        if (strlen($command) > 30) {
            $command = substr($command, 0, 27) . '...';
        }
        
        return $command;
    }
    
    /**
     * Get disk I/O statistics
     * 
     * @return array
     */
    protected function getDiskIO(): array
    {
        $result = ReadonlyCommand::run('cat /proc/diskstats');
        
        if (!$result['success']) {
            return [
                'read_mb_s' => 0,
                'write_mb_s' => 0,
                'total_mb_s' => 0,
            ];
        }
        
        // Read diskstats twice with 100ms delay to calculate rate
        $stats1 = $this->parseDiskStats($result['output']);
        usleep(100000); // 100ms
        $result2 = ReadonlyCommand::run('cat /proc/diskstats');
        $stats2 = $this->parseDiskStats($result2['output']);
        
        // Calculate MB/s for main disk (sda or nvme0n1)
        $mainDisk = isset($stats1['sda']) ? 'sda' : (isset($stats1['nvme0n1']) ? 'nvme0n1' : 'vda');
        
        if (!isset($stats1[$mainDisk]) || !isset($stats2[$mainDisk])) {
            return [
                'read_mb_s' => 0,
                'write_mb_s' => 0,
                'total_mb_s' => 0,
                'disk' => 'unknown',
            ];
        }
        
        // Calculate bytes per second (multiply by 10 since we only waited 100ms)
        $readBytes = ($stats2[$mainDisk]['read_sectors'] - $stats1[$mainDisk]['read_sectors']) * 512 * 10;
        $writeBytes = ($stats2[$mainDisk]['write_sectors'] - $stats1[$mainDisk]['write_sectors']) * 512 * 10;
        
        $readMBs = round($readBytes / 1024 / 1024, 2);
        $writeMBs = round($writeBytes / 1024 / 1024, 2);
        
        return [
            'read_mb_s' => $readMBs,
            'write_mb_s' => $writeMBs,
            'total_mb_s' => round($readMBs + $writeMBs, 2),
            'disk' => $mainDisk,
        ];
    }
    
    /**
     * Parse disk stats from /proc/diskstats
     * 
     * @param string $output
     * @return array
     */
    protected function parseDiskStats(string $output): array
    {
        $stats = [];
        $lines = explode("\n", trim($output));
        
        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 14) {
                $device = $parts[2];
                // Only main disks (sda, nvme0n1, vda, md2)
                if (preg_match('/^(sda|nvme0n1|vda|md2)$/', $device)) {
                    $stats[$device] = [
                        'read_sectors' => (int) $parts[5],
                        'write_sectors' => (int) $parts[9],
                    ];
                }
            }
        }
        
        return $stats;
    }
}
# END 3c6e1a8f4b9d
