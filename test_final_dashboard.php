<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Comprehensive Dashboard Test ===\n\n";

try {
    // Test secretary user
    $secretary = App\Models\User::where('username', 'registraroffice_secretary')
        ->with(['department', 'employeeInfo'])
        ->first();
    
    if (!$secretary) {
        echo "❌ Secretary not found\n";
        exit(1);
    }
    
    echo "✅ Secretary User: " . $secretary->username . "\n";
    echo "   Department: " . ($secretary->department ? $secretary->department->dept_name : 'NULL') . "\n";
    echo "   Employee: " . ($secretary->employeeInfo ? $secretary->employeeInfo->FirstName . ' ' . $secretary->employeeInfo->LastName : 'NULL') . "\n\n";
    
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
    
    echo "📊 Dashboard Stats:\n";
    foreach ($stats as $key => $value) {
        echo "   ✅ $key: $value\n";
    }
    echo "\n";
    
    // Test recent requests
    $recentRequests = App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
        ->with(['fromDepartment', 'toDepartment'])
        ->orderBy('date_submitted', 'desc')
        ->limit(5)
        ->get();
    
    echo "📋 Recent Requests: " . $recentRequests->count() . " found\n";
    
    // Test proxy requests
    $proxyRequests = App\Models\FormRequest::where('requested_by', $secretary->accnt_id)
        ->where('is_proxy_submission', true)
        ->with(['fromDepartment', 'toDepartment'])
        ->orderBy('date_submitted', 'desc')
        ->limit(10)
        ->get();
    
    echo "📋 Proxy Requests: " . $proxyRequests->count() . " found\n";
    
    // Test recent employees
    $recentEmployees = App\Models\EmployeeInfo::whereHas('user', function($query) use ($secretary) {
        $query->where('department_id', $secretary->department_id)
              ->where('position', '!=', 'Secretary');
    })->with(['user.department'])
    ->orderBy('LastName', 'asc')
    ->limit(5)
    ->get();
    
    echo "👥 Recent Employees: " . $recentEmployees->count() . " found\n";
    
    if ($recentEmployees->count() > 0) {
        echo "   Employee sample: " . $recentEmployees->first()->FirstName . " " . $recentEmployees->first()->LastName . " (" . $recentEmployees->first()->Emp_No . ")\n";
    }
    
    // Test department name
    $departmentName = $secretary->department ? $secretary->department->dept_name : 'Unknown Department';
    echo "🏢 Department Name: $departmentName\n";
    
    echo "\n✅ ALL DASHBOARD VARIABLES READY!\n";
    echo "Dashboard should work without any undefined variable errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
