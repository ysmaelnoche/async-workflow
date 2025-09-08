<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Assigning PFMO Staff to Sub-Departments ===\n";

// Get the 3 sub-departments
$subDepartments = DB::table('sub_departments')->where('parent_department_id', 1)->get();

echo "Available Sub-Departments:\n";
foreach($subDepartments as $subDept) {
    echo "- ID: {$subDept->id}, Name: {$subDept->name}, Code: {$subDept->code}\n";
}

// Get 3 PFMO staff members (excluding Head)
$pfmoStaff = App\Models\User::join('tb_employeeinfo', 'tb_account.Emp_No', '=', 'tb_employeeinfo.Emp_No')
    ->join('tb_department', 'tb_account.department_id', '=', 'tb_department.department_id')
    ->where('tb_department.dept_name', 'Physical Facilities Management Office')
    ->where('tb_account.position', 'Staff')
    ->where('tb_account.sub_department_id', null) // Not yet assigned
    ->select('tb_account.*', 'tb_employeeinfo.FirstName', 'tb_employeeinfo.LastName')
    ->limit(3)
    ->get();

echo "\nAssigning staff to sub-departments:\n";

foreach($pfmoStaff as $index => $staff) {
    if($index < count($subDepartments)) {
        $subDept = $subDepartments[$index];
        
        // Update the user's sub_department_id
        App\Models\User::where('Emp_No', $staff->Emp_No)
            ->update(['sub_department_id' => $subDept->id]);
        
        echo "✓ {$staff->FirstName} {$staff->LastName} → {$subDept->name}\n";
    }
}

echo "\n=== Verification ===\n";
$assignedStaff = App\Models\User::join('tb_employeeinfo', 'tb_account.Emp_No', '=', 'tb_employeeinfo.Emp_No')
    ->join('sub_departments', 'tb_account.sub_department_id', '=', 'sub_departments.id')
    ->where('sub_departments.parent_department_id', 1)
    ->select('tb_account.Emp_No', 'tb_employeeinfo.FirstName', 'tb_employeeinfo.LastName', 'sub_departments.name as sub_dept_name')
    ->get();

foreach($assignedStaff as $staff) {
    echo "✓ {$staff->FirstName} {$staff->LastName} ({$staff->Emp_No}) → {$staff->sub_dept_name}\n";
}

echo "\nAssignment completed!\n";
