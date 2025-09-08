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
        Schema::table('job_order_progress', function (Blueprint $table) {
            // Add time unit field for estimated time remaining
            $table->enum('estimated_time_unit', ['days', 'weeks', 'months'])->nullable()->after('estimated_time_remaining');
            
            // Remove percentage_complete field (adviser requested removal)
            $table->dropColumn('percentage_complete');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_order_progress', function (Blueprint $table) {
            // Restore percentage_complete field
            $table->integer('percentage_complete')->default(0)->after('progress_note');
            
            // Remove time unit field
            $table->dropColumn('estimated_time_unit');
        });
    }
};
