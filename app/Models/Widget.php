<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'order',
        'default_settings',
        'default_refresh_interval',
        'is_active',
    ];

    protected $casts = [
        'default_settings' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Default ordering: by order column, then by name
     */
    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('order')->orderBy('name');
        });
    }

    /**
     * Scope: filter by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: only active widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Hent siste snapshot for denne widgeten
     */
    public function latestSnapshot()
    {
        return $this->hasOne(WidgetSnapshot::class, 'widget_key', 'key')
            ->latest('fresh_at');
    }

    /**
     * Alle snapshots for denne widgeten
     */
    public function snapshots()
    {
        return $this->hasMany(WidgetSnapshot::class, 'widget_key', 'key');
    }
}
