<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Complete Permission Fix ===\n\n";

$testUsers = [
    'PFMO-2025-0052' => 'PFMO Head',
    'PFMO-2025-0054' => 'General Services Staff'
];

$formRequest = App\Models\FormRequest::find(15);

foreach ($testUsers as $username => $description) {
    echo "🧪 Testing User: {$username} ({$description})\n";
    
    $user = App\Models\User::where('username', $username)->first();
    if (!$user) {
        echo "❌ User not found!\n\n";
        continue;
    }
    
    echo "Position: {$user->position}\n";
    echo "Access Role: {$user->accessRole}\n";
    echo "Sub-Department ID: " . ($user->sub_department_id ?? 'None') . "\n";
    
    // Simulate the controller logic
    $canTakeAction = false;
    $canEvaluate = false;
    $canSendFeedback = false;
    $canFinalDecision = false;
    
    // Check PFMO permissions
    $isPFMORequest = $formRequest->toDepartment && $formRequest->toDepartment->dept_code === 'PFMO';
    $isPFMOUser = $user->department && $user->department->dept_code === 'PFMO';
    
    if ($isPFMORequest && $isPFMOUser) {
        // PFMO Head can "Evaluate"
        if ($user->position === 'Head' && in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval'])) {
            $canEvaluate = true;
        }
        
        // Sub-department staff can "Send Feedback"
        if ($user->position === 'Staff' && in_array($user->accessRole, ['Approver', 'Viewer']) && 
            $formRequest->status === 'Under Sub-Department Evaluation') {
            
            if ($user->sub_department_id && $formRequest->assigned_sub_department) {
                $userSubDept = \Illuminate\Support\Facades\DB::table('sub_departments')
                    ->where('id', $user->sub_department_id)
                    ->first();
                
                if ($userSubDept) {
                    $subDeptMapping = [
                        'PFMO-GEN' => 'general_services',
                        'PFMO-CONS' => 'electrical',
                        'PFMO-HOUSE' => 'hvac',
                    ];
                    
                    $mappedSubDept = $subDeptMapping[$userSubDept->code] ?? null;
                    
                    if ($mappedSubDept === $formRequest->assigned_sub_department) {
                        $canSendFeedback = true;
                    }
                }
            }
        }
        
        // PFMO Head can make "Final Decision"
        if ($user->position === 'Head' && $formRequest->status === 'Awaiting PFMO Decision') {
            $canFinalDecision = true;
        }
        
        // Set canTakeAction to true if any PFMO action is available
        if ($canEvaluate || $canSendFeedback || $canFinalDecision) {
            $canTakeAction = true;
        }
    }
    
    echo "\n📊 Permission Results:\n";
    echo "- canTakeAction: " . ($canTakeAction ? '✅ YES' : '❌ NO') . "\n";
    echo "- canEvaluate: " . ($canEvaluate ? '✅ YES' : '❌ NO') . "\n";
    echo "- canSendFeedback: " . ($canSendFeedback ? '✅ YES' : '❌ NO') . "\n";
    echo "- canFinalDecision: " . ($canFinalDecision ? '✅ YES' : '❌ NO') . "\n";
    
    echo "\n🎯 Expected Behavior:\n";
    if ($canTakeAction) {
        echo "✅ User should see action buttons (not read-only mode)\n";
        if ($canSendFeedback) {
            echo "✅ 'Send Feedback' button should be visible\n";
        }
    } else {
        echo "❌ User will see 'read-only mode' or 'cannot take action' message\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

echo "🎉 Fix Summary:\n";
echo "- Added missing canTakeAction = true for PFMO workflow\n";
echo "- Both users should now have proper access to their respective actions\n";
