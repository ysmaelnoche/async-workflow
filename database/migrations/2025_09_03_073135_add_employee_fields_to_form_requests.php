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
            // Employee information fields for proxy submissions
            $table->string('actual_requestor_name')->nullable()->after('requested_by');
            $table->string('actual_requestor_employee_id')->nullable()->after('actual_requestor_name');
            $table->string('actual_requestor_department')->nullable()->after('actual_requestor_employee_id');
            $table->string('actual_requestor_position')->nullable()->after('actual_requestor_department');
            $table->boolean('is_proxy_submission')->default(false)->after('actual_requestor_position');
            
            // Add enhanced status for branching logic
            $table->string('sub_status')->nullable()->after('status');
            $table->json('missing_requirements')->nullable()->after('sub_status');
            $table->timestamp('info_requested_at')->nullable()->after('missing_requirements');
            $table->timestamp('info_deadline')->nullable()->after('info_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropColumn([
                'actual_requestor_name',
                'actual_requestor_employee_id', 
                'actual_requestor_department',
                'actual_requestor_position',
                'is_proxy_submission',
                'sub_status',
                'missing_requirements',
                'info_requested_at',
                'info_deadline'
            ]);
        });
    }
};
