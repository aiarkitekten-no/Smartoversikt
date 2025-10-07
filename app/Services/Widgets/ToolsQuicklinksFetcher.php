<?php

namespace App\Services\Widgets;

use Carbon\Carbon;

class ToolsQuicklinksFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'tools.quicklinks';
    
    /**
     * Refresh interval: 3600 seconds (1 hour) - mostly static data
     */
    protected int $refreshIntervalSeconds = 3600;
    
    /**
     * Fetch quicklinks data
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'links' => $this->getQuicklinks(),
            'ready' => true,
        ];
    }
    
    /**
     * Get quicklinks from database/storage
     * For now returns empty array - links will be managed via API
     */
    protected function getQuicklinks(): array
    {
        // Links are stored in user preferences or separate table
        // For now, return empty array
        return [];
    }
}
