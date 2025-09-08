<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\RequestController;

echo "=== Testing Form Submission ===\n\n";

// Create a mock request with the form data
$requestData = [
    'request_type' => 'IOM',
    'iom_to_department_id' => '1', // Assuming department ID 1 exists
    'iom_re' => 'Test Subject',
    'iom_priority' => 'Routine',
    'iom_purpose' => 'Request',
    'iom_specific_request_type' => 'Computer Repair',
    'iom_description' => 'Test description for computer repair',
    'iom_date_needed' => '2025-08-20',
];

try {
    // Check if we can create the models directly
    echo "Testing FormRequest model creation...\n";
    
    $formRequest = new App\Models\FormRequest();
    $formRequest->form_type = 'IOM';
    $formRequest->title = 'Test Subject';
    $formRequest->from_department_id = 1;
    $formRequest->to_department_id = 1;
    $formRequest->requested_by = 1; // Assuming user ID 1 exists
    $formRequest->date_submitted = now();
    
    echo "✓ FormRequest model can be created\n";
    
    echo "Testing IomDetail creation...\n";
    $iomDetail = new App\Models\IomDetail();
    $iomDetail->form_id = 999; // Just testing structure
    $iomDetail->date_needed = '2025-08-20';
    $iomDetail->priority = 'Routine';
    $iomDetail->purpose = 'Request - Computer Repair';
    $iomDetail->body = 'Test description';
    
    echo "✓ IomDetail model can be created\n";
    
    echo "\nAll models can be created successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
