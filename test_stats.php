<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Updated Dashboard Stats ===\n\n";

try {
    // Test secretary user
    $secretary = App\Models\User::where('username', 'registraroffice_secretary')
        ->with(['department', 'employeeInfo'])
        ->first();
    
    if (!$secretary) {
        echo "❌ Secretary not found\n";
        exit(1);
    }
    
    echo "✅ Secretary found: " . $secretary->username . "\n\n";
    
    // Test all dashboard stats
    $stats = [
        'total_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)->count(),
        'pending_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('status', 'Pending')->count(),
        'approved_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('status', 'Approved')->count(),
        'total_proxy_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('is_proxy_submission', true)->count(),
        'pending_proxy_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('is_proxy_submission', true)
            ->where('status', 'Pending')->count(),
        'approved_this_month' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('status', 'Approved')
            ->whereMonth('date_submitted', now()->month)
            ->whereYear('date_submitted', now()->year)
            ->count(),
        'employees_helped' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('is_proxy_submission', true)
            ->distinct('actual_requestor_employee_id')
            ->count('actual_requestor_employee_id'),
    ];
    
    echo "📊 All Dashboard Stats:\n";
    foreach ($stats as $key => $value) {
        echo "   $key: $value\n";
    }
    
    echo "\n✅ All stats computed successfully!\n";
    echo "Dashboard should work now without any undefined key errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
