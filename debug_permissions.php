<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get PFMO users to see their details
$pfmoUsers = App\Models\User::whereHas('department', function($q) {
    $q->where('dept_code', 'PFMO');
})->get();

echo "PFMO Users:\n";
foreach ($pfmoUsers as $user) {
    echo "\nUser ID: " . $user->accnt_id . "\n";
    echo "- Position: " . $user->position . "\n";
    echo "- Access Role: " . $user->accessRole . "\n";
    echo "- Department: " . ($user->department ? $user->department->dept_name : 'None') . "\n";
    
    if ($user->accnt_id == 53) {
        echo "*** This is the user assigned to request 13 ***\n";
        
        // Test the permission logic manually
        $formRequest = App\Models\FormRequest::find(13);
        if ($formRequest) {
            $targetDepartment = $formRequest->toDepartment;
            $isPFMORequest = $targetDepartment && $targetDepartment->dept_code === 'PFMO';
            $isPFMOUser = $user->department && $user->department->dept_code === 'PFMO';
            
            echo "Permission Check:\n";
            echo "- Is PFMO Request: " . ($isPFMORequest ? 'YES' : 'NO') . "\n";
            echo "- Is PFMO User: " . ($isPFMOUser ? 'YES' : 'NO') . "\n";
            echo "- User Position: " . $user->position . "\n";
            echo "- User Access Role: " . $user->accessRole . "\n";
            echo "- Request Status: " . $formRequest->status . "\n";
            
            $canSendFeedback = $isPFMORequest && $isPFMOUser && 
                               $user->position === 'Staff' && 
                               $user->accessRole === 'Approver' && 
                               $formRequest->status === 'Under Sub-Department Evaluation';
                               
            echo "- Can Send Feedback: " . ($canSendFeedback ? 'YES' : 'NO') . "\n";
        }
    }
}
