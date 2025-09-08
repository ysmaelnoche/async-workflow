<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new workflow statuses for enhanced PFMO process
        $newStatuses = "'Pending', 'Noted', 'Approved', 'Rejected', 'Cancelled', 'In Progress', 'Pending Target Department Approval', 'Pending PFMO Approval', 'Under Sub-Department Evaluation', 'Awaiting PFMO Decision'";
        DB::statement("ALTER TABLE form_requests MODIFY COLUMN status ENUM({$newStatuses}) NOT NULL DEFAULT 'Pending'");

        // Add new fields to support enhanced workflow tracking
        Schema::table('form_requests', function (Blueprint $table) {
            $table->string('assigned_sub_department')->nullable()->after('current_approver_id');
            $table->text('auto_assignment_details')->nullable()->after('assigned_sub_department');
            $table->timestamp('date_approved')->nullable()->after('date_submitted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_requests', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_sub_department',
                'auto_assignment_details',
                'date_approved'
            ]);
        });

        // Revert status ENUM to previous state
        $originalStatuses = "'Pending', 'Noted', 'Approved', 'Rejected', 'Cancelled', 'In Progress', 'Pending Target Department Approval'";
        DB::statement("ALTER TABLE form_requests MODIFY COLUMN status ENUM({$originalStatuses}) NOT NULL DEFAULT 'Pending'");
    }
};
