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
     */
    protected function getQuicklinks(): array
    {
        // Check if user widget is set (user-specific widget)
        if (!$this->userWidget) {
            return [];
        }

        // Check if quicklinks table exists
        if (!\Schema::hasTable('quicklinks')) {
            return [];
        }

        // Get user's quicklinks from database
        $links = \DB::table('quicklinks')
            ->where('user_id', $this->userWidget->user_id)
            ->orderBy('sort_order')
            ->get();

        return $links->map(function ($link) {
            return [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'sort_order' => $link->sort_order,
            ];
        })->toArray();
    }
}
