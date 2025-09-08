<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Creating test request for PFMO Evaluate workflow...\n";
    
    // Create a new form request
    $requestId = DB::table('form_requests')->insertGetId([
        'form_type' => 'IOM',
        'title' => 'Test PFMO Evaluate Workflow - Equipment Request',
        'description' => 'Testing the new PFMO workflow with Evaluate and Send Feedback actions',
        'requester_id' => 9, // Regular user
        'to_department_id' => 8, // PFMO department
        'status' => 'In Progress',
        'current_approver_id' => 8, // PFMO Head
        'date_submitted' => Carbon::now(),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    
    // Create IOM details
    DB::table('iom_details')->insert([
        'form_request_id' => $requestId,
        'purpose' => 'Testing PFMO workflow enhancement',
        'body' => 'This is a test request to verify the new PFMO evaluate workflow with sub-department assignment and feedback collection.',
        'date_needed' => Carbon::now()->addDays(7),
        'priority' => 'Medium',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    
    // Create initial submission approval record
    DB::table('form_approvals')->insert([
        'form_request_id' => $requestId,
        'approver_id' => 9, // Requester
        'action' => 'Submitted',
        'action_date' => Carbon::now(),
        'comments' => 'Initial submission for PFMO evaluation',
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);
    
    echo "✅ Test request created successfully!\n";
    echo "Request ID: $requestId\n";
    echo "Status: In Progress\n";
    echo "Current Approver: PFMO Head (User ID 8)\n";
    echo "Next Action: The PFMO Head should see 'Evaluate' and 'Reject' buttons\n";
    echo "\nTo test:\n";
    echo "1. Login as PFMO Head\n";
    echo "2. Navigate to Approvals\n";
    echo "3. View Request ID $requestId\n";
    echo "4. You should see 'Evaluate' and 'Reject' buttons instead of 'Approve'\n";
    echo "5. Click 'Evaluate' to start the PFMO workflow\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
