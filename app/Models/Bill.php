<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Bill extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'due_day',
        'is_paid_this_month',
        'sort_order',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid_this_month' => 'boolean',
        'due_day' => 'integer',
        'sort_order' => 'integer',
    ];
    
    /**
     * Relationship: Bill belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Calculate days until due date
     */
    public function getDaysUntilDueAttribute(): int
    {
        $today = Carbon::today();
        $currentDay = $today->day;
        $dueDay = $this->due_day;
        
        // If due day hasn't passed this month
        if ($dueDay >= $currentDay) {
            $dueDate = Carbon::create($today->year, $today->month, $dueDay);
            return $today->diffInDays($dueDate, false);
        }
        
        // Due day has passed, calculate for next month
        $nextMonth = $today->copy()->addMonth();
        $dueDate = Carbon::create($nextMonth->year, $nextMonth->month, min($dueDay, $nextMonth->daysInMonth));
        return $today->diffInDays($dueDate, false);
    }
    
    /**
     * Get urgency level (green, yellow, red)
     */
    public function getUrgencyLevelAttribute(): string
    {
        $days = $this->days_until_due;
        
        if ($days < 5) {
            return 'red';
        } elseif ($days <= 7) {
            return 'yellow';
        }
        return 'green';
    }
    
    /**
     * Get formatted amount with NOK
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2, ',', ' ') . ' kr';
    }
}
