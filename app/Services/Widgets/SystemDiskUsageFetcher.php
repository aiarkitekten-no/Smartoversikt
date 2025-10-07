<?php
# START disk-usage-fetcher / System Disk Usage widget fetcher
# Hash: 8f2a9c4b7e1d
# Purpose: Fetch disk space usage data

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemDiskUsageFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.disk-usage';
    
    /**
     * Refresh interval: 60 seconds
     */
    protected int $refreshIntervalSeconds = 60;
    
    /**
     * Fetch disk usage data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'partitions' => $this->getDiskUsage(),
            'network' => $this->getNetworkTraffic(),
        ];
    }
    
    /**
     * Get disk usage for important partitions
     * 
     * @return array
     */
    protected function getDiskUsage(): array
    {
        $result = ReadonlyCommand::run('df -h');
        
        if (!$result['success']) {
            return [];
        }
        
        $lines = explode("\n", trim($result['output']));
        $partitions = [];
        
        // Important mount points to monitor
        $importantMounts = ['/', '/var', '/tmp', '/home'];
        
        foreach ($lines as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 6 && in_array($parts[5], $importantMounts)) {
                $usedPercent = (int) rtrim($parts[4], '%');
                $partitions[] = [
                    'filesystem' => $parts[0],
                    'mount' => $parts[5],
                    'size' => $parts[1],
                    'used' => $parts[2],
                    'available' => $parts[3],
                    'percent' => $usedPercent,
                    'status' => $usedPercent >= 90 ? 'critical' : ($usedPercent >= 80 ? 'warning' : 'normal'),
                ];
            }
        }
        
        return $partitions;
    }
    
    /**
     * Get network traffic statistics
     * 
     * @return array
     */
    protected function getNetworkTraffic(): array
    {
        $result = ReadonlyCommand::run('cat /proc/net/dev');
        
        if (!$result['success']) {
            return [
                'rx_mbps' => 0,
                'tx_mbps' => 0,
                'total_mbps' => 0,
            ];
        }
        
        // Read twice with 100ms delay
        $stats1 = $this->parseNetDev($result['output']);
        usleep(100000); // 100ms
        $result2 = ReadonlyCommand::run('cat /proc/net/dev');
        $stats2 = $this->parseNetDev($result2['output']);
        
        // Sum all interfaces except lo
        $rxBytes = 0;
        $txBytes = 0;
        
        foreach ($stats2 as $interface => $data) {
            if ($interface === 'lo') continue;
            if (!isset($stats1[$interface])) continue;
            
            $rxBytes += $data['rx_bytes'] - $stats1[$interface]['rx_bytes'];
            $txBytes += $data['tx_bytes'] - $stats1[$interface]['tx_bytes'];
        }
        
        // Multiply by 10 since we only waited 100ms
        $rxMbps = round((($rxBytes * 10) * 8) / 1024 / 1024, 2);
        $txMbps = round((($txBytes * 10) * 8) / 1024 / 1024, 2);
        
        return [
            'rx_mbps' => $rxMbps,
            'tx_mbps' => $txMbps,
            'total_mbps' => round($rxMbps + $txMbps, 2),
        ];
    }
    
    /**
     * Parse /proc/net/dev output
     * 
     * @param string $output
     * @return array
     */
    protected function parseNetDev(string $output): array
    {
        $stats = [];
        $lines = explode("\n", trim($output));
        
        foreach ($lines as $line) {
            if (preg_match('/^\s*(\w+):\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $matches)) {
                $stats[$matches[1]] = [
                    'rx_bytes' => (int) $matches[2],
                    'tx_bytes' => (int) $matches[3],
                ];
            }
        }
        
        return $stats;
    }
}
# END disk-usage-fetcher
