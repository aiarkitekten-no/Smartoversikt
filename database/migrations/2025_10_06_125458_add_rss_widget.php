<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('widgets')->updateOrInsert(
            ['key' => 'news.rss'],
            [
                'key' => 'news.rss',
                'name' => 'RSS Nyheter',
                'description' => 'Siste nyheter fra RSS-feeds',
                'category' => 'news',
                'default_refresh_interval' => 600,
                'is_active' => true,
                'order' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('widgets')->where('key', 'news.rss')->delete();
    }
};
