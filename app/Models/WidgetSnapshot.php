<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WidgetSnapshot extends Model
{
    protected $fillable = [
        'widget_key',
        'payload',
        'fresh_at',
        'expires_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'fresh_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Sjekk om snapshot er utløpt
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Sjekk om snapshot er ferskt (ikke utløpt)
     */
    public function isFresh(): bool
    {
        return !$this->isExpired() && $this->status === 'success';
    }

    /**
     * Hent alder i sekunder
     */
    public function ageInSeconds(): int
    {
        return Carbon::now()->diffInSeconds($this->fresh_at);
    }
}
