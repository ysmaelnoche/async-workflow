<?php

use Illuminate\Support\Facades\Route;
use App\Services\PFMOEmployeeManagementService;
use App\Models\User;
use App\Models\Department;
use App\Models\SubDepartment;

Route::get('/test-employee-management', function () {
    try {
        // Check if PFMO department exists
        $pfmoDept = Department::where('dept_code', 'PFMO')->first();
        
        if (!$pfmoDept) {
            return response()->json([
                'error' => 'PFMO department not found',
                'departments' => Department::all()->pluck('dept_name', 'dept_code')
            ]);
        }
        
        // Check PFMO users
        $pfmoUsers = User::where('department_id', $pfmoDept->department_id)->get();
        
        // Check sub-departments
        $subDepartments = SubDepartment::all();
        
        // Test the service
        $employeeService = new PFMOEmployeeManagementService();
        $employees = $employeeService->getPFMOEmployees();
        $subDepts = $employeeService->getSubDepartmentsWithSupervisors();
        
        return response()->json([
            'success' => true,
            'pfmo_department' => $pfmoDept,
            'pfmo_users_count' => $pfmoUsers->count(),
            'pfmo_users' => $pfmoUsers->map(function($user) {
                return [
                    'id' => $user->accnt_id,
                    'name' => $user->username,
                    'emp_no' => $user->Emp_No,
                    'position' => $user->position,
                    'sub_department_id' => $user->sub_department_id
                ];
            }),
            'sub_departments_count' => $subDepartments->count(),
            'sub_departments' => $subDepartments,
            'service_employees' => $employees,
            'service_sub_departments' => $subDepts
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/test-manage-employees-view', function () {
    try {
        $employeeService = new PFMOEmployeeManagementService();
        
        $employees = $employeeService->getPFMOEmployees();
        $subDepartments = $employeeService->getSubDepartmentsWithSupervisors();
        
        return view('pfmo.manage-employees', compact('employees', 'subDepartments'));
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to load manage employees page',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
