<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailAccount extends Model
{
    protected $fillable = [
        'name',
        'type',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'validate_cert',
        'is_active',
        'check_interval',
        'metadata',
    ];

    protected $casts = [
        'validate_cert' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Set the password (encrypted)
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = encrypt($value);
    }

    /**
     * Get the decrypted password
     */
    public function getPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get masked password for display
     */
    public function getMaskedPasswordAttribute(): string
    {
        return str_repeat('•', 8);
    }

    /**
     * Scope for IMAP accounts only
     */
    public function scopeImap($query)
    {
        return $query->where('type', 'imap');
    }

    /**
     * Scope for SMTP accounts only
     */
    public function scopeSmtp($query)
    {
        return $query->where('type', 'smtp');
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
