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
        Schema::create('mail_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name (e.g., "Terje - Smartesider")
            $table->string('type'); // 'imap' or 'smtp'
            $table->string('host');
            $table->integer('port');
            $table->string('username');
            $table->string('password'); // Will be encrypted
            $table->string('encryption')->default('ssl'); // ssl, tls, none
            $table->boolean('validate_cert')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('check_interval')->default(300); // seconds
            $table->json('metadata')->nullable(); // For additional settings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_accounts');
    }
};
