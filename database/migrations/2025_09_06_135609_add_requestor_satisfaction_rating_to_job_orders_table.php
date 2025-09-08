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
        Schema::table('job_orders', function (Blueprint $table) {
            $table->integer('requestor_satisfaction_rating')->nullable()->after('requestor_signature_date');
            $table->boolean('requestor_feedback_submitted')->default(false)->after('requestor_satisfaction_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['requestor_satisfaction_rating', 'requestor_feedback_submitted']);
        });
    }
};
