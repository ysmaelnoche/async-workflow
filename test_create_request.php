<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Create Proxy Request Variables ===\n\n";

try {
    // Test secretary user
    $secretary = App\Models\User::where('username', 'registraroffice_secretary')->first();
    
    if (!$secretary) {
        echo "❌ Secretary not found\n";
        exit(1);
    }
    
    echo "✅ Secretary found: " . $secretary->username . "\n\n";
    
    // Test employee (from previous test we know HEAD002 exists)
    $employee = App\Models\EmployeeInfo::with('user.department')->where('Emp_No', 'HEAD002')->first();
    
    if (!$employee) {
        echo "❌ Employee HEAD002 not found\n";
        exit(1);
    }
    
    echo "✅ Employee found: " . $employee->FirstName . " " . $employee->LastName . " (" . $employee->Emp_No . ")\n\n";
    
    // Test departments
    $departments = App\Models\Department::orderBy('dept_name')->get();
    echo "🏢 Departments: " . $departments->count() . " found\n";
    if ($departments->count() > 0) {
        echo "   Sample departments:\n";
        foreach ($departments->take(3) as $dept) {
            echo "   - " . $dept->dept_name . " (ID: " . $dept->department_id . ")\n";
        }
    }
    echo "\n";
    
    // Test request types
    $requestTypes = App\Services\AutomatedRoutingService::getAvailableRequestTypes();
    echo "📋 Request Types: " . count($requestTypes) . " available\n";
    foreach ($requestTypes as $key => $type) {
        echo "   - $key: " . $type['label'] . "\n";
    }
    echo "\n";
    
    // Test routing info
    $routingInfo = [];
    foreach (array_keys($requestTypes) as $type) {
        $routingInfo[$type] = App\Services\AutomatedRoutingService::getDestinationForRequestType($type, $secretary->department_id);
    }
    
    echo "🎯 Routing Info:\n";
    foreach ($routingInfo as $type => $info) {
        echo "   - $type → " . $info['department_name'] . " (ID: " . $info['department_id'] . ")\n";
    }
    
    echo "\n✅ Create Proxy Request page should work now!\n";
    echo "All required variables are available.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
