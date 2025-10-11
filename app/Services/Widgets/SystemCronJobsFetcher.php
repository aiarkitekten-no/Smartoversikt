<?php

namespace App\Services\Widgets;

use Illuminate\Console\Scheduling\Schedule;
use Carbon\Carbon;

class SystemCronJobsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.cron-jobs';

    public function fetchData(): array
    {
        return [
            'jobs' => $this->getScheduledJobs(),
            'summary' => $this->getSummary(),
            'last_run' => $this->getLastSchedulerRun(),
            'crontab_running' => $this->isCrontabRunning(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getScheduledJobs(): array
    {
        $schedule = app(Schedule::class);
        $events = $schedule->events();
        $jobs = [];

        foreach ($events as $event) {
            $command = $event->command ?? $event->description ?? 'Unknown';
            
            // Clean up command for display
            $cleanCommand = $this->cleanCommandName($command);
            
            // Get next run time
            $nextRun = $event->nextRunDate();
            $nextRunCarbon = $nextRun ? Carbon::instance($nextRun) : null;
            
            // Estimate last run based on cron expression
            $cronExpression = $event->expression;
            $lastRun = $this->estimateLastRun($cronExpression);
            
            // Get status from job history (if available)
            $status = $this->getJobStatus($cleanCommand);

            $jobs[] = [
                'command' => $cleanCommand,
                'description' => $event->description ?? 'No description',
                'cron_expression' => $cronExpression,
                'cron_readable' => $this->humanReadableCron($cronExpression),
                'next_run' => $nextRunCarbon?->toIso8601String(),
                'next_run_formatted' => $nextRunCarbon?->format('d.m.Y H:i'),
                'next_run_relative' => $nextRunCarbon?->diffForHumans(),
                'last_run' => $lastRun?->toIso8601String(),
                'last_run_relative' => $lastRun?->diffForHumans(),
                'status' => $status,
                'timezone' => $event->timezone ?? config('app.timezone'),
            ];
        }

        // Sort by next run time
        usort($jobs, fn($a, $b) => ($a['next_run'] ?? 'Z') <=> ($b['next_run'] ?? 'Z'));

        return $jobs;
    }

    protected function cleanCommandName(string $command): string
    {
        // Remove PHP artisan prefix
        $command = str_replace("'/usr/bin/php8.2' 'artisan' ", '', $command);
        $command = str_replace("'/usr/bin/php' 'artisan' ", '', $command);
        $command = preg_replace("/^.*artisan\s+/", '', $command);
        
        // Remove quotes and extra whitespace
        $command = trim(str_replace("'", '', $command));
        
        // Remove output redirects
        $command = preg_replace('/\s*>>?\s*\/dev\/null.*$/', '', $command);
        $command = preg_replace('/\s*2>&1.*$/', '', $command);
        
        return $command;
    }

    protected function humanReadableCron(string $expression): string
    {
        $parts = explode(' ', $expression);
        if (count($parts) !== 5) {
            return $expression;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        // Common patterns
        if ($expression === '* * * * *') return 'Hvert minutt';
        if ($expression === '0 * * * *') return 'Hver time';
        if ($expression === '0 0 * * *') return 'Daglig kl. 00:00';
        if ($minute === '*/10') return 'Hver 10. minutt';
        if ($minute === '*/30') return 'Hver 30. minutt';
        if ($minute === '0' && $hour === '*/6') return 'Hver 6. time';
        if ($minute === '0' && $hour === '*/12') return 'Hver 12. time';

        return $expression;
    }

    protected function estimateLastRun(string $cronExpression): ?Carbon
    {
        // This is a simplified estimation
        // In production, you'd want to log actual runs to database
        
        if ($cronExpression === '* * * * *') {
            return Carbon::now()->subMinute();
        }
        
        if (str_contains($cronExpression, '*/10')) {
            return Carbon::now()->subMinutes(10);
        }
        
        if (str_contains($cronExpression, '*/30')) {
            return Carbon::now()->subMinutes(30);
        }
        
        if (str_starts_with($cronExpression, '0 *')) {
            return Carbon::now()->subHour();
        }
        
        if (str_starts_with($cronExpression, '0 0')) {
            return Carbon::now()->subDay();
        }

        return null;
    }

    protected function getJobStatus(string $command): string
    {
        // Check Laravel log for recent failures
        $logPath = storage_path('logs/laravel.log');
        
        if (file_exists($logPath)) {
            $result = \App\Support\Sys\ReadonlyCommand::run("tail -n 100 {$logPath} | grep -i '{$command}' | grep -i 'error\\|failed\\|exception' 2>/dev/null");
            
            if ($result['success'] && !empty($result['output'])) {
                return 'error';
            }
        }

        return 'ok';
    }

    protected function getSummary(): array
    {
        $jobs = $this->getScheduledJobs();
        
        $summary = [
            'total' => count($jobs),
            'active' => 0,
            'errors' => 0,
            'next_job' => null,
        ];

        foreach ($jobs as $job) {
            if ($job['status'] === 'ok') {
                $summary['active']++;
            } elseif ($job['status'] === 'error') {
                $summary['errors']++;
            }
        }

        if (!empty($jobs)) {
            $summary['next_job'] = [
                'command' => $jobs[0]['command'],
                'time' => $jobs[0]['next_run_relative'] ?? 'Unknown',
            ];
        }

        return $summary;
    }

    protected function getLastSchedulerRun(): ?array
    {
        // Check if scheduler is running by looking at log
        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            return null;
        }

        $result = \App\Support\Sys\ReadonlyCommand::run("tail -n 50 {$logPath} | grep 'Running scheduled command' | tail -1");
        
        if ($result['success'] && !empty($result['output'])) {
            // Try to extract timestamp
            if (preg_match('/\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2})/', $result['output'], $matches)) {
                $timestamp = Carbon::parse($matches[1]);
                return [
                    'timestamp' => $timestamp->toIso8601String(),
                    'relative' => $timestamp->diffForHumans(),
                    'status' => $timestamp->isAfter(Carbon::now()->subMinutes(2)) ? 'active' : 'stale',
                ];
            }
        }

        return null;
    }

    protected function isCrontabRunning(): bool
    {
        // Check if cron process exists (pgrep returns PIDs if found)
        $result = \App\Support\Sys\ReadonlyCommand::run("pgrep cron");
        
        if ($result['success'] && !empty(trim($result['output']))) {
            return true;
        }
        
        // Try crond process name (CentOS/RHEL)
        $result = \App\Support\Sys\ReadonlyCommand::run("pgrep crond");
        
        return $result['success'] && !empty(trim($result['output']));
    }
}
