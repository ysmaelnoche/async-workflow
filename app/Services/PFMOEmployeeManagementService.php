<?php

namespace App\Services;

use App\Models\User;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PFMOEmployeeManagementService
{
    /**
     * Get all PFMO employees with their current assignments
     */
    public function getPFMOEmployees()
    {
        $pfmoDepartment = \App\Models\Department::where('dept_code', 'PFMO')->first();
        
        if (!$pfmoDepartment) {
            throw new Exception('PFMO department not found');
        }

        return User::with(['subDepartment', 'employeeInfo'])
            ->where('department_id', $pfmoDepartment->department_id)
            // Exclude the PFMO Head from assignable employee lists. Some records
            // may have NULL for position or accessRole so we allow those.
            ->where(function($q) {
                $q->whereNull('position')->orWhere('position', '<>', 'Head');
            })
            ->where(function($q) {
                $q->whereNull('accessRole')->orWhere('accessRole', '<>', 'Head');
            })
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->accnt_id,
                    'emp_no' => $user->Emp_No,
                    'name' => $user->employeeInfo 
                        ? ($user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName)
                        : $user->username,
                    'position' => $user->position,
                    'access_role' => $user->accessRole,
                    'sub_department' => $user->subDepartment ? [
                        'id' => $user->subDepartment->id,
                        'name' => $user->subDepartment->name,
                        'code' => $user->subDepartment->subdepartment_code
                    ] : null,
                    'is_supervisor' => $this->isSupervisor($user),
                ];
            });
    }

    /**
     * Get all sub-departments with their current supervisors
     */
    public function getSubDepartmentsWithSupervisors()
    {
        return SubDepartment::with(['supervisor.employeeInfo'])
            ->get()
            ->map(function ($subDept) {
                return [
                    'id' => $subDept->id,
                    'name' => $subDept->name,
                    'code' => $subDept->subdepartment_code,
                    'description' => $subDept->description,
                    'supervisor' => $subDept->supervisor ? [
                        'id' => $subDept->supervisor->accnt_id,
                        'emp_no' => $subDept->supervisor->Emp_No,
                        'name' => $subDept->supervisor->employeeInfo 
                            ? ($subDept->supervisor->employeeInfo->FirstName . ' ' . $subDept->supervisor->employeeInfo->LastName)
                            : $subDept->supervisor->username,
                        'position' => $subDept->supervisor->position,
                    ] : null,
                    'employee_count' => $subDept->employees()->count(),
                ];
            });
    }

    /**
     * Assign employee to sub-department
     */
    public function assignEmployeeToSubDepartment($employeeId, $subDepartmentId)
    {
        return DB::transaction(function () use ($employeeId, $subDepartmentId) {
            // Validate employee exists and is PFMO
            $employee = $this->validatePFMOEmployee($employeeId);
            
            // Validate sub-department exists
            $subDepartment = SubDepartment::find($subDepartmentId);
            if (!$subDepartment) {
                throw new Exception('Sub-department not found');
            }

            // Check if already assigned to this sub-department
            if ($employee->sub_department_id == $subDepartmentId) {
                throw new Exception('Employee is already assigned to this sub-department');
            }

            // Update employee assignment
            $employee->sub_department_id = $subDepartmentId;
            $employee->save();

            Log::info('Employee assigned to sub-department', [
                'employee_id' => $employeeId,
                'employee_name' => $employee->employeeInfo->FirstName ?? $employee->username,
                'sub_department_id' => $subDepartmentId,
                'sub_department_name' => $subDepartment->name,
                'assigned_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Employee successfully assigned to sub-department',
                'employee' => $employee->load(['subDepartment', 'employeeInfo']),
            ];
        });
    }

    /**
     * Assign supervisor to sub-department
     */
    public function assignSupervisorToSubDepartment($employeeId, $subDepartmentId)
    {
        return DB::transaction(function () use ($employeeId, $subDepartmentId) {
            // Lock the sub-department row to prevent race conditions
            $subDepartment = SubDepartment::lockForUpdate()->find($subDepartmentId);
            if (!$subDepartment) {
                throw new Exception('Sub-department not found');
            }

            // Check if sub-department already has a supervisor
            if ($subDepartment->supervisor_id) {
                throw new Exception('Sub-department already has a supervisor. Use reassign or unassign first.');
            }

            // Validate employee exists and is PFMO
            $employee = $this->validatePFMOEmployee($employeeId);

            // Ensure employee is in the same sub-department or assign them
            if ($employee->sub_department_id != $subDepartmentId) {
                $employee->sub_department_id = $subDepartmentId;
                $employee->save();
            }

            // Assign as supervisor
            $subDepartment->supervisor_id = $employeeId;
            $subDepartment->save();

            Log::info('Supervisor assigned to sub-department', [
                'employee_id' => $employeeId,
                'employee_name' => $employee->employeeInfo->FirstName ?? $employee->username,
                'sub_department_id' => $subDepartmentId,
                'sub_department_name' => $subDepartment->name,
                'assigned_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Supervisor successfully assigned to sub-department',
                'sub_department' => $subDepartment->load(['supervisor.employeeInfo']),
            ];
        });
    }

    /**
     * Reassign supervisor to sub-department (replace existing)
     */
    public function reassignSupervisorToSubDepartment($employeeId, $subDepartmentId)
    {
        return DB::transaction(function () use ($employeeId, $subDepartmentId) {
            // Lock the sub-department row to prevent race conditions
            $subDepartment = SubDepartment::lockForUpdate()->find($subDepartmentId);
            if (!$subDepartment) {
                throw new Exception('Sub-department not found');
            }

            // Get old supervisor for logging
            $oldSupervisor = $subDepartment->supervisor;

            // Validate new employee exists and is PFMO
            $newEmployee = $this->validatePFMOEmployee($employeeId);

            // Ensure employee is in the same sub-department or assign them
            if ($newEmployee->sub_department_id != $subDepartmentId) {
                $newEmployee->sub_department_id = $subDepartmentId;
                $newEmployee->save();
            }

            // Replace supervisor
            $subDepartment->supervisor_id = $employeeId;
            $subDepartment->save();

            Log::info('Supervisor reassigned in sub-department', [
                'old_supervisor_id' => $oldSupervisor?->accnt_id,
                'old_supervisor_name' => $oldSupervisor?->employeeInfo?->FirstName ?? $oldSupervisor?->username,
                'new_employee_id' => $employeeId,
                'new_employee_name' => $newEmployee->employeeInfo->FirstName ?? $newEmployee->username,
                'sub_department_id' => $subDepartmentId,
                'sub_department_name' => $subDepartment->name,
                'assigned_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Supervisor successfully reassigned to sub-department',
                'sub_department' => $subDepartment->load(['supervisor.employeeInfo']),
            ];
        });
    }

    /**
     * Unassign employee from sub-department
     */
    public function unassignEmployeeFromSubDepartment($employeeId)
    {
        return DB::transaction(function () use ($employeeId) {
            $employee = $this->validatePFMOEmployee($employeeId);

            if (!$employee->sub_department_id) {
                throw new Exception('Employee is not assigned to any sub-department');
            }

            $subDepartmentName = $employee->subDepartment->name ?? 'Unknown';

            // Check if employee is a supervisor and remove supervisor assignment
            $supervisedDepartment = SubDepartment::where('supervisor_id', $employeeId)->first();
            if ($supervisedDepartment) {
                $supervisedDepartment->supervisor_id = null;
                $supervisedDepartment->save();
            }

            // Remove sub-department assignment
            $employee->sub_department_id = null;
            $employee->save();

            Log::info('Employee unassigned from sub-department', [
                'employee_id' => $employeeId,
                'employee_name' => $employee->employeeInfo->FirstName ?? $employee->username,
                'sub_department_name' => $subDepartmentName,
                'was_supervisor' => $supervisedDepartment ? true : false,
                'unassigned_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Employee successfully unassigned from sub-department',
                'employee' => $employee->fresh(['subDepartment', 'employeeInfo']),
            ];
        });
    }

    /**
     * Unassign supervisor from sub-department
     */
    public function unassignSupervisorFromSubDepartment($subDepartmentId)
    {
        return DB::transaction(function () use ($subDepartmentId) {
            $subDepartment = SubDepartment::lockForUpdate()->find($subDepartmentId);
            if (!$subDepartment) {
                throw new Exception('Sub-department not found');
            }

            if (!$subDepartment->supervisor_id) {
                throw new Exception('Sub-department does not have a supervisor assigned');
            }

            $supervisorName = $subDepartment->supervisor?->employeeInfo?->FirstName ?? $subDepartment->supervisor?->username ?? 'Unknown';

            // Remove supervisor assignment
            $subDepartment->supervisor_id = null;
            $subDepartment->save();

            Log::info('Supervisor unassigned from sub-department', [
                'supervisor_name' => $supervisorName,
                'sub_department_id' => $subDepartmentId,
                'sub_department_name' => $subDepartment->name,
                'unassigned_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Supervisor successfully unassigned from sub-department',
                'sub_department' => $subDepartment->fresh(['supervisor.employeeInfo']),
            ];
        });
    }

    /**
     * Check if user is a supervisor
     */
    private function isSupervisor(User $user): bool
    {
        return SubDepartment::where('supervisor_id', $user->accnt_id)->exists();
    }

    /**
     * Validate that employee exists and is PFMO employee
     */
    private function validatePFMOEmployee($employeeId): User
    {
        $employee = User::with(['employeeInfo', 'department'])->find($employeeId);
        
        if (!$employee) {
            throw new Exception('Employee not found');
        }

        if (!$employee->department || $employee->department->dept_code !== 'PFMO') {
            throw new Exception('Employee is not a PFMO staff member');
        }

        return $employee;
    }
}
