<?php

namespace App\Services\Widgets;

use App\Services\Widgets\BaseWidgetFetcher;
use Illuminate\Support\Facades\Cache;

class SystemCpuCoresFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.cpu-cores';
    
    protected int $cacheTTL = 30; // 30 seconds
    
    protected function fetchData(): array
    {
        return [
            'cpu' => $this->getCpuStats(),
        ];
    }

    private function getCpuStats(): array
    {
        // Read CPU info
        $cpuInfo = @file_get_contents('/proc/cpuinfo');
        $cores = 12; // Default
        $model = 'Unknown CPU';
        
        if ($cpuInfo) {
            // Count physical cores
            preg_match_all('/^processor\s*:/m', $cpuInfo, $matches);
            $cores = count($matches[0]);
            
            // Get CPU model
            if (preg_match('/model name\s*:\s*(.+)$/m', $cpuInfo, $modelMatch)) {
                $model = trim($modelMatch[1]);
            }
        }
        
        // Read load average
        $loadavg = @file_get_contents('/proc/loadavg');
        $loads = [0, 0, 0];
        if ($loadavg) {
            $parts = explode(' ', $loadavg);
            $loads = [
                (float)($parts[0] ?? 0),
                (float)($parts[1] ?? 0),
                (float)($parts[2] ?? 0),
            ];
        }
        
        // Get per-core usage
        $perCoreUsage = $this->getPerCoreUsage();
        
        return [
            'model' => $model,
            'cores' => $cores,
            'loadavg' => [
                '1min' => $loads[0],
                '5min' => $loads[1],
                '15min' => $loads[2],
                '1min_percent' => round(($loads[0] / $cores) * 100, 1),
                '5min_percent' => round(($loads[1] / $cores) * 100, 1),
                '15min_percent' => round(($loads[2] / $cores) * 100, 1),
            ],
            'per_core' => $perCoreUsage,
        ];
    }

    private function getPerCoreUsage(): array
    {
        // Get current stats
        $stat1 = @file_get_contents('/proc/stat');
        if (!$stat1) return [];
        
        usleep(100000); // 100ms delay
        
        $stat2 = @file_get_contents('/proc/stat');
        if (!$stat2) return [];
        
        $lines1 = explode("\n", $stat1);
        $lines2 = explode("\n", $stat2);
        
        $perCore = [];
        
        foreach ($lines1 as $i => $line1) {
            if (!preg_match('/^cpu(\d+)\s+/', $line1, $match)) continue;
            
            $coreNum = (int)$match[1];
            $line2 = $lines2[$i] ?? '';
            
            if (!preg_match('/^cpu\d+\s+(.+)$/', $line2)) continue;
            
            $vals1 = preg_split('/\s+/', trim(preg_replace('/^cpu\d+\s+/', '', $line1)));
            $vals2 = preg_split('/\s+/', trim(preg_replace('/^cpu\d+\s+/', '', $line2)));
            
            $idle1 = (int)($vals1[3] ?? 0);
            $idle2 = (int)($vals2[3] ?? 0);
            
            $total1 = array_sum(array_map('intval', $vals1));
            $total2 = array_sum(array_map('intval', $vals2));
            
            $totalDiff = $total2 - $total1;
            $idleDiff = $idle2 - $idle1;
            
            $usage = $totalDiff > 0 ? (($totalDiff - $idleDiff) / $totalDiff) * 100 : 0;
            
            $perCore[] = [
                'core' => $coreNum,
                'usage' => round($usage, 1),
            ];
        }
        
        return $perCore;
    }

    public function getCacheTTL(): int
    {
        return 30; // 30 seconds
    }
}
