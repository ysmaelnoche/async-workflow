<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check request 13 (the one we just tested)
$request = App\Models\FormRequest::find(13);

if ($request) {
    echo "Request 13 Approval History:\n";
    $approvals = $request->approvals()->orderBy('created_at')->get();
    
    foreach ($approvals as $approval) {
        echo "\n" . $approval->created_at . ":\n";
        echo "- Action: " . $approval->action . "\n";
        echo "- Approver ID: " . $approval->approver_id . "\n";
        echo "- Signature Style ID: " . ($approval->signature_style_id ?? 'NULL') . "\n";
        echo "- Signature Image Path: " . ($approval->signature_image_path ?? 'NULL') . "\n";
        echo "- Comments: " . ($approval->comments ?? 'None') . "\n";
        
        if ($approval->action === 'Evaluate') {
            echo "*** EVALUATE ACTION DETAILS ***\n";
            if ($approval->signature_style_id || $approval->signature_image_path) {
                echo "❌ ERROR: Evaluate action has signature data!\n";
            } else {
                echo "✅ GOOD: Evaluate action has no signature\n";
            }
        }
    }
    
    echo "\n\nCurrent Request Status:\n";
    echo "- Status: " . $request->status . "\n";
    echo "- Current Approver: " . $request->current_approver_id . "\n";
    echo "- Assigned Sub-Dept: " . ($request->assigned_sub_department ?? 'NULL') . "\n";
} else {
    echo "Request 13 not found\n";
}
