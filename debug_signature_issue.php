<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check which request has the signature issue
$request13 = App\Models\FormRequest::find(13);

if ($request13) {
    echo "Request 13 - Latest Approval Records:\n";
    $approvals = $request13->approvals()->orderBy('created_at', 'desc')->take(3)->get();
    
    foreach ($approvals as $approval) {
        echo "\n" . $approval->created_at . ":\n";
        echo "- Action: " . $approval->action . "\n";
        echo "- Approver ID: " . $approval->approver_id . "\n";
        echo "- Signature Style ID: " . ($approval->signature_style_id ?? 'NULL') . "\n";
        echo "- Signature Image Path: " . ($approval->signature_image_path ?? 'NULL') . "\n";
        echo "- Comments: " . ($approval->comments ?? 'None') . "\n";
        
        if ($approval->action === 'Evaluate' && ($approval->signature_style_id || $approval->signature_image_path)) {
            echo "❌ PROBLEM: Evaluate action has signature!\n";
        }
    }
}

// Also check if there are any newer requests that might have this issue
$latestRequests = App\Models\FormRequest::where('form_id', '>=', 13)
    ->orderBy('form_id', 'desc')
    ->take(3)
    ->get();

echo "\n\nLatest Requests (13+):\n";
foreach ($latestRequests as $req) {
    echo "Request {$req->form_id}: Status = {$req->status}, Current Approver = {$req->current_approver_id}\n";
    
    $latestApproval = $req->approvals()->latest()->first();
    if ($latestApproval) {
        echo "  Latest action: {$latestApproval->action}";
        if ($latestApproval->signature_style_id) {
            echo " (HAS SIGNATURE - Style ID: {$latestApproval->signature_style_id})";
        } else {
            echo " (no signature)";
        }
        echo "\n";
    }
}
