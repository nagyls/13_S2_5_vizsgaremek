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
        Schema::create('event_shown_to', function (Blueprint $table) {
            $table->foreignId('events_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('users_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classes_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_shown_to');
    }
};
