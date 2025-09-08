<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\EmployeeInfo;
use Illuminate\Support\Facades\Hash;

class SecretaryAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::all();

        foreach ($departments as $department) {
            // Skip PFMO department as it has different structure
            if ($department->dept_code === 'PFMO') {
                continue;
            }

            $empNo = 'SEC' . str_pad($department->department_id, 3, '0', STR_PAD_LEFT);
            $username = strtolower(str_replace(' ', '', $department->dept_name)) . '_secretary';
            $email = strtolower(str_replace(' ', '', $department->dept_name)) . '.secretary@lyceum.edu.ph';

            // Check if secretary already exists
            if (EmployeeInfo::where('Emp_No', $empNo)->exists() || 
                User::where('username', $username)->exists() ||
                EmployeeInfo::where('Email', $email)->exists()) {
                $this->command->info("Secretary for {$department->dept_name} already exists, skipping...");
                continue;
            }

            // Create employee info for secretary
            $employeeInfo = EmployeeInfo::create([
                'Emp_No' => $empNo,
                'Titles' => 'Ms.',
                'FirstName' => $department->dept_name,
                'LastName' => 'Secretary',
                'MiddleName' => '',
                'Suffix' => '',
                'Email' => $email
            ]);

            // Create secretary account
            User::create([
                'username' => $username,
                'password' => Hash::make('secretary123'),
                'Emp_No' => $employeeInfo->Emp_No,
                'department_id' => $department->department_id,
                'position' => 'Secretary',
                'accessRole' => 'Requestor', // Secretaries can submit proxy requests
                'status' => 'Active'
            ]);
        }

        // Create PFMO secretaries for each sub-department
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();
        if ($pfmoDepartment) {
            $subDepartments = ['Construction', 'Housekeeping', 'General Services'];
            
            foreach ($subDepartments as $index => $subDept) {
                $empNo = 'PFMO-SEC' . ($index + 1);
                $username = 'pfmo_' . strtolower(str_replace(' ', '', $subDept)) . '_secretary';
                $email = 'pfmo.' . strtolower(str_replace(' ', '', $subDept)) . '.secretary@lyceum.edu.ph';

                // Check if PFMO secretary already exists
                if (EmployeeInfo::where('Emp_No', $empNo)->exists() || 
                    User::where('username', $username)->exists() ||
                    EmployeeInfo::where('Email', $email)->exists()) {
                    $this->command->info("PFMO {$subDept} Secretary already exists, skipping...");
                    continue;
                }

                $employeeInfo = EmployeeInfo::create([
                    'Emp_No' => $empNo,
                    'Titles' => 'Ms.',
                    'FirstName' => 'PFMO ' . $subDept,
                    'LastName' => 'Secretary',
                    'MiddleName' => '',
                    'Suffix' => '',
                    'Email' => $email
                ]);

                User::create([
                    'username' => $username,
                    'password' => Hash::make('secretary123'),
                    'Emp_No' => $employeeInfo->Emp_No,
                    'department_id' => $pfmoDepartment->department_id,
                    'position' => 'Secretary',
                    'accessRole' => 'Requestor', // Secretaries can submit proxy requests
                    'status' => 'Active'
                ]);
            }
        }

        $this->command->info('Secretary accounts created successfully for all departments!');
    }
}
