<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RssFeed extends Model
{
    protected $fillable = [
        'name',
        'url',
        'category',
        'is_active',
        'refresh_interval',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Scope to get only active feeds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get feeds by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
