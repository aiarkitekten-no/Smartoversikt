<?php

namespace App\Services\Widgets;

use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ToolsBillsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'tools.bills';
    
    /**
     * Refresh interval: 300 seconds (5 minutes)
     */
    protected int $refreshIntervalSeconds = 300;
    
    /**
     * Fetch bills/forfall data
     */
    protected function fetchData(): array
    {
        // Get user from userWidget or Auth
        $user = $this->userWidget?->user ?? Auth::user();
        
        if (!$user) {
            return $this->getEmptyData();
        }
        
        // Get all bills for user, sorted by due day
        $bills = Bill::where('user_id', $user->id)
            ->orderBy('sort_order')
            ->orderBy('due_day')
            ->get();
        
        // Transform bills with calculated fields
        $billsData = $bills->map(function ($bill) {
            return [
                'id' => $bill->id,
                'name' => $bill->name,
                'amount' => $bill->amount,
                'formatted_amount' => $bill->formatted_amount,
                'due_day' => $bill->due_day,
                'is_paid_this_month' => $bill->is_paid_this_month,
                'days_until_due' => $bill->days_until_due,
                'urgency_level' => $bill->urgency_level,
                'sort_order' => $bill->sort_order,
            ];
        })->values();
        
        // Calculate totals
        $totalMonthly = $bills->sum('amount');
        $remainingThisMonth = $bills->where('is_paid_this_month', false)->sum('amount');
        
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'bills' => $billsData,
            'totals' => [
                'monthly_total' => $totalMonthly,
                'formatted_monthly_total' => number_format($totalMonthly, 2, ',', ' ') . ' kr',
                'remaining_this_month' => $remainingThisMonth,
                'formatted_remaining' => number_format($remainingThisMonth, 2, ',', ' ') . ' kr',
                'paid_count' => $bills->where('is_paid_this_month', true)->count(),
                'total_count' => $bills->count(),
            ],
            'current_day' => Carbon::today()->day,
            'current_month' => Carbon::today()->format('F Y'),
        ];
    }
    
    /**
     * Get empty data structure
     */
    protected function getEmptyData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'bills' => [],
            'totals' => [
                'monthly_total' => 0,
                'formatted_monthly_total' => '0,00 kr',
                'remaining_this_month' => 0,
                'formatted_remaining' => '0,00 kr',
                'paid_count' => 0,
                'total_count' => 0,
            ],
            'current_day' => Carbon::today()->day,
            'current_month' => Carbon::today()->format('F Y'),
        ];
    }
}
