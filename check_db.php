<?php

// Quick database check script
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

echo "=== SUB-DEPARTMENTS ===\n";
foreach (\App\Models\SubDepartment::all() as $dept) {
    echo $dept->id . ": " . $dept->subdepartment_code . " - " . $dept->name . "\n";
}

echo "\n=== PFMO USERS WITH SUB-DEPARTMENTS ===\n";
$pfmoUsers = \App\Models\User::with(['subDepartment', 'department'])
    ->whereHas('department', function($query) {
        $query->where('dept_code', 'PFMO');
    })
    ->get();

foreach ($pfmoUsers as $user) {
    $subDept = $user->subDepartment ? $user->subDepartment->subdepartment_code : 'No Sub-Dept';
    echo $user->username . " (" . $user->position . ") -> " . $subDept . "\n";
}

echo "\n=== RECENT PFMO REQUESTS ===\n";
$recentRequests = \App\Models\FormRequest::with(['iomDetails', 'toDepartment'])
    ->whereHas('toDepartment', function($query) {
        $query->where('dept_code', 'PFMO');
    })
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

foreach ($recentRequests as $request) {
    echo "Request ID: " . $request->form_id . " - " . $request->title . "\n";
    echo "  Status: " . $request->status . "\n";
    echo "  Assigned Sub-Dept: " . ($request->assigned_sub_department ?? 'None') . "\n";
    echo "  Description: " . ($request->iomDetails->body ?? 'N/A') . "\n\n";
}
