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
        Schema::table('establishment_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'rejected'])->default('pending')->after('establishment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishment_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};