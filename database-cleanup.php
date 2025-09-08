<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Database Analysis ===\n\n";

try {
    $formRequestsCount = \App\Models\FormRequest::count();
    $iomDetailsCount = \App\Models\IomDetail::count();
    
    echo "Form Requests count: {$formRequestsCount}\n";
    echo "IOM Details count: {$iomDetailsCount}\n\n";
    
    // Check the max form_id in each table
    $maxFormRequestId = \App\Models\FormRequest::max('form_id') ?: 0;
    $maxIomDetailId = \App\Models\IomDetail::max('form_id') ?: 0;
    
    echo "Max form_id in form_requests: {$maxFormRequestId}\n";
    echo "Max form_id in iom_details: {$maxIomDetailId}\n\n";
    
    // Check for orphaned records
    $orphanedIomDetails = \App\Models\IomDetail::whereNotIn('form_id', 
        \App\Models\FormRequest::pluck('form_id')
    )->get();
    
    echo "Orphaned IOM Details (no matching form_request): " . $orphanedIomDetails->count() . "\n";
    
    if ($orphanedIomDetails->count() > 0) {
        echo "Orphaned IOM Detail form_ids: " . $orphanedIomDetails->pluck('form_id')->join(', ') . "\n";
        
        // Clean up orphaned records
        $deleted = \App\Models\IomDetail::whereNotIn('form_id', 
            \App\Models\FormRequest::pluck('form_id')
        )->delete();
        
        echo "Deleted {$deleted} orphaned IOM detail records\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Analysis Complete ===\n";
?>
