<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Widget;
use App\Models\UserWidget;
use App\Models\User;

// Get admin user
$adminUser = User::where('email', 'ai.arkitekten@gmail.com')->first();

if (!$adminUser) {
    echo "❌ Admin user not found\n";
    exit(1);
}

// Create or update Trello widget in catalog
$widget = Widget::updateOrCreate(
    ['key' => 'project.trello'],
    [
        'name' => 'Trello Oppgaver',
        'description' => 'Oversikt over Trello oppgaver og frister',
        'category' => 'project',
        'icon' => '📋',
        'component' => 'project-trello',
        'is_enabled' => true,
        'refresh_interval' => 300,
        'default_width' => 2,
        'default_height' => 3,
        'config_schema' => json_encode([
            'api_key' => ['type' => 'string', 'required' => true],
            'api_token' => ['type' => 'string', 'required' => true],
            'board_id' => ['type' => 'string', 'required' => true],
        ]),
        'fetcher_class' => 'App\\Services\\Widgets\\ProjectTrelloFetcher',
    ]
);

echo "✅ Widget 'project.trello' created/updated (ID: {$widget->id})\n";

// Check if user already has this widget
$userWidget = UserWidget::where('user_id', $adminUser->id)
    ->where('widget_id', $widget->id)
    ->first();

if ($userWidget) {
    echo "ℹ️  Widget already on dashboard (position: {$userWidget->position})\n";
} else {
    // Find max position for this user
    $maxPosition = UserWidget::where('user_id', $adminUser->id)->max('position') ?? 0;
    $newPosition = $maxPosition + 1;
    
    // Add to user dashboard
    $userWidget = UserWidget::create([
        'user_id' => $adminUser->id,
        'widget_id' => $widget->id,
        'position' => $newPosition,
        'width' => 2,
        'height' => 3,
        'is_visible' => true,
        'config' => json_encode([]),
    ]);
    
    echo "✅ Widget added to dashboard at position {$newPosition}\n";
}

// Display current widget count
$totalWidgets = Widget::count();
$userWidgetCount = UserWidget::where('user_id', $adminUser->id)->count();

echo "\n📊 Summary:\n";
echo "   Total widgets in catalog: {$totalWidgets}\n";
echo "   User's dashboard widgets: {$userWidgetCount}\n";
echo "\n✨ Done!\n";
