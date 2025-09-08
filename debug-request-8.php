<?php

// Test the workflow routing for request ID 8
$request = App\Models\FormRequest::find(8);

if ($request) {
    echo "=== REQUEST 8 ANALYSIS ===\n";
    echo "Form ID: " . $request->form_id . "\n";
    echo "Current Status: " . $request->status . "\n";
    echo "Target Department ID: " . $request->to_department_id . "\n";
    echo "Current Approver ID: " . $request->current_approver_id . "\n";
    echo "Date Approved: " . ($request->date_approved ?? 'Not set') . "\n";
    
    // Check target department
    if ($request->to_department_id) {
        $targetDept = App\Models\Department::find($request->to_department_id);
        if ($targetDept) {
            echo "Target Department: " . $targetDept->dept_name . " (" . $targetDept->dept_code . ")\n";
        }
    } else {
        echo "Target Department: Not set\n";
    }
    
    // Check all approvals
    echo "\n=== APPROVAL HISTORY ===\n";
    $approvals = App\Models\FormApproval::where('form_id', 8)
        ->orderBy('action_date', 'asc')
        ->get();
        
    foreach ($approvals as $approval) {
        $user = App\Models\User::find($approval->approver_id);
        $dept = $user ? App\Models\Department::find($user->department_id) : null;
        
        echo "Date: " . $approval->action_date . "\n";
        echo "Action: " . $approval->action . "\n";
        echo "Approver: " . ($user ? $user->fname . ' ' . $user->lname : 'Unknown') . "\n";
        echo "Department: " . ($dept ? $dept->dept_name : 'Unknown') . "\n";
        echo "Position: " . ($user ? $user->position : 'Unknown') . "\n";
        echo "---\n";
    }
    
    // Check if target department (PFMO) has any approvers
    if ($request->to_department_id) {
        echo "\n=== TARGET DEPARTMENT APPROVERS ===\n";
        $pfmoApprovers = App\Models\User::where('department_id', $request->to_department_id)
            ->where('accessRole', 'Approver')
            ->get();
            
        foreach ($pfmoApprovers as $approver) {
            echo "ID: " . $approver->accnt_id . " - " . $approver->fname . ' ' . $approver->lname . " (" . $approver->position . ")\n";
        }
        
        if ($pfmoApprovers->isEmpty()) {
            echo "No approvers found in target department!\n";
        }
    }
    
    // Analyze workflow issue
    echo "\n=== WORKFLOW ANALYSIS ===\n";
    echo "The request should be:\n";
    echo "1. Submitted by requester\n";
    echo "2. Approved by department head (moves to 'In Progress')\n";
    echo "3. Routed to target department (PFMO)\n";
    echo "4. Shows status 'Pending Target Department Approval' or 'Pending PFMO Approval'\n";
    echo "\nCurrent issue: Request shows 'Pending PFMO Approval' but approver 2 has already approved it.\n";
    echo "This suggests the approval didn't properly route the request to PFMO.\n";
} else {
    echo "Request 8 not found\n";
}
