<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->boot();

echo "=== Debugging Workflow Issue ===\n\n";

$request = \App\Models\FormRequest::with(['approvals.approver.employeeInfo', 'targetDepartment', 'requester.department'])->find(8);
if ($request) {
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
    
    echo "\n=== Analysis ===\n";
    echo "Expected workflow: CCS Staff -> CCS Head (Approve) -> PFMO (Final Approve)\n";
    echo "Issue: After CCS Head approval, request should be 'In Progress' and routed to PFMO, not 'Approved'\n";
    
} else {
    echo "Request not found\n";
}
