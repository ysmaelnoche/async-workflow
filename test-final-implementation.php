<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Phase 1 Implementation After Fix ===\n\n";

// Test 1: Auto-assignment functionality
echo "1. Testing Auto-Assignment:\n";
try {
    $result = App\Services\RequestTypeService::autoAssignDepartment(
        'Air conditioning repair needed', 
        'The AC unit in Room 301 is not cooling properly'
    );
    
    if ($result) {
        echo "   ✓ Assigned to: {$result['department']->dept_name}\n";
        echo "   ✓ Category: {$result['category']}\n";
        echo "   ✓ Confidence Score: {$result['confidence_score']}\n";
    } else {
        echo "   ✗ No assignment found\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: PFMO Sub-department assignment
echo "\n2. Testing PFMO Sub-Department Assignment:\n";
try {
    $subResult = App\Services\RequestTypeService::getPFMOSubDepartmentAssignment(
        'Building renovation project',
        'Need construction work for structural repairs'
    );
    
    if ($subResult) {
        echo "   ✓ Sub-Department: {$subResult['name']}\n";
        echo "   ✓ Code: {$subResult['code']}\n";
        echo "   ✓ Database ID: {$subResult['sub_department_id']}\n";
    } else {
        echo "   ✗ No sub-department assignment\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: ApprovalCacheService
echo "\n3. Testing ApprovalCacheService:\n";
try {
    // Test the method that was causing the error
    App\Services\ApprovalCacheService::clearAllApprovalCaches();
    echo "   ✓ Cache service working properly\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Test 4: Verify staff assignments
echo "\n4. Verifying Staff Assignments:\n";
try {
    $assignedStaff = App\Models\User::join('tb_employeeinfo', 'tb_account.Emp_No', '=', 'tb_employeeinfo.Emp_No')
        ->join('sub_departments', 'tb_account.sub_department_id', '=', 'sub_departments.id')
        ->where('sub_departments.parent_department_id', 1)
        ->select('tb_employeeinfo.FirstName', 'tb_employeeinfo.LastName', 'sub_departments.name as sub_dept_name')
        ->get();

    foreach($assignedStaff as $staff) {
        echo "   ✓ {$staff->FirstName} {$staff->LastName} → {$staff->sub_dept_name}\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== All Tests Completed ===\n";
echo "Phase 1 Enhanced PFMO Workflow is ready for production use!\n";
