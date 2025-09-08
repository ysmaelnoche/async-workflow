<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check General Services department
$generalServices = App\Models\Department::where('dept_code', 'general_services')
    ->orWhere('dept_name', 'like', '%General Services%')
    ->first();

if ($generalServices) {
    echo "General Services Department:\n";
    echo "- ID: " . $generalServices->department_id . "\n";
    echo "- Code: " . $generalServices->dept_code . "\n";
    echo "- Name: " . $generalServices->dept_name . "\n";
    
    // Find users in this department
    $users = App\Models\User::where('department_id', $generalServices->department_id)->get();
    echo "\nUsers in General Services:\n";
    foreach ($users as $user) {
        echo "- ID: " . $user->accnt_id . ", Position: " . $user->position . ", Access Role: " . $user->accessRole . "\n";
    }
} else {
    echo "General Services department not found\n";
    echo "Available departments:\n";
    $depts = App\Models\Department::all();
    foreach ($depts as $dept) {
        echo "- " . $dept->dept_code . ": " . $dept->dept_name . "\n";
    }
}
