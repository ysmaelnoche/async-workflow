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
        // This is just a debug script, not a migration
        $request = \App\Models\FormRequest::with(['approvals.approver.employeeInfo', 'targetDepartment', 'requester.department'])->find(8);
        
        if ($request) {
            echo "=== Request 8 Debug Info ===\n";
            echo "Form ID: {$request->form_id}\n";
            echo "Status: {$request->status}\n";
            echo "Form Type: {$request->form_type}\n";
            echo "From Dept: " . ($request->requester->department->dept_name ?? 'N/A') . " (ID: {$request->from_department_id})\n";
            echo "To Dept: " . ($request->targetDepartment->dept_name ?? 'N/A') . " (ID: {$request->to_department_id})\n";
            echo "Current Approver: {$request->current_approver_id}\n";
            echo "Date Approved: {$request->date_approved}\n\n";
            
            echo "Approval History:\n";
            foreach ($request->approvals->sortBy('action_date') as $approval) {
                $approverName = $approval->approver->employeeInfo->FirstName . ' ' . $approval->approver->employeeInfo->LastName;
                echo "  - {$approval->action} by {$approverName} ({$approval->approver->position}) at {$approval->action_date}\n";
            }
        } else {
            echo "Request not found\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
