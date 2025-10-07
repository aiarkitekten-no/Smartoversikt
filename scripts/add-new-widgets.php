<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();

// Add new widgets
$widgets = [
    'system.disk-usage' => 100,
    'system.network' => 101,
    'system.disk-io' => 102,
];

foreach ($widgets as $key => $position) {
    $widget = App\Models\Widget::where('key', $key)->first();
    
    if ($widget && !App\Models\UserWidget::where('user_id', $user->id)->where('widget_id', $widget->id)->exists()) {
        App\Models\UserWidget::create([
            'user_id' => $user->id,
            'widget_id' => $widget->id,
            'position' => $position,
        ]);
        echo "✓ Added {$widget->name}\n";
    } else {
        echo "- Widget {$key} already added or not found\n";
    }
}

echo "\nDone!\n";
