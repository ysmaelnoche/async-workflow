<?php
/**
 * Secretary Workflow Test Script
 * Tests the complete Secretary proxy request workflow
 */

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmployeeInfo;
use App\Models\Department;
use App\Models\FormRequest;

echo "=== Secretary Workflow Test ===\n\n";

// Test 1: Verify Secretary Account
echo "1. Testing Secretary Account...\n";
$secretary = User::where('username', 'registraroffice_secretary')->first();
if ($secretary) {
    echo "   ✅ Secretary account found: {$secretary->username}\n";
    echo "   - Name: {$secretary->firstName} {$secretary->lastName}\n";
    echo "   - Department ID: {$secretary->department_id}\n";
    echo "   - Position: {$secretary->position}\n";
    echo "   - Access Role: {$secretary->accessRole}\n";
} else {
    echo "   ❌ Secretary account not found!\n";
    exit(1);
}

// Test 2: Verify Employee Data
echo "\n2. Testing Employee Selection...\n";
$employees = EmployeeInfo::with(['user'])
    ->whereHas('user', function($query) use ($secretary) {
        $query->where('department_id', $secretary->department_id);
    })
    ->limit(3)
    ->get();

if ($employees->count() > 0) {
    echo "   ✅ Found {$employees->count()} employees in secretary's department:\n";
    foreach ($employees as $employee) {
        echo "   - {$employee->FirstName} {$employee->LastName} ({$employee->Emp_No})\n";
    }
} else {
    echo "   ❌ No employees found in secretary's department!\n";
}

// Test 3: Check Department Head
echo "\n3. Testing Department Head for Approval...\n";
$departmentHead = User::where('department_id', $secretary->department_id)
    ->where('position', 'Head')
    ->where('accessRole', 'Approver')
    ->first();

if ($departmentHead) {
    echo "   ✅ Department head found: {$departmentHead->firstName} {$departmentHead->lastName}\n";
    echo "   - Account ID: {$departmentHead->accnt_id}\n";
} else {
    echo "   ⚠️  No department head found for approval routing\n";
}

// Test 4: Check Target Departments
echo "\n4. Testing Target Departments...\n";
$targetDepartments = Department::whereIn('department_id', [13, 14, 15, 16])->get();
if ($targetDepartments->count() > 0) {
    echo "   ✅ Found target departments:\n";
    foreach ($targetDepartments as $dept) {
        echo "   - {$dept->dept_name} (ID: {$dept->department_id})\n";
    }
} else {
    echo "   ❌ No target departments found!\n";
}

// Test 5: Simulate Proxy Request Creation
echo "\n5. Testing Proxy Request Creation...\n";
$testEmployee = $employees->first();
if ($testEmployee) {
    echo "   Creating test proxy request for: {$testEmployee->FirstName} {$testEmployee->LastName}\n";
    
    // Simulate form data
    $formData = [
        'form_type' => 'IOM',
        'title' => 'Test IOM Request via Secretary',
        'from_department_id' => $secretary->department_id,
        'to_department_id' => 13, // PFMO
        'requested_by' => $secretary->accnt_id,
        'current_approver_id' => $departmentHead ? $departmentHead->accnt_id : null,
        'status' => 'Pending',
        'date_submitted' => now(),
        'actual_requestor_name' => $testEmployee->FirstName . ' ' . $testEmployee->LastName,
        'actual_requestor_employee_id' => $testEmployee->Emp_No,
        'actual_requestor_department' => $testEmployee->user->department->dept_name ?? 'Unknown',
        'actual_requestor_position' => $testEmployee->user->position ?? 'Unknown',
        'is_proxy_submission' => true,
        'sub_status' => $departmentHead ? 'Awaiting Department Head Approval' : 'No Department Head Available',
    ];
    
    try {
        $testRequest = FormRequest::create($formData);
        echo "   ✅ Test proxy request created successfully!\n";
        echo "   - Request ID: {$testRequest->request_id}\n";
        echo "   - Status: {$testRequest->status}\n";
        echo "   - Sub-status: {$testRequest->sub_status}\n";
        echo "   - Actual Requestor: {$testRequest->actual_requestor_name}\n";
        
        // Clean up test data
        $testRequest->delete();
        echo "   ✅ Test data cleaned up\n";
        
    } catch (Exception $e) {
        echo "   ❌ Failed to create test request: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ No test employee available\n";
}

echo "\n=== Test Complete ===\n";
echo "Secretary workflow is ready for testing!\n";
echo "Login at: http://localhost:8000/login\n";
echo "Username: registraroffice_secretary\n";
echo "Password: password\n";
