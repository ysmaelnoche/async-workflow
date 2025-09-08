<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update request 12 to be assigned to staff member 53
$request = App\Models\FormRequest::find(12);
if ($request) {
    echo "Before update:\n";
    echo "- Status: " . $request->status . "\n";
    echo "- Current Approver ID: " . $request->current_approver_id . "\n";
    echo "- Assigned Sub-Department: " . $request->assigned_sub_department . "\n";
    
    // Update assignment to staff member 53 (the approver staff)
    $request->current_approver_id = 53;
    $request->save();
    
    echo "\nAfter update:\n";
    echo "- Status: " . $request->status . "\n";
    echo "- Current Approver ID: " . $request->current_approver_id . "\n";
    echo "- Assigned Sub-Department: " . $request->assigned_sub_department . "\n";
    
    echo "\nRequest 12 is now properly assigned to PFMO staff member 53 for sub-department feedback!\n";
} else {
    echo "Request not found\n";
}
