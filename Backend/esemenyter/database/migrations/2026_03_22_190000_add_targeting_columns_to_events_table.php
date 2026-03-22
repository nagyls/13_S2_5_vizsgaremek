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
        Schema::table('events', function (Blueprint $table) {
            $table->enum('target_group', ['osztaly_szintu', 'evfolyam_szintu', 'teljes_iskola'])
                ->nullable()
                ->after('type');
            $table->json('target_class_ids')->nullable()->after('target_group');
            $table->json('target_grade_ids')->nullable()->after('target_class_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['target_group', 'target_class_ids', 'target_grade_ids']);
        });
    }
};
