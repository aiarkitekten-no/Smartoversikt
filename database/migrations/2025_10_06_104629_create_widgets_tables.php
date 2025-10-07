<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widgets katalog - tilgjengelige widgets
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // f.eks. 'plesk.status', 'weather.met'
            $table->string('name'); // f.eks. 'Plesk Status'
            $table->text('description')->nullable();
            $table->string('category'); // f.eks. 'system', 'mail', 'analytics'
            $table->json('default_settings')->nullable(); // Standard innstillinger
            $table->integer('default_refresh_interval')->default(60); // sekunder
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User widgets - brukerens valgte widgets
        Schema::create('user_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('widget_key'); // referanse til widgets.key
            $table->json('settings')->nullable(); // Bruker-spesifikke innstillinger
            $table->integer('position')->default(0); // Rekkefølge
            $table->integer('size')->default(1); // 1=liten, 2=medium, 3=stor
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'widget_key']);
        });

        // Widget snapshots - cachet data
        Schema::create('widget_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('widget_key');
            $table->json('payload'); // Hele data-settet
            $table->timestamp('fresh_at'); // Når ble data hentet
            $table->timestamp('expires_at')->nullable(); // Når utløper cache
            $table->string('status')->default('success'); // success, error, stale
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('widget_key');
            $table->index('fresh_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_snapshots');
        Schema::dropIfExists('user_widgets');
        Schema::dropIfExists('widgets');
    }
};
