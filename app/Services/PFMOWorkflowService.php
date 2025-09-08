<?php

namespace App\Services;

use App\Models\Department;
use App\Models\FormRequest;
use App\Models\FormApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PFMOWorkflowService
{
    /**
     * PFMO workflow statuses for enhanced process tracking
     */
    const STATUS_PENDING_PFMO_APPROVAL = 'Pending PFMO Approval';
    const STATUS_UNDER_EVALUATION = 'Under Sub-Department Evaluation';
    const STATUS_AWAITING_PFMO_DECISION = 'Awaiting PFMO Decision';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';

    /**
     * Get PFMO dashboard data
     * 
     * @return array
     */
    public static function getPFMODashboard(): array
    {
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();

        if (!$pfmoDepartment) {
            return ['error' => 'PFMO department not found'];
        }

        $today = now();
        $lastMonth = now()->subMonth();

        // Basic statistics
        $stats = [
            'total_requests' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)->count(),
            'pending_requests' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->whereIn('status', ['Pending', 'In Progress', 'Pending Target Department Approval'])
                ->count(),
            'approved_today' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'Approved')
                ->whereDate('updated_at', $today)
                ->count(),
            'under_evaluation' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', self::STATUS_UNDER_EVALUATION)
                ->count(),
        ];

        // Recent activity
        $recentRequests = FormRequest::with(['requester.employeeInfo', 'iomDetails'])
            ->where('to_department_id', $pfmoDepartment->department_id)
            ->orderBy('date_submitted', 'desc')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryBreakdown = self::getCategoryBreakdown($pfmoDepartment->department_id);

        // Performance metrics
        $performanceMetrics = self::getPerformanceMetrics($pfmoDepartment->department_id);

        return [
            'stats' => $stats,
            'recent_requests' => $recentRequests,
            'category_breakdown' => $categoryBreakdown,
            'performance_metrics' => $performanceMetrics,
            'last_updated' => now()->toISOString()
        ];
    }

    /**
     * Get PFMO recommendations for process improvements
     * 
     * @return array
     */
    public static function getPFMORecommendations(): array
    {
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();

        if (!$pfmoDepartment) {
            return [];
        }

        $recommendations = [];

        // Check for overdue requests
        $overdueRequests = FormRequest::where('to_department_id', $pfmoDepartment->department_id)
            ->whereIn('status', ['Pending', 'In Progress'])
            ->where('date_submitted', '<', now()->subDays(7))
            ->count();

        if ($overdueRequests > 0) {
            $recommendations[] = [
                'type' => 'urgent',
                'title' => 'Overdue Requests Detected',
                'message' => "You have {$overdueRequests} requests pending for more than 7 days.",
                'action' => 'Review overdue requests',
                'priority' => 'high'
            ];
        }

        // Check for rush requests
                $rushRequests = FormRequest::where('to_department_id', $pfmoDepartment->department_id)
            ->whereHas('iomDetails', function ($q) {
                $q->where('priority', 'Rush');
            })
            ->whereIn('status', ['Pending', 'In Progress'])
            ->count();

        if ($rushRequests > 0) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Rush Requests Pending',
                'message' => "You have {$rushRequests} rush priority requests requiring immediate attention.",
                'action' => 'Review rush requests',
                'priority' => 'high'
            ];
        }

        // Performance recommendations
        $avgProcessingTime = self::getAverageProcessingTime($pfmoDepartment->department_id);
        if ($avgProcessingTime > 72) { // More than 3 days
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Processing Time Alert',
                'message' => "Average processing time is {$avgProcessingTime} hours. Consider workflow optimization.",
                'action' => 'Review workflow efficiency',
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    /**
     * Categorize PFMO request based on content
     * 
     * @param string $description
     * @param string $title
     * @return array
     */
    public static function categorizePFMORequest(string $description, string $title = ''): array
    {
        // Use the RequestTypeService for categorization
        $autoAssignment = RequestTypeService::autoAssignDepartment($title, $description);

        if ($autoAssignment && $autoAssignment['department']->dept_code === 'PFMO') {
            $subDepartment = RequestTypeService::getPFMOSubDepartmentAssignment($title, $description);

            return [
                'category' => $autoAssignment['category'],
                'confidence' => $autoAssignment['confidence_score'],
                'sub_department' => $subDepartment,
                'suggested_priority' => self::suggestPriority($description, $title),
                'estimated_completion_days' => self::estimateCompletionTime($autoAssignment['category'])
            ];
        }

        return [
            'category' => 'general_maintenance',
            'confidence' => 0,
            'sub_department' => RequestTypeService::getPFMOSubDepartmentAssignment($title, $description),
            'suggested_priority' => self::suggestPriority($description, $title),
            'estimated_completion_days' => 3
        ];
    }

    /**
     * Process PFMO enhanced workflow
     * 
     * @param FormRequest $request
     * @param string $action
     * @param array $options
     * @return bool
     */
    public static function processEnhancedWorkflow(FormRequest $request, string $action, array $options = []): bool
    {
        try {
            DB::beginTransaction();

            $result = false;

            switch ($action) {
                case 'evaluate':
                    $result = self::processEvaluateAction($request, $options);
                    break;

                case 'assign_sub_department':
                    $result = self::assignToSubDepartment($request, $options);
                    break;

                case 'sub_department_feedback':
                    $result = self::processSubDepartmentFeedback($request, $options);
                    break;

                case 'final_decision':
                    $result = self::processFinalDecision($request, $options);
                    break;

                case 'create_job_order':
                    $result = self::createJobOrder($request, $options);
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown action: {$action}");
            }

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PFMO Enhanced Workflow Error: ' . $e->getMessage(), [
                'request_id' => $request->form_id,
                'action' => $action,
                'options' => $options
            ]);
            return false;
        }
    }

    /**
     * Process evaluate action - PFMO Head decides to evaluate or reject
     */
    private static function processEvaluateAction(FormRequest $request, array $options): bool
    {
        $user = auth()->user();

        // Create approval record
        FormApproval::create([
            'form_id' => $request->form_id,
            'approver_id' => $user->accnt_id,
            'action' => 'Evaluate',
            'action_date' => now(),
            'remarks' => $options['remarks'] ?? 'Request approved for evaluation',
            'approval_level' => 'PFMO_HEAD'
        ]);

        // Update request status
        $request->status = self::STATUS_UNDER_EVALUATION;
        $request->save();

        // Auto-assign to appropriate sub-department if specified
        if (isset($options['sub_department'])) {
            return self::assignToSubDepartment($request, $options);
        }

        return true;
    }

    /**
     * Assign request to sub-department
     */
    private static function assignToSubDepartment(FormRequest $request, array $options): bool
    {
        $subDeptInfo = $options['sub_department'] ??
            RequestTypeService::getPFMOSubDepartmentAssignment(
                $request->title,
                $request->iomDetails->body ?? ''
            );

        // Create assignment record
        FormApproval::create([
            'form_id' => $request->form_id,
            'approver_id' => auth()->user()->accnt_id,
            'action' => 'Assigned',
            'action_date' => now(),
            'remarks' => "Assigned to {$subDeptInfo['name']} for evaluation",
            'approval_level' => 'SUB_DEPARTMENT_ASSIGNMENT',
            'sub_department' => $subDeptInfo['sub_department'] ?? null
        ]);

        return true;
    }

    /**
     * Process sub-department feedback
     */
    private static function processSubDepartmentFeedback(FormRequest $request, array $options): bool
    {
        FormApproval::create([
            'form_id' => $request->form_id,
            'approver_id' => auth()->user()->accnt_id,
            'action' => 'Send Feedback', // Use valid ENUM value
            'action_date' => now(),
            'remarks' => $options['feedback'] ?? '',
            'approval_level' => 'SUB_DEPARTMENT_FEEDBACK',
            'estimated_completion_date' => isset($options['estimated_completion']) ?
                \Carbon\Carbon::parse($options['estimated_completion'])->format('Y-m-d') : null,
            'estimated_cost' => $options['estimated_cost'] ?? null
        ]);

        // Update status to awaiting PFMO decision
        $request->status = self::STATUS_AWAITING_PFMO_DECISION;
        $request->save();

        return true;
    }

    /**
     * Process final PFMO decision
     */
    private static function processFinalDecision(FormRequest $request, array $options): bool
    {
        $action = $options['decision'] ?? 'Approved';

        FormApproval::create([
            'form_id' => $request->form_id,
            'approver_id' => auth()->user()->accnt_id,
            'action' => $action,
            'action_date' => now(),
            'remarks' => $options['remarks'] ?? '',
            'approval_level' => 'PFMO_FINAL_DECISION'
        ]);

        $request->status = $action;
        $request->save();

        // Auto-create job order if approved
        if ($action === 'Approved' && ($options['create_job_order'] ?? true)) {
            return self::createJobOrder($request, $options);
        }

        return true;
    }

    /**
     * Create job order automatically
     */
    private static function createJobOrder(FormRequest $request, array $options): bool
    {
        // Create an actual JobOrder row and add an approval timeline record
        $request->loadMissing(['requester.employeeInfo', 'toDepartment', 'iomDetails']);

        $jobOrderNumber = 'JO-' . now()->format('Ymd') . '-' . str_pad($request->form_id, 5, '0', STR_PAD_LEFT);

        $jobOrder = new \App\Models\JobOrder();
        $jobOrder->form_id = $request->form_id;
        $jobOrder->job_order_number = $jobOrderNumber;
        $jobOrder->control_number = $options['control_number'] ?? null;
        $jobOrder->date_prepared = now();
        $jobOrder->status = 'Pending';
        $jobOrder->requestor_name = optional($request->requester?->employeeInfo)->FirstName . ' ' . optional($request->requester?->employeeInfo)->LastName;
        $jobOrder->department = $request->fromDepartment?->dept_name ?? ($request->requester?->department?->dept_name ?? '');
        $jobOrder->request_description = $request->iomDetails->body ?? $request->title;

        // Smart Auto-Assignment based on service type and workload
        $assignedTo = self::smartAutoAssignment($request);
        $jobOrder->assigned_to = $assignedTo;

        $jobOrder->created_by = auth()->id();
        $jobOrder->save();

        // Send assignment notification to technician
        if ($assignedTo) {
            \App\Services\EmailNotificationService::sendJobAssignment($jobOrder);
        }

        FormApproval::create([
            'form_id' => $request->form_id,
            'approver_id' => auth()->user()->accnt_id,
            'action' => 'Job Order Created',
            'action_date' => now(),
            'remarks' => 'Job order automatically created upon approval',
            'approval_level' => 'JOB_ORDER_CREATION',
            'job_order_number' => $jobOrderNumber
        ]);

        return (bool) $jobOrder->id;
    }

    /**
     * Smart Auto-Assignment Logic
     */
    private static function smartAutoAssignment(FormRequest $request): ?int
    {
        // Get request details for analysis
        $description = $request->iomDetails->body ?? $request->title;
        $category = self::categorizePFMORequest($description, $request->title);

        // Get available PFMO staff
        $pfmoStaff = \App\Models\User::whereHas('department', function ($q) {
            $q->where('dept_code', 'PFMO');
        })
            ->where('position', '!=', 'Head') // Exclude head from job assignments
            ->get();

        if ($pfmoStaff->isEmpty()) {
            return null; // No staff available
        }

        // Service type matching
        $serviceKeywords = [
            'electrical' => ['electrical', 'wiring', 'outlet', 'switch', 'power', 'electricity'],
            'plumbing' => ['plumbing', 'pipe', 'water', 'leak', 'faucet', 'toilet', 'drain'],
            'aircon' => ['aircon', 'air conditioning', 'ac', 'cooling', 'hvac'],
            'carpentry' => ['carpentry', 'wood', 'door', 'window', 'cabinet', 'furniture'],
            'general' => ['cleaning', 'maintenance', 'repair', 'fix', 'service']
        ];

        $bestMatch = null;
        $highestScore = 0;

        foreach ($pfmoStaff as $staff) {
            $score = 0;

            // 1. Service Type Matching (40% weight)
            foreach ($serviceKeywords as $serviceType => $keywords) {
                foreach ($keywords as $keyword) {
                    if (stripos($description, $keyword) !== false) {
                        // Check if staff has experience with this service type
                        $experience = self::getStaffServiceExperience($staff->accnt_id, $serviceType);
                        $score += $experience * 0.4;
                        break 2; // Found match, break both loops
                    }
                }
            }

            // 2. Workload Balancing (30% weight)
            $currentWorkload = \App\Models\JobOrder::where('assigned_to', $staff->accnt_id)
                ->whereIn('status', ['Pending', 'In Progress'])
                ->count();

            $workloadScore = max(0, (5 - $currentWorkload)) * 0.3; // Lower workload = higher score
            $score += $workloadScore;

            // 3. Skill Level Matching (20% weight)
            $complexityScore = self::getRequestComplexity($description);
            $staffSkillLevel = self::getStaffSkillLevel($staff->accnt_id);

            if ($complexityScore <= $staffSkillLevel) {
                $score += 0.2;
            }

            // 4. Performance History (10% weight)
            $performanceScore = self::getStaffPerformanceScore($staff->accnt_id);
            $score += $performanceScore * 0.1;

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $staff->accnt_id;
            }
        }

        return $bestMatch;
    }

    /**
     * Get staff experience with specific service type
     */
    private static function getStaffServiceExperience(int $staffId, string $serviceType): float
    {
        // Count completed jobs of this service type
        $completedJobs = \App\Models\JobOrder::where('assigned_to', $staffId)
            ->where('status', 'Completed')
            ->whereRaw('LOWER(request_description) LIKE ?', ['%' . strtolower($serviceType) . '%'])
            ->count();

        return min($completedJobs / 10, 1.0); // Max score of 1.0 for 10+ jobs
    }

    /**
     * Get request complexity level (1-5)
     */
    private static function getRequestComplexity(string $description): int
    {
        $complexKeywords = [
            5 => ['installation', 'construction', 'major repair', 'system'],
            4 => ['replacement', 'rewiring', 'renovation'],
            3 => ['repair', 'fix', 'maintenance'],
            2 => ['check', 'inspection', 'minor'],
            1 => ['cleaning', 'basic', 'simple']
        ];

        foreach ($complexKeywords as $level => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($description, $keyword) !== false) {
                    return $level;
                }
            }
        }

        return 3; // Default medium complexity
    }

    /**
     * Get staff skill level (1-5)
     */
    private static function getStaffSkillLevel(int $staffId): int
    {
        // Base skill level on completed jobs and success rate
        $completedJobs = \App\Models\JobOrder::where('assigned_to', $staffId)
            ->where('status', 'Completed')
            ->count();

        if ($completedJobs >= 50)
            return 5; // Senior
        if ($completedJobs >= 30)
            return 4; // Experienced
        if ($completedJobs >= 15)
            return 3; // Intermediate
        if ($completedJobs >= 5)
            return 2; // Junior
        return 1; // Beginner
    }

    /**
     * Get staff performance score (0-1)
     */
    private static function getStaffPerformanceScore(int $staffId): float
    {
        $completedJobs = \App\Models\JobOrder::where('assigned_to', $staffId)
            ->where('status', 'Completed')
            ->where('requestor_feedback_submitted', true)
            ->avg('requestor_satisfaction_rating');

        return $completedJobs ? ($completedJobs / 5.0) : 0.5; // Default to 0.5 if no feedback
    }

    /**
     * Get category breakdown for dashboard
     */
    private static function getCategoryBreakdown(int $departmentId): array
    {
        $requests = FormRequest::where('to_department_id', $departmentId)
            ->with('iomDetails')
            ->get();

        $categories = [];

        foreach ($requests as $request) {
            $categorization = self::categorizePFMORequest(
                $request->iomDetails->body ?? '',
                $request->title
            );

            $category = $categorization['category'];
            $categories[$category] = ($categories[$category] ?? 0) + 1;
        }

        return $categories;
    }

    /**
     * Get performance metrics
     */
    private static function getPerformanceMetrics(int $departmentId): array
    {
        $requests = FormRequest::where('to_department_id', $departmentId)
            ->where('status', 'Approved')
            ->with('approvals')
            ->limit(100)
            ->get();

        $totalTime = 0;
        $count = 0;

        foreach ($requests as $request) {
            $firstApproval = $request->approvals->where('action', 'Evaluate')->first();
            $finalApproval = $request->approvals->where('action', 'Approved')->first();

            if ($firstApproval && $finalApproval) {
                $time = $firstApproval->action_date->diffInHours($finalApproval->action_date);
                $totalTime += $time;
                $count++;
            }
        }

        return [
            'average_processing_time_hours' => $count > 0 ? round($totalTime / $count, 1) : 0,
            'total_processed' => $count,
            'efficiency_rating' => $count > 0 ? min(100, max(0, 100 - ($totalTime / $count / 24 * 10))) : 0
        ];
    }

    /**
     * Get average processing time in hours
     */
    private static function getAverageProcessingTime(int $departmentId): float
    {
        $metrics = self::getPerformanceMetrics($departmentId);
        return $metrics['average_processing_time_hours'];
    }

    /**
     * Suggest priority based on content
     */
    private static function suggestPriority(string $description, string $title): string
    {
        $urgentKeywords = ['urgent', 'emergency', 'asap', 'immediate', 'critical', 'broken', 'not working', 'failure'];
        $rushKeywords = ['rush', 'priority', 'soon', 'needed today'];

        $text = strtolower($title . ' ' . $description);

        foreach ($urgentKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return 'Urgent';
            }
        }

        foreach ($rushKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return 'Rush';
            }
        }

        return 'Routine';
    }

    /**
     * Estimate completion time based on category
     */
    private static function estimateCompletionTime(string $category): int
    {
        $estimates = [
            'facility_maintenance' => 3,
            'electrical_maintenance' => 2,
            'plumbing_maintenance' => 2,
            'aircon_maintenance' => 3,
            'general_maintenance' => 5
        ];

        return $estimates[$category] ?? 3;
    }

    /**
     * Initialize PFMO workflow for a new request
     * 
     * @param int $formId
     * @param string $requestType
     * @return void
     */
    public static function initializePFMOWorkflow(int $formId, string $requestType): void
    {
        // Get the form request
        $formRequest = FormRequest::find($formId);
        if (!$formRequest) {
            Log::warning("Form request {$formId} not found");
            return;
        }

        // For PFMO requests, the initial approver should be the department head of the requesting department
        $requestingDepartment = Department::find($formRequest->from_department_id);
        if (!$requestingDepartment) {
            Log::warning("Requesting department not found for form {$formId}");
            return;
        }

        // Find the department head of the requesting department
        $departmentHead = User::where('department_id', $requestingDepartment->department_id)
            ->where('position', 'Head')
            ->where('accessRole', 'Approver')
            ->first();

        if (!$departmentHead) {
            Log::warning("Department head not found for requesting department {$requestingDepartment->department_id} for form {$formId}");
            return;
        }

        // Set the initial approver to the department head
        $formRequest->current_approver_id = $departmentHead->accnt_id;
        $formRequest->save();

        // Create initial submission record
        FormApproval::create([
            'form_id' => $formId,
            'approver_id' => $departmentHead->accnt_id,
            'action' => 'Submitted', // Use valid ENUM value
            'comments' => 'PFMO request submitted and awaiting department head approval',
            'action_date' => now()
        ]);

        Log::info("PFMO workflow initialized for form {$formId}, assigned to department head {$departmentHead->accnt_id}");

        Log::info("PFMO workflow initialized for form {$formId}, assigned to department head {$departmentHead->accnt_id}");
    }

    /**
     * Get sub-department routing information based on request type
     * Based on PFMO Organizational Chart - 5 main sub-departments
     * 
     * @param string $requestType
     * @return array
     */
    public static function getSubDepartmentRouting(string $requestType): array
    {
        $routing = [
            // WAREHOUSE SECTION - ROY MORALES
            // Handles: Warehouse Staff, Electrician, HVAC Technician, Tape/Signage Installer, Fire & Appliance Equipment
            'Electrical Repair' => [
                'sub_department_code' => 'PFMO-WAREHOUSE',
                'department' => 'Warehouse Section',
                'supervisor' => 'ROY MORALES',
                'section' => 'Warehouse Section',
                'specialist' => 'Electrician'
            ],
            'Aircondition Repair' => [
                'sub_department_code' => 'PFMO-WAREHOUSE',
                'department' => 'Warehouse Section',
                'supervisor' => 'ROY MORALES',
                'section' => 'Warehouse Section',
                'specialist' => 'HVAC Technician'
            ],
            'Fire Equipment Maintenance' => [
                'sub_department_code' => 'PFMO-WAREHOUSE',
                'department' => 'Warehouse Section',
                'supervisor' => 'ROY MORALES',
                'section' => 'Warehouse Section',
                'specialist' => 'Fire & Appliance Equipment'
            ],
            'Signage Installation' => [
                'sub_department_code' => 'PFMO-WAREHOUSE',
                'department' => 'Warehouse Section',
                'supervisor' => 'ROY MORALES',
                'section' => 'Warehouse Section',
                'specialist' => 'Tape/Signage Installer'
            ],
            'Equipment Maintenance' => [
                'sub_department_code' => 'PFMO-WAREHOUSE',
                'department' => 'Warehouse Section',
                'supervisor' => 'ROY MORALES',
                'section' => 'Warehouse Section',
                'specialist' => 'Warehouse Staff'
            ],

            // CONSTRUCTION SECTION - ALBERT AYAP
            // Handles: Carpenter, Welder, Construction Workers, Pipe Installer, Painter, Artist
            'Carpentry Work' => [
                'sub_department_code' => 'PFMO-CONSTRUCTION',
                'department' => 'Construction Section',
                'supervisor' => 'ALBERT AYAP',
                'section' => 'Construction Section',
                'specialist' => 'Carpenter'
            ],
            'Welding Work' => [
                'sub_department_code' => 'PFMO-CONSTRUCTION',
                'department' => 'Construction Section',
                'supervisor' => 'ALBERT AYAP',
                'section' => 'Construction Section',
                'specialist' => 'Welder'
            ],
            'Construction Work' => [
                'sub_department_code' => 'PFMO-CONSTRUCTION',
                'department' => 'Construction Section',
                'supervisor' => 'ALBERT AYAP',
                'section' => 'Construction Section',
                'specialist' => 'Construction Workers'
            ],
            'Pipe Installation' => [
                'sub_department_code' => 'PFMO-CONSTRUCTION',
                'department' => 'Construction Section',
                'supervisor' => 'ALBERT AYAP',
                'section' => 'Construction Section',
                'specialist' => 'Pipe Installer'
            ],
            'Painting Work' => [
                'sub_department_code' => 'PFMO-CONSTRUCTION',
                'department' => 'Construction Section',
                'supervisor' => 'ALBERT AYAP',
                'section' => 'Construction Section',
                'specialist' => 'Painter'
            ],
            'Artistic Work' => [
                'sub_department_code' => 'PFMO-CONSTRUCTION',
                'department' => 'Construction Section',
                'supervisor' => 'ALBERT AYAP',
                'section' => 'Construction Section',
                'specialist' => 'Artist'
            ],

            // GENERAL SERVICES - ROS BALTAZAR
            // Handles: Plumber, Traffic, Monitoring & Inspection Team
            'Plumbing Repair' => [
                'sub_department_code' => 'PFMO-GENERAL',
                'department' => 'General Services',
                'supervisor' => 'ROS BALTAZAR',
                'section' => 'General Services',
                'specialist' => 'Plumber'
            ],
            'Traffic Management' => [
                'sub_department_code' => 'PFMO-GENERAL',
                'department' => 'General Services',
                'supervisor' => 'ROS BALTAZAR',
                'section' => 'General Services',
                'specialist' => 'Traffic'
            ],
            'Inspection Services' => [
                'sub_department_code' => 'PFMO-GENERAL',
                'department' => 'General Services',
                'supervisor' => 'ROS BALTAZAR',
                'section' => 'General Services',
                'specialist' => 'Monitoring & Inspection Team'
            ],
            'General Maintenance' => [
                'sub_department_code' => 'PFMO-GENERAL',
                'department' => 'General Services',
                'supervisor' => 'ROS BALTAZAR',
                'section' => 'General Services',
                'specialist' => 'General Services'
            ],

            // HOUSEKEEPING - VIRGILIO VITERBO
            // Handles: Housekeepers, Mechanics, Ground Maintenance
            'Cleaning Service' => [
                'sub_department_code' => 'PFMO-HOUSEKEEPING',
                'department' => 'Housekeeping',
                'supervisor' => 'VIRGILIO VITERBO',
                'section' => 'Housekeeping',
                'specialist' => 'Housekeepers'
            ],
            'Mechanical Repair' => [
                'sub_department_code' => 'PFMO-HOUSEKEEPING',
                'department' => 'Housekeeping',
                'supervisor' => 'VIRGILIO VITERBO',
                'section' => 'Housekeeping',
                'specialist' => 'Mechanics'
            ],
            'Grounds Maintenance' => [
                'sub_department_code' => 'PFMO-HOUSEKEEPING',
                'department' => 'Housekeeping',
                'supervisor' => 'VIRGILIO VITERBO',
                'section' => 'Housekeeping',
                'specialist' => 'Ground Maintenance'
            ],
            'Facility Booking' => [
                'sub_department_code' => 'PFMO-HOUSEKEEPING',
                'department' => 'Housekeeping',
                'supervisor' => 'VIRGILIO VITERBO',
                'section' => 'Housekeeping',
                'specialist' => 'Housekeepers'
            ],

            // TRANSPORTATION SECTION - MICHAEL DELA CRUZ
            // Handles: Head Driver, Drivers
            'Vehicle Request' => [
                'sub_department_code' => 'PFMO-TRANSPORTATION',
                'department' => 'Transportation Section',
                'supervisor' => 'MICHAEL DELA CRUZ',
                'section' => 'Transportation Section',
                'specialist' => 'Drivers'
            ],
            'Transportation Service' => [
                'sub_department_code' => 'PFMO-TRANSPORTATION',
                'department' => 'Transportation Section',
                'supervisor' => 'MICHAEL DELA CRUZ',
                'section' => 'Transportation Section',
                'specialist' => 'Head Driver'
            ],
            'Vehicle Maintenance' => [
                'sub_department_code' => 'PFMO-TRANSPORTATION',
                'department' => 'Transportation Section',
                'supervisor' => 'MICHAEL DELA CRUZ',
                'section' => 'Transportation Section',
                'specialist' => 'Drivers'
            ],

            // Legacy support for existing request types
            'Other PFMO Service' => [
                'sub_department_code' => 'PFMO-GENERAL',
                'department' => 'General Services',
                'supervisor' => 'ROS BALTAZAR',
                'section' => 'General Services',
                'specialist' => 'General Services'
            ]
        ];

        return $routing[$requestType] ?? [
            'sub_department_code' => 'PFMO-GENERAL',
            'department' => 'General Services',
            'supervisor' => 'ROS BALTAZAR',
            'section' => 'General Services',
            'specialist' => 'General Services'
        ];
    }
}
