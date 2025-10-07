<?php
# START 9d3b7f2e4c1a / Base Widget Fetcher
# Hash: 9d3b7f2e4c1a
# Purpose: Abstract base class for alle widget fetchers

namespace App\Services\Widgets;

use App\Models\WidgetSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class BaseWidgetFetcher
{
    /**
     * Widget key (må overstyres av child)
     */
    protected string $widgetKey;

    /**
     * Refresh interval i sekunder (kan overstyres)
     */
    protected int $refreshInterval = 300;
    
    /**
     * User widget instance (optional, for user-specific data)
     */
    protected ?\App\Models\UserWidget $userWidget = null;
    
    /**
     * Set user widget for user-specific fetchers
     */
    public function setUserWidget(\App\Models\UserWidget $userWidget): self
    {
        $this->userWidget = $userWidget;
        return $this;
    }

    /**
     * Hent data for widgeten (må implementeres av child)
     */
    abstract protected function fetchData(): array;
    
    /**
     * Public method to fetch data (for user-specific widgets)
     */
    public function fetch(): array
    {
        return $this->fetchData();
    }

    /**
     * Hent eller oppdater snapshot
     */
    public function getSnapshot(bool $forceRefresh = false): ?WidgetSnapshot
    {
        if (!$forceRefresh) {
            $existing = $this->getLatestSnapshot();
            
            if ($existing && $existing->isFresh()) {
                return $existing;
            }
        }

        return $this->refreshSnapshot();
    }

    /**
     * Hent siste snapshot fra DB
     */
    protected function getLatestSnapshot(): ?WidgetSnapshot
    {
        return WidgetSnapshot::where('widget_key', $this->widgetKey)
            ->latest('fresh_at')
            ->first();
    }

    /**
     * Refresh snapshot med ny data
     */
    public function refreshSnapshot(): WidgetSnapshot
    {
        try {
            $data = $this->fetchData();
            
            $snapshot = WidgetSnapshot::create([
                'widget_key' => $this->widgetKey,
                'payload' => $data,
                'fresh_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addSeconds($this->refreshInterval),
                'status' => 'success',
                'error_message' => null,
            ]);

            Log::info("Widget snapshot refreshed: {$this->widgetKey}", [
                'data_size' => strlen(json_encode($data)),
            ]);

            return $snapshot;

        } catch (\Exception $e) {
            Log::error("Widget fetch failed: {$this->widgetKey}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return WidgetSnapshot::create([
                'widget_key' => $this->widgetKey,
                'payload' => ['error' => true],
                'fresh_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addMinutes(5),
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sjekk om snapshot trenger refresh
     */
    public function needsRefresh(): bool
    {
        $snapshot = $this->getLatestSnapshot();

        if (!$snapshot) {
            return true;
        }

        return $snapshot->isExpired();
    }
}
# SLUTT 9d3b7f2e4c1a
