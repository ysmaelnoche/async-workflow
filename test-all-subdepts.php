<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\RequestTypeService;

// Test specific requests for each sub-department
$testRequests = [
    // Construction Division
    'Building renovation project for the library - need structural work',
    'Concrete repair work needed for foundation cracks',
    'Roof tiles replacement and ceiling repair required',
    'Carpentry work for new furniture installation',
    'Painting and wall renovation for classroom',
    
    // Housekeeping Division  
    'Cleaning service needed for the auditorium after event',
    'Janitorial services required for restroom maintenance',
    'Floor mopping and disinfection of laboratory areas',
    'Waste management and garbage collection request',
    'Window cleaning service for office buildings',
    
    // General Services Division
    'Equipment delivery and setup for computer lab',
    'Office furniture procurement and installation',
    'General supplies inventory and transportation',
    'Miscellaneous services for event logistics'
];

echo "=== Testing All 3 PFMO Sub-Departments ===\n\n";

foreach ($testRequests as $index => $request) {
    echo "Test " . ($index + 1) . ": " . $request . "\n";
    
    $mainResult = RequestTypeService::autoAssignDepartment($request, $request);
    if ($mainResult && $mainResult['category'] === 'facility_maintenance') {
        $subDept = RequestTypeService::getPFMOSubDepartmentAssignment($request, $request);
        if ($subDept) {
            echo "✓ {$subDept['name']} (ID: {$subDept['sub_department_id']}) - Score: {$subDept['confidence_score']}\n";
        }
    } else {
        echo "Not assigned to PFMO\n";
    }
    echo "---\n";
}

echo "\nTesting completed!\n";
