<?php
# START network-fetcher / System Network Traffic widget fetcher
# Hash: 3d6e8a2f9c1b
# Purpose: Fetch network traffic statistics

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemNetworkFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.network';
    
    /**
     * Refresh interval: 30 seconds
     */
    protected int $refreshIntervalSeconds = 30;
    
    /**
     * Fetch network traffic data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'traffic' => $this->getNetworkTraffic(),
        ];
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
# END network-fetcher
