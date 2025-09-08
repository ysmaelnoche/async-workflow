<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\RequestTypeService;

// Test various request types
$testRequests = [
    'Air conditioning repair in Room 301 - The aircon is not cooling properly',
    'Electrical wiring issue in the faculty room',
    'Broken door lock in the library',
    'Network connectivity problem in computer lab',
    'Cleaning service needed for the auditorium',
    'General maintenance request for building repairs'
];

echo "=== PFMO Auto-Assignment Test Results ===\n\n";

foreach ($testRequests as $index => $request) {
    echo "Test " . ($index + 1) . ": " . $request . "\n";
    $result = RequestTypeService::autoAssignDepartment($request, $request);
    
    if ($result) {
        echo "Assigned to: " . $result['department']->dept_name . " (Score: " . $result['confidence_score'] . ")\n";
        echo "Category: " . $result['category'] . "\n";
        echo "Auto-assigned: " . ($result['auto_assigned'] ? 'Yes' : 'No') . "\n";
        
        // Check for PFMO sub-department
        if ($result['category'] === 'pfmo') {
            $subDept = RequestTypeService::getPFMOSubDepartmentAssignment($request, $request);
            if ($subDept) {
                echo "Sub-department: " . $subDept['sub_department'] . " (Score: " . $subDept['score'] . ")\n";
                echo "Specialist: " . $subDept['specialist'] . "\n";
            }
        }
    } else {
        echo "No department assignment found\n";
    }
    echo "---\n\n";
}

echo "Auto-assignment testing completed!\n";
