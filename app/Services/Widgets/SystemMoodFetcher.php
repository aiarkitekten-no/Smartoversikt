<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SystemMoodFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.mood';

    protected int $cacheTTL = 30; // 30 seconds

    /**
     * Fetch server mood based on CPU and RAM usage
     */
    protected function fetchData(): array
    {
        try {
            $cpu = $this->getCpuLoad();
            $ram = $this->getRamUsage();
            $avg = ($cpu + $ram) / 2;
            $now = now();

            // Streaks
            [$chillStreakSec, $okStreakSec] = $this->updateStreaks($avg, $now);

            // Coffee meter
            $coffee = $this->computeCoffee($avg, $now);

            // Pet happiness
            $pet = $this->updatePetHappiness($avg, $now);

            // Trend (24h hourly buckets)
            $trend = $this->updateTrend($avg, $now);
            
            return [
                'cpu_percent' => $cpu,
                'ram_percent' => $ram,
                'mood' => $this->calculateMood($cpu, $ram),
                'emoji' => $this->getMoodEmoji($cpu, $ram),
                'color' => $this->getMoodColor($cpu, $ram),
                'avg' => round($avg, 1),
                'streak_chill_seconds' => $chillStreakSec,
                'streak_ok_seconds' => $okStreakSec,
                'coffee' => $coffee,
                'pet' => $pet,
                'trend' => $trend,
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Server Mood fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to fetch server mood',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Keep two streaks in cache:
     *  - chill: avg < 40
     *  - ok: avg < 70
     */
    protected function updateStreaks(float $avg, \Illuminate\Support\Carbon $now): array
    {
        $lastKey = 'system_mood:last';
        $last = Cache::get($lastKey);
        $delta = 0;
        if ($last && isset($last['t'])) {
            $delta = max(0, $now->diffInSeconds($last['t']));
        }

        $chillKey = 'system_mood:streak:chill';
        $okKey = 'system_mood:streak:ok';

        $chill = Cache::get($chillKey, 0);
        $ok = Cache::get($okKey, 0);

        if ($delta > 0) {
            if ($avg < 40) {
                $chill += $delta;
            } else {
                $chill = 0;
            }
            if ($avg < 70) {
                $ok += $delta;
            } else {
                $ok = 0;
            }
        }

        Cache::put($chillKey, $chill, 86400);
        Cache::put($okKey, $ok, 86400);
        Cache::put($lastKey, ['t' => $now, 'avg' => $avg], 86400);

        return [$chill, $ok];
    }

    protected function computeCoffee(float $avg, \Illuminate\Support\Carbon $now): array
    {
        $hour = (int) $now->format('G');
        // Base cups by day time
        $base = 0; // night
        if ($hour >= 5 && $hour < 9) $base = 2; // morning boost
        elseif ($hour >= 9 && $hour < 14) $base = 1; // day
        elseif ($hour >= 14 && $hour < 18) $base = 1; // afternoon
        else $base = 0; // evening/night

        // Load influence
        $loadAdj = $avg >= 70 ? 2 : ($avg >= 50 ? 1 : 0);
        $cups = max(0, min(3, $base + $loadAdj));

        $texts = [
            0 => 'Koffeinfri zen',
            1 => 'En rolig kopp',
            2 => 'Trenger påfyll',
            3 => 'Barista mode'
        ];

        return [
            'level' => $cups, // 0..3
            'cups' => $cups,
            'text' => $texts[$cups] ?? 'Kaffestatus'
        ];
    }

    protected function updatePetHappiness(float $avg, \Illuminate\Support\Carbon $now): array
    {
        $key = 'system_mood:pet:happiness';
        $h = (int) Cache::get($key, 70);

        // Adjust by load
        if ($avg < 40) $h += 2;
        elseif ($avg < 70) $h += 0;
        else $h -= 2;

        $h = max(0, min(100, $h));
        Cache::put($key, $h, 86400);

        $level = $h >= 75 ? 'happy' : ($h >= 40 ? 'okay' : 'grumpy');
        $emoji = $level === 'happy' ? '🐶' : ($level === 'okay' ? '🐢' : '🐙');

        return [
            'happiness' => $h,
            'level' => $level,
            'emoji' => $emoji,
        ];
    }

    protected function updateTrend(float $avg, \Illuminate\Support\Carbon $now): array
    {
        $key = 'system_mood:trend';
        $trend = Cache::get($key, []);
        $hourKey = $now->format('YmdH');

        if (!isset($trend[$hourKey])) {
            $trend[$hourKey] = ['sum' => 0.0, 'count' => 0];
        }
        $trend[$hourKey]['sum'] += $avg;
        $trend[$hourKey]['count'] += 1;

        // Keep last 24 hours
        ksort($trend);
        if (count($trend) > 24) {
            $trend = array_slice($trend, -24, null, true);
        }

        Cache::put($key, $trend, 86400 * 2);

        // Build display array (0..100)
        $out = [];
        foreach ($trend as $bucket) {
            $mean = $bucket['count'] > 0 ? $bucket['sum'] / $bucket['count'] : 0;
            $out[] = round(max(0, min(100, $mean)), 1);
        }
        return $out;
    }

    /**
     * Get current CPU load percentage
     */
    protected function getCpuLoad(): float
    {
        $loadavg = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        
        return round(($loadavg[0] / $cpuCount) * 100, 1);
    }

    /**
     * Get CPU core count
     */
    protected function getCpuCount(): int
    {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        return count($matches[0]) ?: 1;
    }

    /**
     * Get RAM usage percentage
     */
    protected function getRamUsage(): float
    {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        
        $totalMem = $total[1] ?? 1;
        $availMem = $available[1] ?? 0;
        $usedMem = $totalMem - $availMem;
        
        return round(($usedMem / $totalMem) * 100, 1);
    }

    /**
     * Calculate mood based on CPU and RAM
     */
    protected function calculateMood(float $cpu, float $ram): string
    {
        $avg = ($cpu + $ram) / 2;
        
        if ($avg < 10) return '🥱 Gjesp... Kjeeeedelig!';
        if ($avg < 20) return '😌 Rolig som en fiskdam';
        if ($avg < 30) return '☕ Kaffe og chill';
        if ($avg < 40) return '💼 I gang med dagen';
        if ($avg < 50) return '🏃 Travelt, men ok';
        if ($avg < 60) return '😅 Begynner å svette';
        if ($avg < 70) return '🥵 Dette blir varmt!';
        if ($avg < 80) return '😰 Houston, vi har et problem';
        if ($avg < 90) return '🚨 RED ALERT!';
        return '🔥 MAYDAY MAYDAY MAYDAY!';
    }

    /**
     * Get mood emoji
     */
    protected function getMoodEmoji(float $cpu, float $ram): string
    {
        $avg = ($cpu + $ram) / 2;
        
        if ($avg < 20) return '😌';
        if ($avg < 40) return '☕';
        if ($avg < 60) return '💼';
        if ($avg < 80) return '😰';
        return '🚨';
    }

    /**
     * Get mood color class
     */
    protected function getMoodColor(float $cpu, float $ram): string
    {
        $avg = ($cpu + $ram) / 2;
        
        if ($avg < 40) return 'excellent';
        if ($avg < 70) return 'warning';
        return 'critical';
    }
}
