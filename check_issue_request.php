<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check the problematic request from the screenshot
$request = App\Models\FormRequest::find(14);

if ($request) {
    echo "Request 14 Details:\n";
    echo "- Status: " . $request->status . "\n";
    echo "- Current Approver ID: " . $request->current_approver_id . "\n";
    echo "- Assigned Sub-Department: " . ($request->assigned_sub_department ?? 'NULL') . "\n";
    
    // Check all approvals for this request
    echo "\nAll Actions for Request 14:\n";
    $allApprovals = $request->approvals()->orderBy('created_at')->get();
    foreach ($allApprovals as $approval) {
        echo "- " . $approval->created_at . ": " . $approval->action;
        if ($approval->signature_style_id) {
            echo " (with signature ID: " . $approval->signature_style_id . ")";
        } else {
            echo " (no signature)";
        }
        echo " - Comments: " . ($approval->comments ?? 'None') . "\n";
    }
} else {
    echo "Request 14 not found. Let me check the latest requests:\n";
    $latestRequests = App\Models\FormRequest::orderBy('form_id', 'desc')->take(3)->get();
    foreach ($latestRequests as $req) {
        echo "Request " . $req->form_id . ": Status = " . $req->status . "\n";
    }
}
