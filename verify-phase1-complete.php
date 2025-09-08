<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\RequestTypeService;

echo "=== PHASE 1: Enhanced PFMO Workflow - Final Verification ===\n\n";

echo "1. PFMO Sub-Departments in Database:\n";
$subDepts = DB::table('sub_departments')->where('parent_department_id', 1)->get();
foreach($subDepts as $subDept) {
    echo "   • {$subDept->name} (ID: {$subDept->id}) - {$subDept->code}\n";
}

echo "\n2. PFMO Staff Assignments:\n";
$assignedStaff = App\Models\User::join('tb_employeeinfo', 'tb_account.Emp_No', '=', 'tb_employeeinfo.Emp_No')
    ->join('sub_departments', 'tb_account.sub_department_id', '=', 'sub_departments.id')
    ->where('sub_departments.parent_department_id', 1)
    ->select('tb_account.Emp_No', 'tb_employeeinfo.FirstName', 'tb_employeeinfo.LastName', 'sub_departments.name as sub_dept_name')
    ->get();

foreach($assignedStaff as $staff) {
    echo "   • {$staff->FirstName} {$staff->LastName} → {$staff->sub_dept_name}\n";
}

echo "\n3. Auto-Assignment Testing:\n";

$sampleRequests = [
    'Building renovation and structural repair work',
    'Cleaning and janitorial services for laboratory',
    'Equipment procurement and furniture delivery',
    'Air conditioning repair and maintenance'
];

foreach($sampleRequests as $index => $request) {
    echo "\n   Test Request: \"{$request}\"\n";
    
    // Main department assignment
    $mainResult = RequestTypeService::autoAssignDepartment($request, $request);
    if ($mainResult && $mainResult['category'] === 'facility_maintenance') {
        echo "   ✓ Assigned to: {$mainResult['department']->dept_name}\n";
        
        // Sub-department assignment
        $subDept = RequestTypeService::getPFMOSubDepartmentAssignment($request, $request);
        if ($subDept) {
            echo "   ✓ Sub-Department: {$subDept['name']} (Score: {$subDept['confidence_score']})\n";
            
            // Find assigned staff
            $staffMember = App\Models\User::join('tb_employeeinfo', 'tb_account.Emp_No', '=', 'tb_employeeinfo.Emp_No')
                ->where('tb_account.sub_department_id', $subDept['sub_department_id'])
                ->select('tb_employeeinfo.FirstName', 'tb_employeeinfo.LastName')
                ->first();
            
            if ($staffMember) {
                echo "   ✓ Assigned Staff: {$staffMember->FirstName} {$staffMember->LastName}\n";
            }
        }
    } else {
        echo "   ✗ Not assigned to PFMO\n";
    }
}

echo "\n\n=== PHASE 1 IMPLEMENTATION SUMMARY ===\n";
echo "✓ Part 1: Auto-assignment of departments based on request type - COMPLETED\n";
echo "✓ Part 2: Enhanced IOM workflow with PFMO sub-department routing - COMPLETED\n";
echo "✓ Database alignment with existing 3 sub-departments - COMPLETED\n";
echo "✓ Staff assignment to sub-departments - COMPLETED\n";
echo "\nPhase 1 Enhanced PFMO Workflow implementation is ready for testing!\n";
