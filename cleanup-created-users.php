<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Cleaning Up Created Users ===\n";

// Delete the users I created
$usersToDelete = ['PFMO-HEAD-001', 'PFMO-HVAC-001', 'PFMO-ELEC-001', 'PFMO-MAINT-001', 'CCS-FACULTY-001'];

foreach($usersToDelete as $empNo) {
    $user = App\Models\User::where('Emp_No', $empNo)->first();
    if($user) {
        echo "Deleting user: {$empNo} - {$user->username}\n";
        $user->delete();
    }
    
    $employee = App\Models\EmployeeInfo::where('Emp_No', $empNo)->first();
    if($employee) {
        echo "Deleting employee info: {$empNo}\n";
        $employee->delete();
    }
}

// Also delete the CCS department I created
$ccsDept = App\Models\Department::where('dept_name', 'College of Computer Studies')->first();
if($ccsDept) {
    echo "Deleting CCS department\n";
    $ccsDept->delete();
}

echo "Cleanup completed!\n";

echo "\n=== Current PFMO Staff (excluding Head) ===\n";
$pfmoStaff = App\Models\User::join('tb_employeeinfo', 'tb_account.Emp_No', '=', 'tb_employeeinfo.Emp_No')
    ->join('tb_department', 'tb_account.department_id', '=', 'tb_department.department_id')
    ->where('tb_department.dept_name', 'Physical Facilities Management Office')
    ->where('tb_account.position', 'Staff')
    ->select('tb_account.*', 'tb_employeeinfo.FirstName', 'tb_employeeinfo.LastName')
    ->limit(3)
    ->get();

foreach($pfmoStaff as $index => $staff) {
    echo ($index + 1) . ". {$staff->FirstName} {$staff->LastName} (Emp_No: {$staff->Emp_No})\n";
}

echo "\nDone!\n";
