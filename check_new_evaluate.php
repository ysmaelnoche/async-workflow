<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the latest request to see if our new evaluate method worked
$request = App\Models\FormRequest::find(13);

if ($request) {
    echo "Request 13 Details:\n";
    echo "- Status: " . $request->status . "\n";
    echo "- Current Approver ID: " . $request->current_approver_id . "\n";
    echo "- Assigned Sub-Department: " . ($request->assigned_sub_department ?? 'NULL') . "\n";
    echo "- To Department ID: " . $request->to_department_id . "\n";
    echo "- Updated At: " . $request->updated_at . "\n";
    
    // Check the latest approval/evaluation record
    $latestApproval = $request->approvals()->latest()->first();
    if ($latestApproval) {
        echo "\nLatest Action:\n";
        echo "- Action: " . $latestApproval->action . "\n";
        echo "- Approver ID: " . $latestApproval->approver_id . "\n";
        echo "- Signature Style ID: " . ($latestApproval->signature_style_id ?? 'NULL (no signature)') . "\n";
        echo "- Comments: " . ($latestApproval->comments ?? 'None') . "\n";
        echo "- Created At: " . $latestApproval->created_at . "\n";
    }
    
    // Check all approvals for this request
    echo "\nAll Actions for Request 13:\n";
    $allApprovals = $request->approvals()->orderBy('created_at')->get();
    foreach ($allApprovals as $approval) {
        echo "- " . $approval->created_at . ": " . $approval->action;
        if ($approval->signature_style_id) {
            echo " (with signature)";
        } else {
            echo " (no signature)";
        }
        echo "\n";
    }
} else {
    echo "Request 13 not found\n";
}
