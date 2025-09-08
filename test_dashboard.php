<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Dashboard Variables ===\n\n";

try {
    // Test secretary user
    $secretary = App\Models\User::where('username', 'registraroffice_secretary')
        ->with(['department', 'employeeInfo'])
        ->first();
    
    if (!$secretary) {
        echo "❌ Secretary not found\n";
        exit(1);
    }
    
    echo "✅ Secretary found: " . $secretary->username . "\n";
    echo "   Department: " . ($secretary->department ? $secretary->department->dept_name : 'NULL') . "\n";
    echo "   Employee Info: " . ($secretary->employeeInfo ? $secretary->employeeInfo->FirstName . ' ' . $secretary->employeeInfo->LastName : 'NULL') . "\n";
    echo "   Position: " . $secretary->position . "\n\n";
    
    // Test dashboard data
    $stats = [
        'total_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)->count(),
        'pending_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('status', 'Pending')->count(),
        'approved_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('status', 'Approved')->count(),
        'proxy_requests' => App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
            ->where('is_proxy_submission', true)->count(),
    ];
    
    echo "📊 Dashboard Stats:\n";
    echo "   Total Requests: " . $stats['total_requests'] . "\n";
    echo "   Pending: " . $stats['pending_requests'] . "\n";
    echo "   Approved: " . $stats['approved_requests'] . "\n";
    echo "   Proxy Requests: " . $stats['proxy_requests'] . "\n\n";
    
    // Test recent requests
    $recentRequests = App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
        ->with(['fromDepartment', 'toDepartment'])
        ->orderBy('date_submitted', 'desc')
        ->limit(5)
        ->get();
    
    echo "📋 Recent Requests: " . $recentRequests->count() . "\n";
    
    // Test recent employees
    $recentEmployees = App\Models\EmployeeInfo::whereHas('user', function($query) use ($secretary) {
        $query->where('department_id', $secretary->department_id)
              ->where('position', '!=', 'Secretary');
    })->with(['user.department'])
    ->orderBy('LastName', 'asc')
    ->limit(5)
    ->get();
    
    echo "👥 Department Employees: " . $recentEmployees->count() . "\n";
    
    echo "\n✅ All dashboard variables available!\n";
    echo "Dashboard should work now.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
