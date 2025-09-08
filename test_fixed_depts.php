<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Fixed Department Query ===\n\n";

try {
    // Test the fixed query
    $departments = App\Models\Department::selectRaw('MIN(department_id) as department_id, dept_name')
        ->groupBy('dept_name')
        ->orderBy('dept_name')
        ->get();
    
    echo "Fixed query returns: " . $departments->count() . " unique departments\n\n";
    
    echo "First 10 departments:\n";
    foreach ($departments->take(10) as $dept) {
        echo "- " . $dept->dept_name . " (ID: " . $dept->department_id . ")\n";
    }
    
    echo "\n✅ Department dropdown should now show unique departments only!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
