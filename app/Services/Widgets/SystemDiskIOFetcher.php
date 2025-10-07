<?php
# START disk-io-fetcher / System Disk I/O widget fetcher
# Hash: 5b9c3e7a1f2d
# Purpose: Fetch disk I/O statistics

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemDiskIOFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.disk-io';
    
    /**
     * Refresh interval: 30 seconds
     */
    protected int $refreshIntervalSeconds = 30;
    
    /**
     * Fetch disk I/O data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'io' => $this->getDiskIO(),
        ];
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
# END disk-io-fetcher
