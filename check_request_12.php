<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the request
$request = App\Models\FormRequest::find(12);

if ($request) {
    echo "Request ID: " . $request->id . "\n";
    echo "Status: " . $request->status . "\n";
    echo "Assigned Sub-Department: " . ($request->assigned_sub_department ?? 'NULL') . "\n";
    echo "Current Approver ID: " . ($request->current_approver_id ?? 'NULL') . "\n";
    echo "Department ID: " . $request->department_id . "\n";
    echo "Request Type: " . $request->request_type . "\n";
    echo "Created At: " . $request->created_at . "\n";
    echo "Updated At: " . $request->updated_at . "\n";
    
    // Check the latest approval
    $latestApproval = $request->approvals()->latest()->first();
    if ($latestApproval) {
        echo "\nLatest Approval:\n";
        echo "- ID: " . $latestApproval->id . "\n";
        echo "- Action: " . $latestApproval->action . "\n";
        echo "- Approver ID: " . $latestApproval->approver_id . "\n";
        echo "- Created At: " . $latestApproval->created_at . "\n";
    }
} else {
    echo "Request not found\n";
}
