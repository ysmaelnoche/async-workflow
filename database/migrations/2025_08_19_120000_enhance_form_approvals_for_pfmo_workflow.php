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
        // First, add the new columns to form_approvals table
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->string('remarks')->nullable()->after('comments');
            $table->string('approval_level')->nullable()->after('remarks');
            $table->string('sub_department')->nullable()->after('approval_level');
            $table->date('estimated_completion_date')->nullable()->after('sub_department');
            $table->decimal('estimated_cost', 12, 2)->nullable()->after('estimated_completion_date');
            $table->string('job_order_number')->nullable()->after('estimated_cost');
        });

        // Update the action ENUM to include new workflow actions
        $newActions = "'Approved', 'Rejected', 'Noted', 'Submitted', 'Evaluate', 'Assigned', 'Send Feedback', 'Job Order Created'";
        DB::statement("ALTER TABLE form_approvals MODIFY COLUMN action ENUM({$newActions}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_approvals', function (Blueprint $table) {
            $table->dropColumn([
                'remarks',
                'approval_level', 
                'sub_department',
                'estimated_completion_date',
                'estimated_cost',
                'job_order_number'
            ]);
        });

        // Revert action ENUM to original values
        $originalActions = "'Approved', 'Rejected', 'Noted', 'Submitted'";
        DB::statement("ALTER TABLE form_approvals MODIFY COLUMN action ENUM({$originalActions}) NOT NULL");
    }
};
