<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Department Duplicates Investigation ===\n\n";

try {
    // Check for duplicate department names
    $allDepts = App\Models\Department::orderBy('dept_name')->get();
    echo "Total departments: " . $allDepts->count() . "\n\n";
    
    // Group by name to find duplicates
    $grouped = $allDepts->groupBy('dept_name');
    
    echo "Unique department names: " . $grouped->count() . "\n\n";
    
    echo "Departments with duplicates:\n";
    foreach ($grouped as $name => $depts) {
        if ($depts->count() > 1) {
            echo "- $name: " . $depts->count() . " records\n";
            foreach ($depts as $dept) {
                echo "  ID: " . $dept->department_id . "\n";
            }
            echo "\n";
        }
    }
    
    echo "=== Solution Options ===\n";
    echo "1. Use distinct department names\n";
    echo "2. Group by name and select first ID\n";
    echo "3. Remove duplicate records from database\n\n";
    
    // Show what the fixed query would return
    $distinctDepts = App\Models\Department::select('dept_name')
        ->distinct()
        ->orderBy('dept_name')
        ->get();
    
    echo "Distinct departments would return: " . $distinctDepts->count() . " unique names\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
