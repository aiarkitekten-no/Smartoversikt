<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('refresh_interval')->default(600); // seconds
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        
        // Insert default feeds
        DB::table('rss_feeds')->insert([
            [
                'name' => 'NRK Nyheter',
                'url' => 'https://www.nrk.no/toppsaker.rss',
                'category' => 'Nyheter',
                'is_active' => true,
                'refresh_interval' => 600,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'VG',
                'url' => 'https://www.vg.no/rss/feed/',
                'category' => 'Nyheter',
                'is_active' => true,
                'refresh_interval' => 600,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Aftenposten',
                'url' => 'https://www.aftenposten.no/rss',
                'category' => 'Nyheter',
                'is_active' => true,
                'refresh_interval' => 600,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};
