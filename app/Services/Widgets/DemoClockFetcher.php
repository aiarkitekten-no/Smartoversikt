<?php
# START 4c8a1f6e9b2d / Demo Clock Widget Fetcher
# Hash: 4c8a1f6e9b2d
# Purpose: Enkel test-widget som viser klokke og system-info

namespace App\Services\Widgets;

use Carbon\Carbon;

class DemoClockFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'demo.clock';
    protected int $refreshInterval = 10; // 10 sekunder

    /**
     * Hent klokke og system-info
     */
    protected function fetchData(): array
    {
        $now = Carbon::now('Europe/Oslo');

        return [
            'timestamp' => $now->toIso8601String(),
            'time' => $now->format('H:i:s'),
            'date' => $now->isoFormat('dddd, D. MMMM YYYY'),
            'timezone' => 'Europe/Oslo',
            'server' => [
                'hostname' => gethostname(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
            'stats' => [
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            ],
        ];
    }
}
# SLUTT 4c8a1f6e9b2d
