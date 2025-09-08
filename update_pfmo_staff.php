<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find the first PFMO staff and update their access role to Approver
$pfmoDept = App\Models\Department::where('dept_code', 'PFMO')->first();
$pfmoStaff = App\Models\User::where('department_id', $pfmoDept->department_id)
    ->where('position', 'Staff')
    ->first();

if ($pfmoStaff) {
    echo "Updating PFMO staff access role...\n";
    echo "Before: Staff ID {$pfmoStaff->accnt_id}, Access Role: {$pfmoStaff->accessRole}\n";
    
    $pfmoStaff->accessRole = 'Approver';
    $pfmoStaff->save();
    
    echo "After: Staff ID {$pfmoStaff->accnt_id}, Access Role: {$pfmoStaff->accessRole}\n";
    echo "\nNow this staff member can handle sub-department evaluations!\n";
} else {
    echo "No PFMO staff found\n";
}
