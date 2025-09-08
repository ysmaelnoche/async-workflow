<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use App\Models\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkflowPreviewService
{
    /**
     * Generate comprehensive workflow preview for a request before submission
     * 
     * @param array $requestData
     * @return array
     */
    public static function generateWorkflowPreview(array $requestData): array
    {
        $user = Auth::user();
        $formType = $requestData['form_type'] ?? '';

        // For testing without authentication
        if (!$user) {
            $user = (object) [
                'accnt_id' => 'test_user',
                'position' => 'Staff',
                'department_id' => 1,
                'department' => (object) ['dept_name' => 'Test Department'],
                'employeeInfo' => (object) ['FirstName' => 'Test', 'LastName' => 'User']
            ];
        }

        try {
            if ($formType === 'IOM') {
                return self::generateIOMWorkflowPreview($requestData, $user);
            } elseif ($formType === 'Leave') {
                return self::generateLeaveWorkflowPreview($requestData, $user);
            }

            return [
                'success' => false,
                'message' => 'Unsupported form type for workflow preview'
            ];

        } catch (\Exception $e) {
            Log::error('Workflow preview generation failed', [
                'error' => $e->getMessage(),
                'request_data' => $requestData,
                'user_id' => $user->accnt_id ?? 'unknown'
            ]);

            return [
                'success' => false,
                'message' => 'Unable to generate workflow preview. Please try again.'
            ];
        }
    }

    /**
     * Generate IOM workflow preview
     */
    private static function generateIOMWorkflowPreview(array $requestData, $user): array
    {
        $steps = [];
        $estimatedDays = 0;
        $targetDepartmentId = $requestData['to_department_id'] ?? null;

        // Convert string to integer if needed
        if ($targetDepartmentId && is_string($targetDepartmentId)) {
            $targetDepartmentId = intval($targetDepartmentId);
        }

        if (!$targetDepartmentId || $targetDepartmentId <= 0) {
            return [
                'success' => false,
                'message' => 'Target department is required for workflow preview'
            ];
        }

        try {
            $targetDepartment = Department::find($targetDepartmentId);
        } catch (\Exception $e) {
            Log::warning('Department lookup failed', [
                'department_id' => $targetDepartmentId,
                'error' => $e->getMessage()
            ]);
            $targetDepartment = null;
        }

        if (!$targetDepartment) {
            // Create a fallback department object
            $targetDepartment = (object) [
                'department_id' => $targetDepartmentId,
                'dept_name' => 'Selected Department',
                'dept_code' => 'DEPT'
            ];
        }

        $userDepartment = $user->department ?? (object) ['dept_name' => 'Your Department'];
        $isVPAAUser = ($user->position ?? '') === 'VPAA';
        $isDepartmentHead = ($user->position ?? '') === 'Head';

        // Step 1: Request Submission
        $steps[] = [
            'step_number' => 1,
            'title' => 'Request Submission',
            'description' => 'You submit the IOM request',
            'actor' => ($user->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($user->employeeInfo->LastName ?? 'User'),
            'actor_position' => $user->position ?? 'Staff',
            'department' => $userDepartment->dept_name ?? 'Unknown',
            'action' => 'Submit',
            'estimated_duration' => 'Immediate',
            'status' => 'current',
            'icon' => 'document-plus'
        ];

        // Determine workflow path based on user role
        if ($isDepartmentHead || $isVPAAUser) {
            // Department Head or VPAA - direct routing
            if ($targetDepartment->department_id === $user->department_id) {
                // Same department - auto-approved
                $steps[] = [
                    'step_number' => 2,
                    'title' => 'Auto-Approval',
                    'description' => 'Request is automatically approved (same department)',
                    'actor' => ($user->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($user->employeeInfo->LastName ?? 'User'),
                    'actor_position' => $user->position ?? 'Head',
                    'department' => $userDepartment->dept_name ?? 'Unknown',
                    'action' => 'Auto-Approve',
                    'estimated_duration' => 'Immediate',
                    'status' => 'pending',
                    'icon' => 'check-circle'
                ];
                $estimatedDays = 0;
            } else {
                // Different department - route to target
                $steps[] = [
                    'step_number' => 2,
                    'title' => 'Department Head Approval',
                    'description' => 'Your approval as department head',
                    'actor' => ($user->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($user->employeeInfo->LastName ?? 'User'),
                    'actor_position' => $user->position ?? 'Head',
                    'department' => $userDepartment->dept_name ?? 'Unknown',
                    'action' => 'Approve & Route',
                    'estimated_duration' => 'Immediate',
                    'status' => 'pending',
                    'icon' => 'check-circle'
                ];

                // Add target department approval
                $targetHead = self::findDepartmentHead($targetDepartment);
                if ($targetHead) {
                    $steps[] = [
                        'step_number' => 3,
                        'title' => 'Target Department Approval',
                        'description' => "Approval from {$targetDepartment->dept_name}",
                        'actor' => ($targetHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($targetHead->employeeInfo->LastName ?? 'Head'),
                        'actor_position' => $targetHead->position ?? 'Head',
                        'department' => $targetDepartment->dept_name,
                        'action' => 'Review & Approve',
                        'estimated_duration' => '1-2 business days',
                        'status' => 'pending',
                        'icon' => 'check-circle'
                    ];
                    $estimatedDays += 2;
                }
            }
        } else {
            // Regular staff - must go through department head first
            $departmentHead = self::findDepartmentHead($userDepartment);

            if ($departmentHead) {
                $steps[] = [
                    'step_number' => 2,
                    'title' => 'Department Head Review',
                    'description' => "Approval from your department head",
                    'actor' => ($departmentHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($departmentHead->employeeInfo->LastName ?? 'Head'),
                    'actor_position' => $departmentHead->position ?? 'Head',
                    'department' => $userDepartment->dept_name ?? 'Unknown',
                    'action' => 'Review & Approve',
                    'estimated_duration' => '1-2 business days',
                    'status' => 'pending',
                    'icon' => 'user-check'
                ];
                $estimatedDays += 2;

                // If target department is different, add target department step
                if ($targetDepartment->department_id !== $user->department_id) {
                    $targetHead = self::findDepartmentHead($targetDepartment);
                    if ($targetHead) {
                        $steps[] = [
                            'step_number' => 3,
                            'title' => 'Target Department Approval',
                            'description' => "Final approval from {$targetDepartment->dept_name}",
                            'actor' => ($targetHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($targetHead->employeeInfo->LastName ?? 'Head'),
                            'actor_position' => $targetHead->position ?? 'Head',
                            'department' => $targetDepartment->dept_name,
                            'action' => 'Review & Approve',
                            'estimated_duration' => '1-2 business days',
                            'status' => 'pending',
                            'icon' => 'check-circle'
                        ];
                        $estimatedDays += 2;
                    }
                }
            }
        }

        // Special PFMO workflow if target is PFMO
        if ($targetDepartment->dept_code === 'PFMO') {
            $steps = self::addPFMOWorkflowSteps($steps, $requestData);
            $estimatedDays += 5; // Additional time for PFMO evaluation process
        }

        // Final completion step
        $steps[] = [
            'step_number' => count($steps) + 1,
            'title' => 'Request Completed',
            'description' => 'Request has been fully processed and approved',
            'actor' => 'System',
            'actor_position' => 'Automated',
            'department' => 'System',
            'action' => 'Complete',
            'estimated_duration' => 'Immediate',
            'status' => 'pending',
            'icon' => 'check-badge'
        ];

        return [
            'success' => true,
            'form_type' => 'IOM',
            'target_department' => $targetDepartment->dept_name,
            'total_steps' => count($steps),
            'estimated_completion_days' => $estimatedDays,
            'workflow_steps' => $steps,
            'requires_job_order' => $targetDepartment->dept_code === 'PFMO',
            'preview_generated_at' => now()->toISOString()
        ];
    }

    /**
     * Generate Leave workflow preview
     */
    private static function generateLeaveWorkflowPreview(array $requestData, $user): array
    {
        $steps = [];
        $estimatedDays = 0;

        $userDepartment = $user->department ?? (object) ['dept_name' => 'Your Department'];
        $isDepartmentHead = ($user->position ?? '') === 'Head';

        // Step 1: Request Submission
        $steps[] = [
            'step_number' => 1,
            'title' => 'Leave Request Submission',
            'description' => 'You submit the leave request',
            'actor' => ($user->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($user->employeeInfo->LastName ?? 'User'),
            'actor_position' => $user->position ?? 'Staff',
            'department' => $userDepartment->dept_name ?? 'Unknown',
            'action' => 'Submit',
            'estimated_duration' => 'Immediate',
            'status' => 'current',
            'icon' => 'calendar-plus'
        ];

        if ($isDepartmentHead) {
            // Department Head leave - goes to VPAA
            $vpaaUser = self::findVPAAUser();
            if ($vpaaUser) {
                $steps[] = [
                    'step_number' => 2,
                    'title' => 'VPAA Review',
                    'description' => 'Review and approval by VPAA',
                    'actor' => ($vpaaUser->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($vpaaUser->employeeInfo->LastName ?? 'VPAA'),
                    'actor_position' => 'VPAA',
                    'department' => 'Vice President for Academic Affairs',
                    'action' => 'Review & Approve',
                    'estimated_duration' => '2-3 business days',
                    'status' => 'pending',
                    'icon' => 'user-check'
                ];
                $estimatedDays += 3;

                // HR Final Processing
                $hrHead = self::findHRHead();
                if ($hrHead) {
                    $steps[] = [
                        'step_number' => 3,
                        'title' => 'HR Processing',
                        'description' => 'Final processing by Human Resources',
                        'actor' => ($hrHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($hrHead->employeeInfo->LastName ?? 'Head'),
                        'actor_position' => $hrHead->position ?? 'Head',
                        'department' => 'Human Resources',
                        'action' => 'Process & Approve',
                        'estimated_duration' => '1-2 business days',
                        'status' => 'pending',
                        'icon' => 'document-check'
                    ];
                    $estimatedDays += 2;
                }
            }
        } else {
            // Regular staff leave - goes to department head
            $departmentHead = self::findDepartmentHead($userDepartment);
            if ($departmentHead) {
                $steps[] = [
                    'step_number' => 2,
                    'title' => 'Department Head Approval',
                    'description' => 'Approval from your department head',
                    'actor' => ($departmentHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($departmentHead->employeeInfo->LastName ?? 'Head'),
                    'actor_position' => $departmentHead->position ?? 'Head',
                    'department' => $userDepartment->dept_name ?? 'Unknown',
                    'action' => 'Review & Approve',
                    'estimated_duration' => '1-2 business days',
                    'status' => 'pending',
                    'icon' => 'user-check'
                ];
                $estimatedDays += 2;
            }
        }

        // Final completion step
        $steps[] = [
            'step_number' => count($steps) + 1,
            'title' => 'Leave Approved',
            'description' => 'Leave request has been fully approved',
            'actor' => 'System',
            'actor_position' => 'Automated',
            'department' => 'System',
            'action' => 'Complete',
            'estimated_duration' => 'Immediate',
            'status' => 'pending',
            'icon' => 'check-badge'
        ];

        return [
            'success' => true,
            'form_type' => 'Leave',
            'leave_type' => ucfirst($requestData['leave_type'] ?? 'Leave'),
            'total_steps' => count($steps),
            'estimated_completion_days' => $estimatedDays,
            'workflow_steps' => $steps,
            'requires_job_order' => false,
            'preview_generated_at' => now()->toISOString()
        ];
    }

    /**
     * Add PFMO-specific workflow steps
     */
    private static function addPFMOWorkflowSteps(array $steps, array $requestData): array
    {
        $pfmoHead = self::findPFMOHead();

        if ($pfmoHead) {
            // PFMO Head Evaluation
            $steps[] = [
                'step_number' => count($steps) + 1,
                'title' => 'PFMO Head Evaluation',
                'description' => 'PFMO Head reviews and decides to evaluate or reject',
                'actor' => ($pfmoHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($pfmoHead->employeeInfo->LastName ?? 'Head'),
                'actor_position' => 'PFMO Head',
                'department' => 'Plant Facilities & Maintenance Office',
                'action' => 'Evaluate or Reject',
                'estimated_duration' => '1-2 business days',
                'status' => 'pending',
                'icon' => 'clipboard-document-check'
            ];

            // Sub-Department Assignment (if evaluated)
            $subDeptInfo = null;
            try {
                if (class_exists('\App\Services\PFMOWorkflowService')) {
                    $subDeptInfo = \App\Services\PFMOWorkflowService::categorizePFMORequest(
                        $requestData['description'] ?? '',
                        $requestData['title'] ?? ''
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Failed to categorize PFMO request', [
                    'error' => $e->getMessage(),
                    'description' => $requestData['description'] ?? '',
                    'title' => $requestData['title'] ?? ''
                ]);
            }

            $steps[] = [
                'step_number' => count($steps) + 1,
                'title' => 'Sub-Department Assignment',
                'description' => "Automatically assigned to " . (
                    $subDeptInfo && isset($subDeptInfo['sub_department']['name'])
                    ? $subDeptInfo['sub_department']['name']
                    : 'appropriate sub-department'
                ),
                'actor' => 'System',
                'actor_position' => 'Automated',
                'department' => 'PFMO Sub-Department',
                'action' => 'Auto-Assign',
                'estimated_duration' => 'Immediate',
                'status' => 'pending',
                'icon' => 'arrow-path'
            ];

            // Sub-Department Feedback
            $steps[] = [
                'step_number' => count($steps) + 1,
                'title' => 'Sub-Department Evaluation',
                'description' => 'Technical evaluation and cost estimation',
                'actor' => 'Sub-Department Staff',
                'actor_position' => 'Technical Staff',
                'department' => 'PFMO Sub-Department',
                'action' => 'Evaluate & Provide Feedback',
                'estimated_duration' => '2-3 business days',
                'status' => 'pending',
                'icon' => 'wrench-screwdriver'
            ];

            // PFMO Head Final Decision
            $steps[] = [
                'step_number' => count($steps) + 1,
                'title' => 'PFMO Final Decision',
                'description' => 'PFMO Head makes final approval decision',
                'actor' => ($pfmoHead->employeeInfo->FirstName ?? 'Unknown') . ' ' . ($pfmoHead->employeeInfo->LastName ?? 'Head'),
                'actor_position' => 'PFMO Head',
                'department' => 'Plant Facilities & Maintenance Office',
                'action' => 'Final Approval',
                'estimated_duration' => '1 business day',
                'status' => 'pending',
                'icon' => 'check-circle'
            ];

            // Job Order Creation
            $steps[] = [
                'step_number' => count($steps) + 1,
                'title' => 'Job Order Creation',
                'description' => 'System automatically creates job order for PFMO execution',
                'actor' => 'System',
                'actor_position' => 'Automated',
                'department' => 'Plant Facilities & Maintenance Office',
                'action' => 'Create Job Order',
                'estimated_duration' => 'Immediate',
                'status' => 'pending',
                'icon' => 'document-plus'
            ];
        }

        return $steps;
    }

    /**
     * Helper methods to find specific users
     */
    private static function findDepartmentHead($department)
    {
        if (!$department)
            return null;

        try {
            return User::with('employeeInfo')
                ->where('department_id', $department->department_id)
                ->where('position', 'Head')
                ->where('accessRole', 'Approver')
                ->first();
        } catch (\Exception $e) {
            Log::warning('Failed to find department head', [
                'department_id' => $department->department_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private static function findVPAAUser()
    {
        try {
            return User::with('employeeInfo')
                ->where('position', 'VPAA')
                ->where('accessRole', 'Approver')
                ->first();
        } catch (\Exception $e) {
            Log::warning('Failed to find VPAA user', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private static function findHRHead()
    {
        try {
            $hrDepartment = Department::where('dept_code', 'HR')
                ->orWhere('dept_code', 'HRD')
                ->orWhere('dept_code', 'HRMD')
                ->orWhere('dept_name', 'like', '%Human Resource%')
                ->first();

            if (!$hrDepartment)
                return null;

            return User::with('employeeInfo')
                ->where('department_id', $hrDepartment->department_id)
                ->where('position', 'Head')
                ->where('accessRole', 'Approver')
                ->first();
        } catch (\Exception $e) {
            Log::warning('Failed to find HR head', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private static function findPFMOHead()
    {
        try {
            $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();

            if (!$pfmoDepartment)
                return null;

            return User::with('employeeInfo')
                ->where('department_id', $pfmoDepartment->department_id)
                ->where('position', 'Head')
                ->where('accessRole', 'Approver')
                ->first();
        } catch (\Exception $e) {
            Log::warning('Failed to find PFMO head', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function generateWorkflowPreviewLegacy(string $formType, int $fromDepartmentId, int $toDepartmentId, string $requestContent = null): array
    {
        $requestData = [
            'form_type' => $formType,
            'from_department_id' => $fromDepartmentId,
            'to_department_id' => $toDepartmentId,
            'description' => $requestContent
        ];

        return self::generateWorkflowPreview($requestData);
    }
}
