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
        // Remove unique constraint to allow multiple instances of same widget
        Schema::table('user_widgets', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'widget_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_widgets', function (Blueprint $table) {
            $table->unique(['user_id', 'widget_id']);
        });
    }
};
