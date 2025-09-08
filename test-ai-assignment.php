<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Enhanced AI Auto-Assignment ===\n\n";

// Test the enhanced auto-assignment
$testRequests = [
    'Air conditioning repair needed urgently - The AC in Room 301 is completely broken and not cooling',
    'Computer network problem - Cannot connect to internet in computer lab',
    'Cleaning service required - Office is very messy and needs sanitization',
    'Electrical emergency - Power outlet sparking in faculty room, urgent repair needed'
];

foreach ($testRequests as $index => $request) {
    echo "Test " . ($index + 1) . ": {$request}\n";
    
    try {
        $result = App\Services\RequestTypeService::autoAssignDepartment($request, $request);
        
        if ($result) {
            echo "✓ Assigned to: {$result['department']->dept_name}\n";
            echo "✓ Confidence Score: {$result['confidence_score']}\n";
            echo "✓ Confidence Level: {$result['confidence_level']}\n";
            echo "✓ Reasoning: {$result['reasoning']}\n";
            if (isset($result['ai_analysis'])) {
                echo "✓ AI Analysis: " . json_encode($result['ai_analysis']) . "\n";
            }
        } else {
            echo "✗ No assignment found\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "---\n\n";
}

echo "Testing completed!\n";
