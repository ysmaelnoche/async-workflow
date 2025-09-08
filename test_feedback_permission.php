<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Fixed Permission Logic ===\n\n";

$user = App\Models\User::where('username', 'PFMO-2025-0054')->first();
$formRequest = App\Models\FormRequest::find(15); // Test with request 15

if ($user && $formRequest) {
    echo "User: {$user->username}\n";
    echo "Position: {$user->position}\n";
    echo "Access Role: {$user->accessRole}\n";
    echo "Sub-Department ID: {$user->sub_department_id}\n";
    
    echo "\nRequest:\n";
    echo "Form ID: {$formRequest->form_id}\n";
    echo "Status: {$formRequest->status}\n";
    echo "Assigned Sub-Department: {$formRequest->assigned_sub_department}\n";
    
    // Test the permission logic
    $canSendFeedback = false;
    
    // Simulate the fixed permission check
    if ($user->position === 'Staff' && in_array($user->accessRole, ['Approver', 'Viewer']) && 
        $formRequest->status === 'Under Sub-Department Evaluation') {
        
        echo "\n🔍 Permission Check:\n";
        echo "- Position is Staff: ✓\n";
        echo "- Access Role is Approver/Viewer: " . (in_array($user->accessRole, ['Approver', 'Viewer']) ? '✓' : '✗') . "\n";
        echo "- Status is Under Sub-Department Evaluation: " . ($formRequest->status === 'Under Sub-Department Evaluation' ? '✓' : '✗') . "\n";
        
        if ($user->sub_department_id && $formRequest->assigned_sub_department) {
            $userSubDept = \Illuminate\Support\Facades\DB::table('sub_departments')
                ->where('id', $user->sub_department_id)
                ->first();
            
            echo "- User Sub-Department: {$userSubDept->name} (Code: {$userSubDept->code})\n";
            
            if ($userSubDept) {
                $subDeptMapping = [
                    'PFMO-GEN' => 'general_services',
                    'PFMO-CONS' => 'electrical',
                    'PFMO-HOUSE' => 'hvac',
                ];
                
                $mappedSubDept = $subDeptMapping[$userSubDept->code] ?? null;
                echo "- Mapped Sub-Department: " . ($mappedSubDept ?? 'None') . "\n";
                echo "- Request Assigned Sub-Department: {$formRequest->assigned_sub_department}\n";
                
                if ($mappedSubDept === $formRequest->assigned_sub_department) {
                    $canSendFeedback = true;
                    echo "- Sub-Department Match: ✓\n";
                } else {
                    echo "- Sub-Department Match: ✗\n";
                }
            }
        }
    }
    
    echo "\n🎯 Final Result:\n";
    echo "Can Send Feedback: " . ($canSendFeedback ? '✅ YES' : '❌ NO') . "\n";
    
    if ($canSendFeedback) {
        echo "\n🎉 The user should now be able to see the 'Send Feedback' button!\n";
    } else {
        echo "\n❌ The user still cannot send feedback. Need to investigate further.\n";
    }
} else {
    echo "❌ User or request not found!\n";
}
