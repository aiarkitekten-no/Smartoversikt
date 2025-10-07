<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserWidget;
use App\Models\Widget;
use Illuminate\Database\Seeder;

class UserWidgetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('email', env('ADMIN_EMAIL'))->first();
        
        if (!$admin) {
            $this->command->warn('Admin bruker ikke funnet. Kjør AdminUserSeeder først.');
            return;
        }

        // Clear existing widgets for admin
        UserWidget::where('user_id', $admin->id)->delete();

        // Get all active widgets
        $widgets = Widget::active()->orderBy('order')->get();

        if ($widgets->isEmpty()) {
            $this->command->warn('Ingen aktive widgets funnet. Kjør WidgetSeeder først.');
            return;
        }

        // Add all widgets to admin's dashboard
        $position = 0;
        foreach ($widgets as $widget) {
            UserWidget::create([
                'user_id' => $admin->id,
                'widget_id' => $widget->id,
                'position' => $position++,
                'is_visible' => true,
            ]);
            
            $this->command->info("Lagt til widget: {$widget->name}");
        }

        $this->command->info("✓ {$widgets->count()} widgets lagt til for admin-bruker");
    }
}

