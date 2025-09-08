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
        Schema::table('form_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('form_requests', 'auto_assignment_details')) {
                $table->json('auto_assignment_details')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            if (Schema::hasColumn('form_requests', 'auto_assignment_details')) {
                $table->dropColumn('auto_assignment_details');
            }
        });
    }
};
