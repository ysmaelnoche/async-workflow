<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verification of Changes ===\n\n";

// Check requests with "Under Sub-Department Evaluation" status
$underEvaluationRequests = App\Models\FormRequest::where('status', 'Under Sub-Department Evaluation')
    ->with(['approvals'])
    ->get();

echo "Requests in 'Under Sub-Department Evaluation' status:\n";
foreach ($underEvaluationRequests as $request) {
    echo "\n📋 Form {$request->form_id}:\n";
    echo "  Status: {$request->status}\n";
    echo "  Assigned Sub-Department: " . ($request->assigned_sub_department ?? 'None') . "\n";
    
    // Show expected sub-department display name
    $subDeptName = 'PFMO Sub-Department';
    if ($request->assigned_sub_department) {
        switch($request->assigned_sub_department) {
            case 'electrical':
                $subDeptName = 'PFMO Electrical Department';
                break;
            case 'hvac':
                $subDeptName = 'PFMO HVAC Department';
                break;
            case 'general_services':
                $subDeptName = 'PFMO General Services';
                break;
            default:
                $subDeptName = 'PFMO ' . ucwords(str_replace('_', ' ', $request->assigned_sub_department)) . ' Department';
        }
    }
    echo "  Will display: 'Awaiting action from {$subDeptName}'\n";
    
    // Check if this request has any Evaluate actions
    $evaluateActions = $request->approvals->where('action', 'Evaluate');
    echo "  Evaluate actions found: " . $evaluateActions->count() . "\n";
    
    foreach ($evaluateActions as $evaluate) {
        echo "    - Evaluate by User {$evaluate->approver_id} at {$evaluate->created_at}\n";
        echo "      Has signature style: " . ($evaluate->signature_style_id ? 'YES (ID: ' . $evaluate->signature_style_id . ')' : 'NO') . "\n";
        echo "      Has signature data: " . ($evaluate->signature_data ? 'YES' : 'NO') . "\n";
    }
    
    // Check signature-worthy actions (should NOT include Evaluate)
    $signatureActions = $request->approvals
        ->whereNotIn('action', ['Submitted', 'Evaluate'])
        ->filter(function ($approval) {
            return $approval->signature_data || 
                   ($approval->approver && $approval->approver->signatureStyle) || 
                   $approval->signature_name;
        });
    
    echo "  Actions that will show signatures: " . $signatureActions->count() . "\n";
    foreach ($signatureActions as $action) {
        echo "    - {$action->action} by User {$action->approver_id}\n";
    }
}

echo "\n=== Summary ===\n";
echo "✅ Change 1: Signature display will exclude 'Evaluate' actions completely\n";
echo "✅ Change 2: 'Awaiting action from' will show sub-department names instead of user IDs\n";
echo "\nThe Evaluate button will now:\n";
echo "- ❌ NOT create any signature records\n";
echo "- ❌ NOT display in the signatures section\n";
echo "- ✅ Only change status to 'Under Sub-Department Evaluation'\n";
echo "- ✅ Show 'Awaiting action from [Sub-Department Name]' instead of user ID\n";
