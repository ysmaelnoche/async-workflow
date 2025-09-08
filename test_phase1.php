<?php

// Phase 1 Testing Script
echo "=== PHASE 1 TESTING RESULTS ===\n\n";

// Test 1: Check Secretary Accounts
echo "1. SECRETARY ACCOUNTS:\n";
$secretaries = App\Models\User::where('position', 'Secretary')->get();
echo "   Total Secretaries: " . $secretaries->count() . "\n";

echo "   Sample Accounts:\n";
foreach($secretaries->take(5) as $secretary) {
    echo "   - {$secretary->username} | Position: {$secretary->position} | Access: {$secretary->accessRole}\n";
}

// Test 2: Check Department Coverage
echo "\n2. DEPARTMENT COVERAGE:\n";
$departments = App\Models\Department::all();
echo "   Total Departments: " . $departments->count() . "\n";

$departmentsWithSecretaries = App\Models\Department::whereHas('users', function($query) {
    $query->where('position', 'Secretary');
})->count();
echo "   Departments with Secretaries: " . $departmentsWithSecretaries . "\n";

// Test 3: Check PFMO Secretaries
echo "\n3. PFMO SECRETARIES:\n";
$pfmoSecretaries = App\Models\User::where('position', 'Secretary')
    ->whereHas('department', function($query) {
        $query->where('dept_code', 'PFMO');
    })->get();
    
echo "   PFMO Secretaries: " . $pfmoSecretaries->count() . "\n";
foreach($pfmoSecretaries as $pfmoSec) {
    echo "   - {$pfmoSec->username}\n";
}

// Test 4: Model Helper Methods
echo "\n4. MODEL HELPER METHODS:\n";
$testSecretary = App\Models\User::where('position', 'Secretary')->first();
if($testSecretary) {
    echo "   Testing with: {$testSecretary->username}\n";
    echo "   isSecretary(): " . ($testSecretary->isSecretary() ? 'true' : 'false') . "\n";
    echo "   canSubmitProxyRequests(): " . ($testSecretary->canSubmitProxyRequests() ? 'true' : 'false') . "\n";
    echo "   isDepartmentHead(): " . ($testSecretary->isDepartmentHead() ? 'true' : 'false') . "\n";
}

echo "\n=== END TESTING ===\n";
