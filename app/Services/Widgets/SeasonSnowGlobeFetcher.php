<?php

namespace App\Services\Widgets;

use Carbon\Carbon;

class SeasonSnowGlobeFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'season.snow-globe';
    protected int $refreshIntervalSeconds = 3600;

    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'ready' => true,
        ];
    }
}
