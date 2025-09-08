<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PFMO User Position Check ===\n\n";

$pfmoUsers = App\Models\User::whereHas('department', function($q) {
    $q->where('dept_code', 'PFMO');
})->get();

echo "All PFMO Users:\n";
foreach ($pfmoUsers as $user) {
    echo "Username: {$user->username}\n";
    echo "  Position: {$user->position}\n";
    echo "  Access Role: {$user->accessRole}\n";
    echo "  Sub-Department ID: " . ($user->sub_department_id ?? 'None') . "\n";
    
    if ($user->username === 'PFMO-2025-0052') {
        echo "  ^^^ This should be the PFMO Head\n";
    }
    echo "\n";
}

// Check if we need to find the actual PFMO Head
$actualPFMOHead = App\Models\User::whereHas('department', function($q) {
    $q->where('dept_code', 'PFMO');
})->where('position', 'Head')->first();

if ($actualPFMOHead) {
    echo "Actual PFMO Head found: {$actualPFMOHead->username}\n";
} else {
    echo "❌ No user with position 'Head' found in PFMO department!\n";
    echo "Available positions in PFMO:\n";
    $positions = App\Models\User::whereHas('department', function($q) {
        $q->where('dept_code', 'PFMO');
    })->pluck('position')->unique();
    
    foreach ($positions as $pos) {
        echo "  - {$pos}\n";
    }
}

echo "\n=== Testing Current Request Status ===\n";
$request = App\Models\FormRequest::find(15);
echo "Request 15 Status: {$request->status}\n";
echo "Should allow Evaluate? " . (in_array($request->status, ['In Progress', 'Pending Target Department Approval']) ? 'YES' : 'NO') . "\n";
echo "Should allow Send Feedback? " . ($request->status === 'Under Sub-Department Evaluation' ? 'YES' : 'NO') . "\n";
