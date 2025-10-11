<?php

namespace Database\Seeders;

use App\Models\Widget;
use Illuminate\Database\Seeder;

class WidgetSeeder extends Seeder
{
    /**
     * Seed widgets table from config/widgets.php catalog.
     * This syncs the widget definitions to the database.
     */
    public function run(): void
    {
        $this->command->info('Syncing widgets from config/widgets.php to database...');

        $widgetsConfig = config('widgets.catalog', []);
        $categoriesConfig = config('widgets.categories', []);

        if (empty($widgetsConfig)) {
            $this->command->warn('No widgets found in config/widgets.php');
            return;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($widgetsConfig as $key => $widgetConfig) {
            // Get category name from categories config
            $categoryKey = $widgetConfig['category'] ?? 'other';
            $categoryName = $categoriesConfig[$categoryKey]['name'] ?? ucfirst($categoryKey);

            // Prepare widget data
            $widgetData = [
                'key' => $key,
                'name' => $widgetConfig['name'] ?? $key,
                'description' => $widgetConfig['description'] ?? null,
                'category' => $categoryKey,
                'default_refresh_interval' => $widgetConfig['refresh_interval'] ?? 60,
                'is_active' => $widgetConfig['is_active'] ?? true,
            ];

            // Check if widget already exists
            $existingWidget = Widget::where('key', $key)->first();

            if ($existingWidget) {
                // Update existing widget (but preserve custom order)
                $updateData = $widgetData;
                unset($updateData['key']); // Don't update the key
                
                $existingWidget->update($updateData);
                $updated++;
                $this->command->line("  ✓ Updated: {$widgetData['name']} ({$key})");
            } else {
                // Create new widget
                Widget::create($widgetData);
                $created++;
                $this->command->info("  + Created: {$widgetData['name']} ({$key})");
            }
        }

        $this->command->newLine();
        $this->command->info("Summary: {$created} created, {$updated} updated, {$skipped} skipped.");
        $this->command->info('Total widgets in database: ' . Widget::count());
    }
}
