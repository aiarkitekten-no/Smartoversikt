<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWidget extends Model
{
    protected $fillable = [
        'user_id',
        'widget_id',
        'settings',
        'refresh_interval',
        'position',
        'is_visible',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_visible' => 'boolean',
        'position' => 'integer',
        'refresh_interval' => 'integer',
    ];

    /**
     * Default ordering by position
     */
    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('position')->orderBy('id');
        });
    }

    /**
     * User who owns this widget
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The widget definition
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * Scope: only visible widgets
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Get the effective refresh interval (user override or widget default)
     */
    public function getEffectiveRefreshInterval(): int
    {
        return $this->refresh_interval ?? $this->widget->default_refresh_interval ?? 300;
    }

    /**
     * Get the effective settings (merge user settings with widget defaults)
     */
    public function getEffectiveSettings(): array
    {
        $defaults = $this->widget->default_settings ?? [];
        $userSettings = $this->settings ?? [];
        
        return array_merge($defaults, $userSettings);
    }

    /**
     * Get the latest snapshot for this widget
     */
    public function getLatestSnapshot()
    {
        return $this->widget->latestSnapshot;
    }
    
    /**
     * Get widget data with user-specific settings applied
     * For widgets that need user-specific configuration (GitHub, Uptime, etc.)
     */
    public function getData(): ?array
    {
        // Check if this widget needs user-specific data
        $userSpecificWidgets = ['dev.github', 'monitoring.uptime', 'news.rss'];
        
        if (in_array($this->widget->key, $userSpecificWidgets)) {
            return $this->fetchUserSpecificData();
        }
        
        // For global widgets, use the shared snapshot
        $snapshot = $this->getLatestSnapshot();
        return $snapshot ? $snapshot->payload : null;
    }
    
    /**
     * Fetch user-specific widget data
     */
    protected function fetchUserSpecificData(): ?array
    {
        $catalog = config('widgets.catalog');
        $fetcherClass = $catalog[$this->widget->key]['fetcher'] ?? null;
        
        if (!$fetcherClass || !class_exists($fetcherClass)) {
            return null;
        }
        
        try {
            $fetcher = new $fetcherClass();
            $fetcher->setUserWidget($this);
            
            // Fetch fresh data for this user
            $data = $fetcher->fetch();
            
            return $data;
        } catch (\Exception $e) {
            \Log::error("Failed to fetch user-specific data for {$this->widget->key}: " . $e->getMessage());
            return null;
        }
    }
}


