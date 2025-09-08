<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find PFMO department
$pfmoDept = App\Models\Department::where('dept_code', 'PFMO')->first();

if ($pfmoDept) {
    echo "PFMO Department:\n";
    echo "- ID: " . $pfmoDept->department_id . "\n";
    echo "- Code: " . $pfmoDept->dept_code . "\n";
    echo "- Name: " . $pfmoDept->dept_name . "\n";
    
    // Find users in PFMO department
    $pfmoUsers = App\Models\User::where('department_id', $pfmoDept->department_id)->get();
    echo "\nUsers in PFMO:\n";
    foreach ($pfmoUsers as $user) {
        echo "- ID: " . $user->accnt_id . ", Position: " . $user->position . ", Access Role: " . $user->accessRole . "\n";
    }
    
    // Check if any staff member can see the request
    echo "\nChecking requests for PFMO staff:\n";
    foreach ($pfmoUsers as $user) {
        if ($user->position === 'Staff' && $user->accessRole === 'Approver') {
            $query = App\Models\FormRequest::query()
                ->where(function ($mainQuery) use ($user) {
                    $mainQuery->where(function ($subDeptQuery) use ($user) {
                        $subDeptQuery->where('status', 'Under Sub-Department Evaluation')
                            ->where('current_approver_id', $user->accnt_id)
                            ->where('to_department_id', $user->department_id);
                    });
                    
                    $mainQuery->orWhere(function ($pfmoSubQuery) use ($user) {
                        $pfmoSubQuery->where('status', 'Under Sub-Department Evaluation')
                            ->where('to_department_id', $user->department_id);
                    });
                });
            
            $visibleRequests = $query->get();
            echo "Staff {$user->accnt_id} can see " . $visibleRequests->count() . " requests:\n";
            foreach ($visibleRequests as $req) {
                echo "  - Request {$req->form_id} (Status: {$req->status}, Assigned to: {$req->current_approver_id})\n";
            }
        }
    }
} else {
    echo "PFMO department not found\n";
}
