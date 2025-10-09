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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Netflix, Strøm, Husleie
            $table->decimal('amount', 10, 2); // Beløp i NOK
            $table->integer('due_day'); // Dag i måneden (1-31)
            $table->boolean('is_paid_this_month')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Index for performance
            $table->index(['user_id', 'due_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
