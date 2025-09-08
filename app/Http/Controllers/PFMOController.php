<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PFMOWorkflowService;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\User;
use App\Models\FormRequest;
use App\Models\FormApproval;
use Illuminate\Support\Facades\Auth;

class PFMOController extends Controller
{
    /**
     * Display PFMO dashboard
     */
    public function dashboard()
    {
<<<<<<< HEAD
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();
        
        if (!$pfmoDepartment) {
            return view('pfmo.dashboard', [
                'dashboard' => ['error' => 'PFMO department not found'],
                'recommendations' => [],
                'feedbackData' => []
            ]);
        }

        // Enhanced dashboard statistics
        $stats = [
            'total_requests' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)->count(),
            'pending_requests' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->whereIn('status', ['Pending', 'Pending Department Head Approval', 'Pending Target Department Approval'])
                ->count(),
            'in_progress' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'In Progress')
                ->count(),
            'under_evaluation' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'Under Sub-Department Evaluation')
                ->count(),
            'awaiting_decision' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'Awaiting PFMO Decision')
                ->count(),
            'approved_today' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'Approved')
                ->whereDate('updated_at', now())
                ->count(),
            'rejected_today' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'Rejected')
                ->whereDate('updated_at', now())
                ->count(),
        ];

        // Recent requests with proper relationships
        $recentRequests = FormRequest::with(['requester.employeeInfo', 'requester.department', 'iomDetails'])
            ->where('to_department_id', $pfmoDepartment->department_id)
            ->orderBy('date_submitted', 'desc')
            ->limit(10)
            ->get();

                    // Get sub-departments with supervisors for management
            $subDepartments = SubDepartment::with(['supervisor.employeeInfo'])->get();

        // Add category breakdown for dashboard
        $categoryBreakdown = FormRequest::where('to_department_id', $pfmoDepartment->department_id)
            ->selectRaw('form_type, COUNT(*) as count')
            ->groupBy('form_type')
            ->pluck('count', 'form_type')
            ->toArray();

        // Add performance metrics
        $performanceMetrics = [
            'average_processing_time_hours' => 24, // Mock data - replace with actual calculation
            'total_processed' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)->count(),
            'efficiency_rating' => 85 // Mock data - replace with actual calculation
        ];

        $dashboard = [
            'stats' => $stats,
            'recent_requests' => $recentRequests,
            'sub_departments' => $subDepartments,
            'category_breakdown' => $categoryBreakdown,
            'performance_metrics' => $performanceMetrics,
            'last_updated' => now()->toISOString()
        ];
        
        $recommendations = PFMOWorkflowService::getPFMORecommendations();
        
        // Get feedback data for dashboard
        $feedbackData = \App\Services\PFMOFeedbackService::getDashboardSummary();
        $averageRating = \App\Services\PFMOFeedbackService::getAverageRating();
        $ratingDistribution = \App\Services\PFMOFeedbackService::getRatingDistribution();
        
        // Add missing variables for clean dashboard
        $totalRequests = FormRequest::count();
        $pendingRequests = FormRequest::whereIn('status', ['Pending', 'In Progress', 'Under Sub-Department Evaluation'])->count();
        $approvedRequests = FormRequest::where('status', 'Approved')->count(); // 0 for now
        $rejectedRequests = FormRequest::where('status', 'Rejected')->count();
        $todayApproved = FormRequest::where('status', 'Approved')->whereDate('updated_at', now())->count(); // 0 for now
        $departmentCount = SubDepartment::count();
        $inProgressRequests = FormRequest::where('status', 'In Progress')->count();
        $feedbackCount = 48; // This would come from actual feedback table
        
        // Variables for original dashboard template
        $monthlyCount = FormRequest::whereMonth('created_at', now()->month)->count();
        $yearlyCount = FormRequest::whereYear('created_at', now()->year)->count();
        $avgProcessingTime = 'N/A'; // Calculate actual processing time
        $approvalRate = $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100) : 0;
        $isPFMOUser = true; // This is PFMO dashboard
        $averageRating = 4.2; // Mock feedback rating
        $totalFeedback = 48; // Mock feedback count
        $recentFeedback = collect([]); // Empty for now, can be populated with actual feedback
        $activeTab = request('tab', 'all'); // Default tab
        
        // Get recent requests for the table with pagination
        $requests = FormRequest::with(['requester.employeeInfo', 'requester.department'])
            ->orderBy('date_submitted', 'desc')
            ->paginate(10);
            
        // Add counts for tab navigation with correct status values
        $counts = [
            'all' => FormRequest::count(),
            'pending' => FormRequest::whereIn('status', ['Pending', 'In Progress', 'Under Sub-Department Evaluation'])->count(),
            'completed' => 0, // No completed status found in current data
            'rejected' => FormRequest::where('status', 'Rejected')->count(),
        ];
            
        $departmentName = 'Physical Facilities Management Office';
        $position = 'Head';
        
        return view('pfmo.dashboard', compact(
            'dashboard',
            'recommendations',
            'feedbackData',
            'averageRating',
            'ratingDistribution',
            'totalRequests',
            'pendingRequests', 
            'approvedRequests',
            'rejectedRequests',
            'todayApproved',
            'departmentCount',
            'subDepartments',
            'inProgressRequests',
            'feedbackCount',
            'monthlyCount',
            'yearlyCount',
            'avgProcessingTime',
            'approvalRate',
            'isPFMOUser',
            'totalFeedback',
            'recentFeedback',
            'activeTab',
            'requests',
            'counts',
            'departmentName',
            'position'
        ));
    }

    /**
     * Show PFMO facility requests
     */
    public function facilityRequests(Request $request)
    {
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();
        
        if (!$pfmoDepartment) {
            return redirect()->back()->with('error', 'PFMO department not found');
        }

        $query = FormRequest::with(['requester.employeeInfo', 'requester.department', 'iomDetails', 'approvals'])
            ->where('to_department_id', $pfmoDepartment->department_id);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->whereHas('iomDetails', function($q) use ($request) {
                $q->where('priority', $request->priority);
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_submitted', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_submitted', '<=', $request->date_to);
        }

        $requests = $query->orderBy('date_submitted', 'desc')
            ->paginate(20)
            ->appends($request->all());

        $stats = [
            'total' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)->count(),
            'pending' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->whereIn('status', ['Pending', 'In Progress', 'Pending Target Department Approval'])->count(),
            'approved' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('status', 'Approved')->count(),
            'rush' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->whereHas('iomDetails', function($q) {
                    $q->where('priority', 'Rush');
                })->count()
        ];

        return view('pfmo.facility-requests', compact('requests', 'stats'));
    }

    /**
     * Show single facility request details
     */
    public function showRequest($id)
    {
        $request = FormRequest::with([
            'requester.employeeInfo', 
            'requester.department', 
            'iomDetails', 
            'approvals.approver.employeeInfo',
            'department'
        ])->findOrFail($id);

        // Check if user has permission to view this request
        $user = Auth::user();
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();
        
        if (!$pfmoDepartment || $user->department_id !== $pfmoDepartment->department_id) {
            if ($user->accessRole !== 'Admin' && $request->current_approver_id !== $user->accnt_id) {
                return redirect()->back()->with('error', 'Access denied');
            }
        }

        // Get categorization suggestions
        $categorySuggestions = PFMOWorkflowService::categorizePFMORequest(
            $request->iomDetails->description ?? '',
            $request->title
        );

        return view('pfmo.request-details', compact('request', 'categorySuggestions'));
    }

    /**
     * Process PFMO approval
     */
    public function processApproval(Request $request, $requestId)
    {
        $request->validate([
            'action' => 'required|in:Approved,Denied',
            'remarks' => 'nullable|string|max:1000',
            'signature_style' => 'nullable|string'
        ]);

        $formRequest = FormRequest::findOrFail($requestId);
        $user = Auth::user();

        // Verify user can approve this request
        if ($formRequest->current_approver_id !== $user->accnt_id && $user->accessRole !== 'Admin') {
            return redirect()->back()->with('error', 'You are not authorized to approve this request');
        }

        try {
            \DB::beginTransaction();

            // Create approval record
            $approval = new FormApproval();
            $approval->form_id = $formRequest->form_id;
            $approval->approver_id = $user->accnt_id;
            $approval->action = $request->action;
            $approval->action_date = now();
            $approval->remarks = $request->remarks;
            $approval->approval_level = 'PFMO';
            $approval->signature_style = $request->signature_style;
            $approval->save();

            // Update request status
            $formRequest->status = $request->action;
            if ($request->action === 'Approved') {
                $formRequest->date_approved = now();
            }
            $formRequest->save();

            // Clear approval caches
            app(\App\Services\ApprovalCacheService::class)->clearAllApprovalCaches();

            \DB::commit();

            $message = $request->action === 'Approved' ? 
                'Request approved successfully' : 
                'Request denied successfully';

            return redirect()->route('pfmo.facility-requests')->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('PFMO approval processing failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process approval');
        }
    }

    /**
     * PFMO performance metrics
     */
    public function metrics()
    {
        $pfmoDepartment = Department::where('dept_code', 'PFMO')->first();
        
        if (!$pfmoDepartment) {
            return redirect()->back()->with('error', 'PFMO department not found');
        }

        // Last 30 days metrics
        $last30Days = now()->subDays(30);
        
        $metrics = [
            'requests_received' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('date_submitted', '>=', $last30Days)->count(),
            'requests_processed' => FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->where('date_submitted', '>=', $last30Days)
                ->where('status', 'Approved')->count(),
            'avg_processing_time' => 0,
            'by_category' => [],
            'monthly_trend' => []
        ];

        // Calculate average processing time
        $processedRequests = FormRequest::where('to_department_id', $pfmoDepartment->department_id)
            ->where('status', 'Approved')
            ->where('date_submitted', '>=', $last30Days)
            ->with('approvals')
            ->get();

        if ($processedRequests->count() > 0) {
            $totalHours = $processedRequests->sum(function($request) {
                $approval = $request->approvals->where('action', 'Approved')->first();
                return $approval ? $request->date_submitted->diffInHours($approval->action_date) : 0;
            });
            $metrics['avg_processing_time'] = round($totalHours / $processedRequests->count(), 1);
        }

        // Monthly trend (last 6 months)
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $monthlyCount = FormRequest::where('to_department_id', $pfmoDepartment->department_id)
                ->whereBetween('date_submitted', [$monthStart, $monthEnd])
                ->count();
                
            $metrics['monthly_trend'][] = [
                'month' => $monthStart->format('M Y'),
                'count' => $monthlyCount
            ];
        }

        return view('pfmo.metrics', compact('metrics'));
    }

    /**
     * Bulk action for multiple requests
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:tb_form_request,form_id',
            'action' => 'required|in:approve,deny,assign',
            'remarks' => 'nullable|string|max:1000',
            'assignee_id' => 'required_if:action,assign|exists:tb_account,accnt_id'
        ]);

        $user = Auth::user();
        $successCount = 0;
        $errors = [];

        try {
            \DB::beginTransaction();

            foreach ($request->request_ids as $requestId) {
                $formRequest = FormRequest::find($requestId);
                
                if (!$formRequest) {
                    continue;
                }

                switch ($request->action) {
                    case 'approve':
                    case 'deny':
                        if ($formRequest->current_approver_id === $user->accnt_id || $user->accessRole === 'Admin') {
                            $approval = new FormApproval();
                            $approval->form_id = $formRequest->form_id;
                            $approval->approver_id = $user->accnt_id;
                            $approval->action = $request->action === 'approve' ? 'Approved' : 'Denied';
                            $approval->action_date = now();
                            $approval->remarks = $request->remarks;
                            $approval->approval_level = 'PFMO';
                            $approval->save();

                            $formRequest->status = $approval->action;
                            if ($approval->action === 'Approved') {
                                $formRequest->date_approved = now();
                            }
                            $formRequest->save();
                            
                            $successCount++;
                        }
                        break;
                        
                    case 'assign':
                        if ($user->accessRole === 'Admin' || $user->position === 'Head') {
                            $formRequest->current_approver_id = $request->assignee_id;
                            $formRequest->save();
                            $successCount++;
                        }
                        break;
                }
            }

            // Clear approval caches
            app(\App\Services\ApprovalCacheService::class)->clearAllApprovalCaches();

            \DB::commit();

            return redirect()->back()->with('success', "Successfully processed {$successCount} requests");

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('PFMO bulk action failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process bulk action');
        }
    }
<<<<<<< HEAD

    /**
     * Show manage employees page
     */
    public function manageEmployees()
    {
        try {
            $employeeService = new \App\Services\PFMOEmployeeManagementService();
            
            $employees = $employeeService->getPFMOEmployees();
            $subDepartments = $employeeService->getSubDepartmentsWithSupervisors();
            
            return view('pfmo.manage-employees', compact('employees', 'subDepartments'));
        } catch (\Exception $e) {
            \Log::error('Error loading manage employees page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load employee management page');
        }
    }

    /**
     * Assign employee to sub-department
     */
    public function assignEmployee(\App\Http\Requests\AssignEmployeeRequest $request)
    {
        try {
            $employeeService = new \App\Services\PFMOEmployeeManagementService();
            
            $result = $employeeService->assignEmployeeToSubDepartment(
                $request->employee_id,
                $request->sub_department_id
            );
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::error('Employee assignment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Assign supervisor to sub-department
     */
    public function assignSupervisor(\App\Http\Requests\AssignSupervisorRequest $request)
    {
        try {
            $employeeService = new \App\Services\PFMOEmployeeManagementService();
            
            if ($request->reassign) {
                $result = $employeeService->reassignSupervisorToSubDepartment(
                    $request->employee_id,
                    $request->sub_department_id
                );
            } else {
                $result = $employeeService->assignSupervisorToSubDepartment(
                    $request->employee_id,
                    $request->sub_department_id
                );
            }
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::error('Supervisor assignment failed: ' . $e->getMessage());
            
            // Handle specific error for existing supervisor
            if (strpos($e->getMessage(), 'already has a supervisor') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_code' => 'supervisor_exists'
                ], 409);
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Unassign employee from sub-department
     */
    public function unassignEmployee(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:tb_account,accnt_id'
        ]);

        try {
            $employeeService = new \App\Services\PFMOEmployeeManagementService();
            
            $result = $employeeService->unassignEmployeeFromSubDepartment($request->employee_id);
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::error('Employee unassignment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Unassign supervisor from sub-department
     */
    public function unassignSupervisor(Request $request)
    {
        $request->validate([
            'sub_department_id' => 'required|integer|exists:sub_departments,id'
        ]);

        try {
            $employeeService = new \App\Services\PFMOEmployeeManagementService();
            
            $result = $employeeService->unassignSupervisorFromSubDepartment($request->sub_department_id);
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::error('Supervisor unassignment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

}
