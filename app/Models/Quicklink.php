<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quicklink extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Default ordering by sort_order
     */
    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('sort_order')->orderBy('id');
        });
    }

    /**
     * User who owns this quicklink
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
