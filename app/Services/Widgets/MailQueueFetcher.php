<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Illuminate\Support\Facades\DB;

class MailQueueFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'mail.queue';
    
    protected function fetchData(): array
    {
        try {
            // Laravel queue statistics
            $laravelQueue = $this->getLaravelQueueStats();
            
            // System mail queue (Postfix/qshape)
            $systemQueue = $this->getSystemMailQueue();
            
            return [
                'laravel' => $laravelQueue,
                'system' => $systemQueue,
                'total_pending' => ($laravelQueue['pending'] ?? 0) + ($systemQueue['total'] ?? 0),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    private function getLaravelQueueStats(): array
    {
        try {
            // Count pending jobs in database queue
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            
            return [
                'pending' => $pending,
                'failed' => $failed,
                'status' => $failed > 10 ? 'warning' : 'ok',
            ];
        } catch (\Exception $e) {
            return [
                'pending' => 0,
                'failed' => 0,
                'status' => 'unknown',
                'error' => 'Database not configured for queues',
            ];
        }
    }

    private function getSystemMailQueue(): array
    {
        // Return unavailable status on Plesk/restricted environments
        // System mail queue monitoring requires elevated permissions
        return [
            'deferred' => 0,
            'active' => 0,
            'total' => 0,
            'status' => 'unavailable',
            'note' => 'System queue monitoring ikke tilgjengelig',
        ];
    }
}
