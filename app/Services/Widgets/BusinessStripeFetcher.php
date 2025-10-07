<?php

namespace App\Services\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BusinessStripeFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'business.stripe';
    
    /**
     * Refresh interval: 300 seconds (5 min)
     */
    protected int $refreshIntervalSeconds = 300;
    
    /**
     * Fetch Stripe data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        // For demo purposes, generate realistic mock data
        // In production, integrate with Stripe API
        
        $today = $this->getTodaySales();
        $thisMonth = $this->getMonthSales();
        $yesterday = $this->getYesterdaySales();
        $lastMonth = $this->getLastMonthSales();
        
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'today' => $today,
            'yesterday' => $yesterday,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'monthly_goal' => 150000, // NOK
            'top_products' => $this->getTopProducts(),
            'recent_transactions' => $this->getRecentTransactions(),
            'performance_status' => $this->determinePerformanceStatus($thisMonth['total'], 150000),
        ];
    }
    
    /**
     * Get today's sales data
     */
    protected function getTodaySales(): array
    {
        $hour = (int)date('H');
        $baseAmount = rand(5000, 15000);
        $transactionCount = rand(10, 50);
        
        // Simulate growth during the day
        $multiplier = min(1, $hour / 24);
        $amount = round($baseAmount * $multiplier);
        $count = round($transactionCount * $multiplier);
        
        return [
            'total' => $amount,
            'count' => $count,
            'average_order' => $count > 0 ? round($amount / $count) : 0,
        ];
    }
    
    /**
     * Get yesterday's sales
     */
    protected function getYesterdaySales(): array
    {
        return [
            'total' => rand(8000, 20000),
            'count' => rand(20, 60),
        ];
    }
    
    /**
     * Get this month's sales
     */
    protected function getMonthSales(): array
    {
        $dayOfMonth = (int)date('d');
        $baseAmount = rand(80000, 140000);
        
        // Simulate growth through the month
        $multiplier = $dayOfMonth / 31;
        $amount = round($baseAmount * $multiplier);
        $count = rand(200, 800);
        
        return [
            'total' => $amount,
            'count' => $count,
            'average_order' => round($amount / $count),
        ];
    }
    
    /**
     * Get last month's total
     */
    protected function getLastMonthSales(): array
    {
        return [
            'total' => rand(120000, 160000),
            'count' => rand(500, 1000),
        ];
    }
    
    /**
     * Get top products
     */
    protected function getTopProducts(): array
    {
        return [
            ['name' => 'Premium Plan', 'revenue' => rand(20000, 40000), 'count' => rand(50, 150)],
            ['name' => 'Standard Plan', 'revenue' => rand(15000, 30000), 'count' => rand(80, 200)],
            ['name' => 'Basic Plan', 'revenue' => rand(10000, 20000), 'count' => rand(100, 250)],
        ];
    }
    
    /**
     * Get recent transactions
     */
    protected function getRecentTransactions(): array
    {
        $transactions = [];
        $products = ['Premium Plan', 'Standard Plan', 'Basic Plan', 'Add-on Service'];
        
        for ($i = 0; $i < 5; $i++) {
            $minutesAgo = rand(1, 120);
            $transactions[] = [
                'amount' => rand(199, 1999),
                'product' => $products[array_rand($products)],
                'time' => Carbon::now()->subMinutes($minutesAgo)->toIso8601String(),
                'customer' => 'Kunde #' . rand(1000, 9999),
            ];
        }
        
        return $transactions;
    }
    
    /**
     * Determine performance status
     */
    protected function determinePerformanceStatus(int $current, int $goal): string
    {
        $percentage = ($current / $goal) * 100;
        
        if ($percentage >= 100) {
            return 'exceeded'; // Goal exceeded!
        } elseif ($percentage >= 80) {
            return 'on_track'; // On track
        } elseif ($percentage >= 50) {
            return 'behind'; // Behind schedule
        } else {
            return 'critical'; // Critical - far behind
        }
    }
}
