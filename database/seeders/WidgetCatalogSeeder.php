<?php

namespace Database\Seeders;

use App\Models\Widget;
use Illuminate\Database\Seeder;

class WidgetCatalogSeeder extends Seeder
{
    /**
     * Seed widgets-katalogen fra config
     */
    public function run(): void
    {
        $catalog = config('widgets.catalog', []);

        foreach ($catalog as $key => $config) {
            Widget::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $config['name'],
                    'description' => $config['description'] ?? null,
                    'category' => $config['category'],
                    'default_settings' => $config['default_settings'] ?? null,
                    'default_refresh_interval' => $config['refresh_interval'] ?? 300,
                    'is_active' => true,
                ]
            );

            $this->command->info("Widget '{$key}' synced.");
        }

        $this->command->info('Widget catalog seeded successfully.');
    }
}

