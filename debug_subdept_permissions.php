<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PFMO Sub-Department Mapping Debug ===\n\n";

// Check the user's sub-department
$user = App\Models\User::where('username', 'PFMO-2025-0054')->first();
if ($user) {
    echo "User: {$user->username}\n";
    echo "Sub-Department ID: {$user->sub_department_id}\n";
    echo "Position: {$user->position}\n";
    echo "Access Role: {$user->accessRole}\n";
    echo "Department ID: {$user->department_id}\n";
    
    // Find what sub_department_id = 3 refers to
    $subDepts = \Illuminate\Support\Facades\DB::table('sub_departments')->get();
    echo "\nSub-Department Mapping:\n";
    foreach ($subDepts as $subDept) {
        echo "ID {$subDept->id}: {$subDept->name} (Code: {$subDept->code})\n";
        if ($subDept->id == 3) {
            echo "  ^^^ This is the user's sub-department\n";
        }
    }
} else {
    echo "User PFMO-2025-0054 not found!\n";
}

// Check current requests under evaluation
echo "\n=== Current Request Status ===\n";
$requests = App\Models\FormRequest::where('status', 'Under Sub-Department Evaluation')
    ->get();

foreach ($requests as $request) {
    echo "Form {$request->form_id}:\n";
    echo "  - Status: {$request->status}\n";
    echo "  - Assigned Sub-Department: " . ($request->assigned_sub_department ?? 'NULL') . "\n";
    echo "  - Current Approver ID: " . ($request->current_approver_id ?? 'NULL') . "\n";
    
    // Show what the current permission logic would return
    if ($user && $request->assigned_sub_department) {
        echo "  - Permission Check:\n";
        echo "    * User sub_department_id: {$user->sub_department_id}\n";
        echo "    * Request assigned_sub_department: {$request->assigned_sub_department}\n";
        echo "    * Should user be able to send feedback? ";
        
        // Check if sub_department_id 3 maps to 'general_services'
        $subDept = \Illuminate\Support\Facades\DB::table('sub_departments')->where('id', 3)->first();
        if ($subDept && $subDept->code === 'general_services' && $request->assigned_sub_department === 'general_services') {
            echo "YES (matching sub-department)\n";
        } else {
            echo "NO (sub-department mismatch)\n";
            echo "    * Sub-dept ID 3 code: " . ($subDept->code ?? 'not found') . "\n";
        }
    }
    echo "\n";
}
