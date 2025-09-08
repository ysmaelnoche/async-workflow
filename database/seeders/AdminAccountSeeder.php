<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\EmployeeInfo;
use App\Models\User;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Insert admin into tb_employeeinfo
        EmployeeInfo::updateOrCreate(
            ['Emp_No' => 'ADMIN-0000'],
            [
                'Titles' => 'Administrator',
                'LastName' => 'Admin',
                'FirstName' => 'System',
                'MiddleName' => '',
                'Suffix' => '',
                'Email' => 'admin@workflow.local',
            ]
        );

        // Insert admin into tb_account (User)
        User::updateOrCreate(
            ['Emp_No' => 'ADMIN-0000'],
            [
                'username' => 'ADMIN-0000',
                'password' => Hash::make('admin123'), // Default password: admin123
                'department_id' => 14, // Change if your admin's department_id is different
                'position' => 'Admin',
                'accessRole' => 'Admin',
                'status' => 'active',
            ]
        );
    }
}