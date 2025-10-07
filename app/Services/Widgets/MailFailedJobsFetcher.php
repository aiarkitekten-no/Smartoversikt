<?php

namespace App\Services\Widgets;

use Illuminate\Support\Facades\DB;

class MailFailedJobsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'mail.failed-jobs';
    
    protected function fetchData(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')
                ->select('id', 'queue', 'exception', 'failed_at')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get();

            $total = DB::table('failed_jobs')->count();
            
            // Count failures by queue
            $byQueue = DB::table('failed_jobs')
                ->select('queue', DB::raw('COUNT(*) as count'))
                ->groupBy('queue')
                ->get()
                ->pluck('count', 'queue')
                ->toArray();

            // Recent failures (last 24h)
            $recent = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();

            return [
                'total' => $total,
                'recent_24h' => $recent,
                'by_queue' => $byQueue,
                'latest' => $failedJobs->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'queue' => $job->queue,
                        'error' => $this->extractErrorMessage($job->exception),
                        'failed_at' => $job->failed_at,
                    ];
                })->toArray(),
                'status' => $total > 10 ? 'critical' : ($total > 0 ? 'warning' : 'ok'),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed jobs table not available',
                'total' => 0,
                'status' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    private function extractErrorMessage(string $exception): string
    {
        // Extract first line of exception
        $lines = explode("\n", $exception);
        $firstLine = $lines[0] ?? 'Unknown error';
        
        // Truncate if too long
        return strlen($firstLine) > 100 
            ? substr($firstLine, 0, 97) . '...' 
            : $firstLine;
    }
}
