<?php

/**
 * Script to update all "Noted" actions to "Approved" in the database
 * and update the workflow logic throughout the application
 */

require_once 'vendor/autoload.php';

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\FormApproval;

echo "Starting database update: Changing 'Noted' to 'Approved'...\n";

try {
    DB::beginTransaction();
    
    // Update form_approvals table - change all "Noted" actions to "Approved"
    $approvalCount = DB::table('form_approvals')
        ->where('action', 'Noted')
        ->update(['action' => 'Approved']);
    
    echo "Updated {$approvalCount} approval records from 'Noted' to 'Approved'\n";
    
    // Note: We don't need to update form_requests status since "Noted" was never used as a status
    // The status flow is: Pending -> In Progress -> Approved/Rejected
    
    DB::commit();
    echo "Database update completed successfully!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error updating database: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Database update completed. All 'Noted' actions have been changed to 'Approved'.\n";
echo "The workflow now uses only 'Approve' and 'Reject' actions.\n";
