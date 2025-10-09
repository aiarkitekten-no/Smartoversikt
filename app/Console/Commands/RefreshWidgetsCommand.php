<?php
# START 6b9d4e1f7c2a / Refresh Widgets Command
# Hash: 6b9d4e1f7c2a
# Purpose: Scheduled command for refreshing widget snapshots

namespace App\Console\Commands;

use App\Models\Widget;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshWidgetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'widgets:refresh {key?} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh widget snapshots (all or specific widget)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $key = $this->argument('key');
        $force = $this->option('force');

        if ($key) {
            return $this->refreshSingle($key, $force);
        }

        return $this->refreshAll($force);
    }

    /**
     * Refresh en enkelt widget
     */
    protected function refreshSingle(string $key, bool $force): int
    {
        $widget = Widget::where('key', $key)->where('is_active', true)->first();

        if (!$widget) {
            $this->error("Widget '{$key}' not found or inactive.");
            return 1;
        }

        $catalog = config('widgets.catalog', []);
        
        if (!isset($catalog[$key]) || !isset($catalog[$key]['fetcher'])) {
            $this->error("Fetcher not configured for '{$key}'.");
            return 1;
        }

        $fetcherClass = $catalog[$key]['fetcher'];

        if (!class_exists($fetcherClass)) {
            $this->error("Fetcher class does not exist: {$fetcherClass}");
            return 1;
        }

        // Check if this is a user-specific widget
        $userWidgets = \App\Models\UserWidget::with('user')
            ->where('widget_id', $widget->id)
            ->get();

        if ($userWidgets->isNotEmpty()) {
            // Refresh for each user
            $this->info("Refreshing user-specific widget: {$key} for {$userWidgets->count()} users");
            foreach ($userWidgets as $userWidget) {
                $fetcher = new $fetcherClass();
                $fetcher->setUserWidget($userWidget);
                
                if (!$force && !$fetcher->needsRefresh()) {
                    $this->info("  User {$userWidget->user->name}: fresh, skipping.");
                    continue;
                }
                
                $this->info("  User {$userWidget->user->name}: refreshing...");
                $snapshot = $fetcher->refreshSnapshot();
                
                if ($snapshot->status === 'success') {
                    $this->info("    ✓ Success");
                } else {
                    $this->error("    ✗ Failed: {$snapshot->error_message}");
                }
            }
            return 0;
        }

        // Global widget
        $fetcher = new $fetcherClass();

        if (!$force && !$fetcher->needsRefresh()) {
            $this->info("Widget '{$key}' is fresh, skipping.");
            return 0;
        }

        $this->info("Refreshing widget: {$key}");
        $snapshot = $fetcher->refreshSnapshot();

        if ($snapshot->status === 'success') {
            $this->info("✓ {$key} refreshed successfully.");
            return 0;
        } else {
            $this->error("✗ {$key} failed: {$snapshot->error_message}");
            return 1;
        }
    }

    /**
     * Refresh alle widgets
     */
    protected function refreshAll(bool $force): int
    {
        $widgets = Widget::where('is_active', true)->get();
        $catalog = config('widgets.catalog', []);
        $refreshed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($widgets as $widget) {
            if (!isset($catalog[$widget->key]) || !isset($catalog[$widget->key]['fetcher'])) {
                $this->warn("Fetcher missing for '{$widget->key}', skipping.");
                $skipped++;
                continue;
            }

            $fetcherClass = $catalog[$widget->key]['fetcher'];

            if (!class_exists($fetcherClass)) {
                $this->warn("Fetcher class missing for '{$widget->key}', skipping.");
                $skipped++;
                continue;
            }

            // Check if this widget has user-specific instances
            $userWidgets = \App\Models\UserWidget::with('user')
                ->where('widget_id', $widget->id)
                ->get();

            if ($userWidgets->isNotEmpty()) {
                // Refresh for each user
                foreach ($userWidgets as $userWidget) {
                    $fetcher = new $fetcherClass();
                    $fetcher->setUserWidget($userWidget);
                    
                    if (!$force && !$fetcher->needsRefresh()) {
                        $skipped++;
                        continue;
                    }

                    $this->info("Refreshing: {$widget->key} (user: {$userWidget->user->name})");
                    $snapshot = $fetcher->refreshSnapshot();

                    if ($snapshot->status === 'success') {
                        $this->info("  ✓ Success");
                        $refreshed++;
                    } else {
                        $this->error("  ✗ Failed: {$snapshot->error_message}");
                        $failed++;
                    }
                }
                continue;
            }

            // Global widget (no user-specific data)
            $fetcher = new $fetcherClass();

            if (!$force && !$fetcher->needsRefresh()) {
                $skipped++;
                continue;
            }

            $this->info("Refreshing: {$widget->key}");
            $snapshot = $fetcher->refreshSnapshot();

            if ($snapshot->status === 'success') {
                $this->info("  ✓ Success");
                $refreshed++;
            } else {
                $this->error("  ✗ Failed: {$snapshot->error_message}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Summary: {$refreshed} refreshed, {$skipped} skipped, {$failed} failed.");

        return $failed > 0 ? 1 : 0;
    }
}
# SLUTT 6b9d4e1f7c2a
