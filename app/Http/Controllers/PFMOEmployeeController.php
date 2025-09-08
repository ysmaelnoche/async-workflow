<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SubDepartment;
use App\Services\PFMOEmployeeManagementService;
use App\Http\Requests\AssignEmployeeRequest;
use App\Http\Requests\AssignSupervisorRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PFMOEmployeeController extends Controller
{
    protected $employeeManagementService;

    public function __construct(PFMOEmployeeManagementService $employeeManagementService)
    {
        $this->employeeManagementService = $employeeManagementService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Check if user has access to PFMO
        if (!$user->can('access-pfmo')) {
            abort(403, 'Unauthorized access to PFMO employee management.');
        }

        // Get all PFMO employees with their sub-department assignments
        $employees = User::whereHas('department', function($query) {
            $query->where('dept_code', 'PFMO');
        })->with(['subDepartment', 'department'])->get();

        // Transform employees data for the view
        $employeesData = $employees->map(function($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->fname . ' ' . $employee->lname,
                'position' => $employee->position,
                'email' => $employee->email,
                'sub_department' => $employee->subDepartment ? [
                    'id' => $employee->subDepartment->id,
                    'name' => $employee->subDepartment->sub_dept_name
                ] : null,
                'is_supervisor' => $employee->subDepartment && $employee->subDepartment->supervisor_id === $employee->id
            ];
        });

        // Get all sub-departments with their current supervisors and employee counts
        $subDepartments = SubDepartment::with(['supervisor', 'employees'])->get();
        
        // Transform sub-departments data for the view
        $subDepartmentsData = $subDepartments->map(function($subDept) {
            return [
                'id' => $subDept->id,
                'name' => $subDept->sub_dept_name,
                'supervisor' => $subDept->supervisor ? [
                    'id' => $subDept->supervisor->id,
                    'name' => $subDept->supervisor->fname . ' ' . $subDept->supervisor->lname
                ] : null,
                'employee_count' => $subDept->employees->count()
            ];
        });

        // Get unassigned PFMO employees for assignment dropdowns
        $unassignedEmployees = User::whereHas('department', function($query) {
            $query->where('dept_code', 'PFMO');
        })->whereNull('sub_department_id')->get();

        // Get PFMO employees who can be supervisors (excluding current supervisors)
        $availableSupervisors = User::whereHas('department', function($query) {
            $query->where('dept_code', 'PFMO');
        })->whereNotIn('id', SubDepartment::whereNotNull('supervisor_id')->pluck('supervisor_id'))
        ->get();

        return view('pfmo.manage-employees', compact(
            'employeesData',
            'subDepartmentsData', 
            'unassignedEmployees',
            'availableSupervisors'
        ));
    }

    public function assignEmployee(AssignEmployeeRequest $request)
    {
        try {
            $result = $this->employeeManagementService->assignEmployeeToSubDepartment(
                $request->employee_id,
                $request->sub_department_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error assigning employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning the employee.'
            ], 500);
        }
    }

    public function assignSupervisor(AssignSupervisorRequest $request)
    {
        try {
            $result = $this->employeeManagementService->assignSupervisorToSubDepartment(
                $request->employee_id,
                $request->sub_department_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error assigning supervisor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning the supervisor.'
            ], 500);
        }
    }

    public function removeSupervisor(Request $request)
    {
        $request->validate([
            'sub_department_id' => 'required|exists:sub_departments,id'
        ]);

        try {
            $result = $this->employeeManagementService->removeSupervisorFromSubDepartment(
                $request->sub_department_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error removing supervisor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the supervisor.'
            ], 500);
        }
    }

    public function unassignEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:tb_account,id'
        ]);

        try {
            $result = $this->employeeManagementService->unassignEmployeeFromSubDepartment(
                $request->employee_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error unassigning employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unassigning the employee.'
            ], 500);
        }
    }

    public function unassignSupervisor(Request $request)
    {
        $request->validate([
            'sub_department_id' => 'required|exists:sub_departments,id'
        ]);

        try {
            $result = $this->employeeManagementService->removeSupervisorFromSubDepartment(
                $request->sub_department_id
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error unassigning supervisor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unassigning the supervisor.'
            ], 500);
        }
    }
}
