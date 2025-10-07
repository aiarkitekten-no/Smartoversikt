<?php

/**
 * Add new widgets to user dashboard
 * Run: php scripts/add-new-widgets-security.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get first user (adjust as needed)
$user = \App\Models\User::first();

if (!$user) {
    die("No user found!\n");
}

// Widgets to add
$widgetsToAdd = [
    ['key' => 'security.ssl-certs', 'position' => 103],
    ['key' => 'system.error-log', 'position' => 104],
    ['key' => 'system.cron-jobs', 'position' => 105],
    ['key' => 'security.events', 'position' => 106],
];

foreach ($widgetsToAdd as $widgetData) {
    $widget = \App\Models\Widget::where('key', $widgetData['key'])->first();
    
    if (!$widget) {
        echo "⚠️  Widget '{$widgetData['key']}' not found in catalog.\n";
        continue;
    }

    // Check if already added
    $exists = \App\Models\UserWidget::where('user_id', $user->id)
        ->where('widget_id', $widget->id)
        ->exists();

    if ($exists) {
        echo "ℹ️  Widget '{$widgetData['key']}' already exists for user.\n";
        continue;
    }

    // Add widget
    \App\Models\UserWidget::create([
        'user_id' => $user->id,
        'widget_id' => $widget->id,
        'position' => $widgetData['position'],
        'settings' => [],
        'is_visible' => true,
    ]);

    echo "✅ Added widget '{$widgetData['key']}' at position {$widgetData['position']}.\n";
}

// Count total widgets
$total = \App\Models\UserWidget::where('user_id', $user->id)->count();
echo "\n✨ User now has {$total} widgets total.\n";
