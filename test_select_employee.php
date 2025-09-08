<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Select Employee Page Variables ===\n\n";

try {
    // Test secretary user
    $secretary = App\Models\User::where('username', 'registraroffice_secretary')->first();
    
    if (!$secretary) {
        echo "❌ Secretary not found\n";
        exit(1);
    }
    
    echo "✅ Secretary found: " . $secretary->username . "\n";
    echo "   Department ID: " . $secretary->department_id . "\n\n";
    
    // Test department employees query (same as in controller)
    $departmentEmployees = App\Models\EmployeeInfo::whereHas('user', function($query) use ($secretary) {
        $query->where('department_id', $secretary->department_id)
              ->where('position', '!=', 'Secretary');
    })->with(['user.department'])
    ->orderBy('LastName', 'asc')
    ->orderBy('FirstName', 'asc')
    ->get();
    
    echo "👥 Department Employees: " . $departmentEmployees->count() . " found\n";
    
    if ($departmentEmployees->count() > 0) {
        echo "   Employees in department:\n";
        foreach ($departmentEmployees as $employee) {
            echo "   - " . $employee->FirstName . " " . $employee->LastName . " (" . $employee->Emp_No . ")\n";
            echo "     Position: " . ($employee->user ? $employee->user->position : 'N/A') . "\n";
        }
    } else {
        echo "   ⚠️  No employees found in department (other than secretary)\n";
    }
    
    echo "\n✅ Select Employee page should work now!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
