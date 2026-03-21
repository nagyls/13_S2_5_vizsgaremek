<!-- SZAKKÖR MÓDOSÍTÁS -->
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
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->enum('recurrence_frequency', ['weekly'])->nullable()->after('is_recurring');
            $table->dateTime('recurrence_until')->nullable()->after('recurrence_frequency');
            $table->foreignId('recurrence_parent_event_id')
                ->nullable()
                ->after('recurrence_until')
                ->constrained('events')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recurrence_parent_event_id');
            $table->dropColumn('recurrence_until');
            $table->dropColumn('recurrence_frequency');
            $table->dropColumn('is_recurring');
        });
    }
};
