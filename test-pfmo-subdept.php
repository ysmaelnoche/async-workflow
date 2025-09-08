<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\RequestTypeService;

// Test PFMO-specific requests for sub-department assignment
$pfmoRequests = [
    'Air conditioning repair in Room 301 - The aircon is not cooling properly',
    'Electrical wiring issue in the faculty room - sparks coming from outlet',
    'General maintenance request for building repairs and painting',
    'HVAC system maintenance for proper ventilation in office',
    'Lighting fixtures need replacement in hallway',
    'Plumbing repair needed in restroom - water leak',
    'Emergency electrical repair - power outage in building'
];

echo "=== PFMO Sub-Department Assignment Test Results ===\n\n";

foreach ($pfmoRequests as $index => $request) {
    echo "Test " . ($index + 1) . ": " . $request . "\n";
    
    // First check main department assignment
    $mainResult = RequestTypeService::autoAssignDepartment($request, $request);
    if ($mainResult) {
        echo "Main Department: " . $mainResult['department']->dept_name . "\n";
        echo "Category: " . $mainResult['category'] . "\n";
        
        // If assigned to PFMO, check sub-department
        if ($mainResult['category'] === 'facility_maintenance') {
            $subDept = RequestTypeService::getPFMOSubDepartmentAssignment($request, $request);
            if ($subDept) {
                echo "Sub-department: " . $subDept['sub_department'] . "\n";
                echo "Name: " . $subDept['name'] . "\n";
                echo "Code: " . $subDept['code'] . "\n";
                echo "Sub-dept ID: " . $subDept['sub_department_id'] . "\n";
                echo "Score: " . $subDept['confidence_score'] . "\n";
            } else {
                echo "No specific sub-department assignment\n";
            }
        }
    } else {
        echo "No department assignment found\n";
    }
    echo "---\n\n";
}

echo "PFMO sub-department testing completed!\n";
