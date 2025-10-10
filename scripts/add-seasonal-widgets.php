<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Widget;

$seasonal = [
    [
        'key' => 'season.snow-globe',
        'name' => 'Snøkule',
        'description' => 'Interaktiv snøkule med virvlende snø',
        'category' => 'seasonal',
        'default_refresh_interval' => 3600,
    ],
    [
        'key' => 'season.tree-lights',
        'name' => 'Juletrelys',
        'description' => 'Juletre med animerte lysmønstre',
        'category' => 'seasonal',
        'default_refresh_interval' => 3600,
    ],
    [
        'key' => 'season.sleigh-tracker',
        'name' => 'Nissens Radar',
        'description' => 'Liten radar som viser nissens slede på rute',
        'category' => 'seasonal',
        'default_refresh_interval' => 3600,
    ],
    [
        'key' => 'season.fireplace',
        'name' => 'Peis med flammer',
        'description' => 'Koselig peis med animerte flammer',
        'category' => 'seasonal',
        'default_refresh_interval' => 3600,
    ],
];

$added = 0; $updated = 0; $skipped = 0;

foreach ($seasonal as $w) {
    $existing = Widget::where('key', $w['key'])->first();
    if ($existing) {
        // Update name/description/category/interval in case config changed
        $existing->update([
            'name' => $w['name'],
            'description' => $w['description'],
            'category' => $w['category'],
            'default_refresh_interval' => $w['default_refresh_interval'],
            'is_active' => true,
        ]);
        $updated++;
        echo "~ Updated {$w['key']}\n";
    } else {
        Widget::create(array_merge($w, [
            'default_settings' => null,
            'is_active' => true,
        ]));
        $added++;
        echo "+ Added {$w['key']}\n";
    }
}

echo "\nDone. Added: {$added}, Updated: {$updated}\n";
