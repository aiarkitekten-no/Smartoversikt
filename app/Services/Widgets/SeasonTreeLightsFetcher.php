<?php

namespace App\Services\Widgets;

use Carbon\Carbon;

class SeasonTreeLightsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'season.tree-lights';
    protected int $refreshIntervalSeconds = 3600;

    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'ready' => true,
        ];
    }
}
