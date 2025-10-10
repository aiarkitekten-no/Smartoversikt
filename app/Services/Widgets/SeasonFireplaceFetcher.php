<?php

namespace App\Services\Widgets;

use Carbon\Carbon;

class SeasonFireplaceFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'season.fireplace';
    protected int $refreshIntervalSeconds = 3600;

    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'ready' => true,
        ];
    }
}
