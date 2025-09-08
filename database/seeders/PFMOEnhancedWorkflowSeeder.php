<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;
use App\Models\EmployeeInfo;
use Illuminate\Support\Facades\Hash;

class PFMOEnhancedWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeder for PFMO Enhanced Workflow
     */
    public function run(): void
    {
        // Ensure PFMO department exists
        $pfmoDepartment = Department::firstOrCreate(
            ['dept_code' => 'PFMO'],
            [
                'dept_name' => 'Physical Facilities Management Office',
                'category' => 'Non-teaching'
            ]
        );

        $this->command->info("PFMO Department: {$pfmoDepartment->dept_name} (ID: {$pfmoDepartment->department_id})");

        // Ensure Housekeeping department exists for auto-assignment
        $housekeepingDepartment = Department::firstOrCreate(
            ['dept_code' => 'HSK'],
            [
                'dept_name' => 'Housekeeping Department',
                'category' => 'Non-teaching'
            ]
        );

        $this->command->info("Housekeeping Department: {$housekeepingDepartment->dept_name} (ID: {$housekeepingDepartment->department_id})");

        // Ensure ICT department exists for auto-assignment
        $ictDepartment = Department::firstOrCreate(
            ['dept_code' => 'ICT'],
            [
                'dept_name' => 'Information and Communications Technology',
                'category' => 'Non-teaching'
            ]
        );

        $this->command->info("ICT Department: {$ictDepartment->dept_name} (ID: {$ictDepartment->department_id})");

        // Create sample PFMO users if they don't exist
        $this->createSamplePFMOUsers($pfmoDepartment);

        $this->command->info('PFMO Enhanced Workflow seeder completed successfully!');
    }

    /**
     * Create sample PFMO users for testing
     */
    private function createSamplePFMOUsers(Department $pfmoDepartment): void
    {
        // PFMO Head
        $pfmoHeadEmployee = EmployeeInfo::firstOrCreate(
            ['Emp_No' => 'PFMO-HEAD-001'],
            [
                'Titles' => 'Engr.',
                'FirstName' => 'Maria',
                'LastName' => 'Rodriguez',
                'MiddleName' => 'Santos',
                'Email' => 'maria.rodriguez@lyceum.edu.ph'
            ]
        );

        $pfmoHead = User::firstOrCreate(
            ['Emp_No' => 'PFMO-HEAD-001'],
            [
                'department_id' => $pfmoDepartment->department_id,
                'username' => 'pfmo-head',
                'password' => Hash::make('password123'),
                'position' => 'Head',
                'accessRole' => 'Approver'
            ]
        );

        $this->command->info("Created PFMO Head: {$pfmoHeadEmployee->FirstName} {$pfmoHeadEmployee->LastName}");

        // PFMO Staff - HVAC Specialist
        $hvacSpecialistEmployee = EmployeeInfo::firstOrCreate(
            ['Emp_No' => 'PFMO-HVAC-001'],
            [
                'Titles' => 'Mr.',
                'FirstName' => 'Juan',
                'LastName' => 'Cruz',
                'MiddleName' => 'Dela',
                'Email' => 'juan.cruz@lyceum.edu.ph'
            ]
        );

        $hvacSpecialist = User::firstOrCreate(
            ['Emp_No' => 'PFMO-HVAC-001'],
            [
                'department_id' => $pfmoDepartment->department_id,
                'username' => 'pfmo-hvac-specialist',
                'password' => Hash::make('password123'),
                'position' => 'Staff',
                'accessRole' => 'Approver'
            ]
        );

        $this->command->info("Created HVAC Specialist: {$hvacSpecialistEmployee->FirstName} {$hvacSpecialistEmployee->LastName}");

        // PFMO Staff - Electrical Specialist
        $electricalSpecialistEmployee = EmployeeInfo::firstOrCreate(
            ['Emp_No' => 'PFMO-ELEC-001'],
            [
                'Titles' => 'Mr.',
                'FirstName' => 'Roberto',
                'LastName' => 'Mendoza',
                'MiddleName' => 'Garcia',
                'Email' => 'roberto.mendoza@lyceum.edu.ph'
            ]
        );

        $electricalSpecialist = User::firstOrCreate(
            ['Emp_No' => 'PFMO-ELEC-001'],
            [
                'department_id' => $pfmoDepartment->department_id,
                'username' => 'pfmo-electrical-specialist',
                'password' => Hash::make('password123'),
                'position' => 'Staff',
                'accessRole' => 'Approver'
            ]
        );

        $this->command->info("Created Electrical Specialist: {$electricalSpecialistEmployee->FirstName} {$electricalSpecialistEmployee->LastName}");

        // PFMO Staff - General Maintenance
        $maintenanceStaffEmployee = EmployeeInfo::firstOrCreate(
            ['Emp_No' => 'PFMO-MAINT-001'],
            [
                'Titles' => 'Mr.',
                'FirstName' => 'Carlos',
                'LastName' => 'Reyes',
                'MiddleName' => 'Lopez',
                'Email' => 'carlos.reyes@lyceum.edu.ph'
            ]
        );

        $maintenanceStaff = User::firstOrCreate(
            ['Emp_No' => 'PFMO-MAINT-001'],
            [
                'department_id' => $pfmoDepartment->department_id,
                'username' => 'pfmo-maintenance-staff',
                'password' => Hash::make('password123'),
                'position' => 'Staff',
                'accessRole' => 'Approver'
            ]
        );

        $this->command->info("Created Maintenance Staff: {$maintenanceStaffEmployee->FirstName} {$maintenanceStaffEmployee->LastName}");

        // Create College of Computer Studies department for sample requester
        $ccsDepartment = Department::firstOrCreate(
            ['dept_name' => 'College of Computer Studies'],
            [
                'category' => 'Academic',
                'dept_code' => 'CCS'
            ]
        );

        // Sample requester from College of Computer Studies
        $requesterEmployee = EmployeeInfo::firstOrCreate(
            ['Emp_No' => 'CCS-FACULTY-001'],
            [
                'Titles' => 'Prof.',
                'FirstName' => 'Ana',
                'LastName' => 'Santos',
                'MiddleName' => 'Garcia',
                'Email' => 'ana.santos@lyceum.edu.ph'
            ]
        );

        $requester = User::firstOrCreate(
            ['Emp_No' => 'CCS-FACULTY-001'],
            [
                'department_id' => $ccsDepartment->department_id,
                'username' => 'ccs-faculty',
                'password' => Hash::make('password123'),
                'position' => 'Staff',
                'accessRole' => 'Viewer'
            ]
        );

        $this->command->info("Created Sample Requester: {$requesterEmployee->FirstName} {$requesterEmployee->LastName}");

        $this->command->info('PFMO Enhanced Workflow seeder completed successfully!');
    }
}
