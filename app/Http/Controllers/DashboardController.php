<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// use App\Models\RequestModel; // Old model
use App\Models\FormRequest; // New model
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $user = Auth::user();

            // Redirect admin users to admin dashboard
            if ($user->accessRole === 'Admin') {
                return redirect()->route('admin.dashboard')
                    ->with('info', 'Redirected to Admin Dashboard.');
            }

            $departmentName = $user->department ? $user->department->dept_name : 'N/A';

            // Get the active tab from the query parameter, default to 'all'
            $activeTab = $request->query('tab', 'all');

            // Base query for user's requests with eager loading
            $baseQuery = FormRequest::with(['iomDetails', 'leaveDetails', 'fromDepartment', 'toDepartment', 'approvals.approver'])
                ->where('requested_by', $user->accnt_id);

            // Get counts for tabs first
            $counts = [
                'all' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->whereIn('status', [
                    'Pending',
                    'In Progress',
                    'Pending Department Head Approval',
                    'Pending Target Department Approval'
                ])->count(),
                'completed' => (clone $baseQuery)->where('status', 'Approved')->count(),
                'rejected' => (clone $baseQuery)->where('status', 'Rejected')->count(),
            ];

            // Apply filters based on active tab
            $requests = match ($activeTab) {
                'pending' => (clone $baseQuery)->whereIn('status', [
                    'Pending',
                    'In Progress',
                    'Pending Department Head Approval',
                    'Pending Target Department Approval'
                ]),
                'completed' => (clone $baseQuery)->where('status', 'Approved'),
                'rejected' => (clone $baseQuery)->where('status', 'Rejected'),
                default => clone $baseQuery,
            };

            // Get the paginated results
            $requests = $requests->latest('date_submitted')->paginate(10)->withQueryString();

            // Calculate monthly and yearly counts
            $now = Carbon::now();
            $monthlyCount = (clone $baseQuery)
                ->whereYear('date_submitted', $now->year)
                ->whereMonth('date_submitted', $now->month)
                ->count();

            $yearlyCount = (clone $baseQuery)
                ->whereYear('date_submitted', $now->year)
                ->count();

            // Calculate average processing time for completed requests
            $completedRequests = (clone $baseQuery)
                ->where('status', 'Approved')
                ->with([
                    'approvals' => function ($query) {
                        $query->whereIn('action', ['Approved', 'Submitted']);
                    }
                ])
                ->get();

            $avgProcessingTime = 'N/A';
            if ($completedRequests->isNotEmpty()) {
                $totalProcessingTime = 0;
                $completedCount = 0;

                foreach ($completedRequests as $req) {
                    $submitted = $req->approvals->where('action', 'Submitted')->first();
                    $approved = $req->approvals->where('action', 'Approved')->first();

                    if ($submitted && $approved) {
                        $totalProcessingTime += $submitted->action_date->diffInHours($approved->action_date);
                        $completedCount++;
                    }
                }

                if ($completedCount > 0) {
                    $avgProcessingTime = round($totalProcessingTime / $completedCount) . ' hrs';
                }
            }

            // Calculate approval rate
            $totalFinalized = (clone $baseQuery)
                ->whereIn('status', ['Approved', 'Rejected'])
                ->count();

            $totalApproved = (clone $baseQuery)
                ->where('status', 'Approved')
                ->count();

            $approvalRate = $totalFinalized > 0
                ? round(($totalApproved / $totalFinalized) * 100)
                : 0;

            // Get feedback data for PFMO users only
            $recentFeedback = collect();
            $averageRating = null;
            $totalFeedback = 0;

            if ($user->department && $user->department->dept_code === 'PFMO') {
                // Get recent job order feedback (5 most recent by default)
                $recentFeedback = \App\Models\JobOrder::with([
                    'formRequest.requester.employeeInfo',
                    'formRequest.iomDetails'
                ])
                    ->where('status', 'Completed')
                    ->whereNotNull('requestor_comments')
                    ->whereNotNull('requestor_satisfaction_rating')
                    ->orderBy('requestor_feedback_date', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($jobOrder) {
                        return [
                            'job_order_number' => $jobOrder->job_order_number,
                            'satisfaction_rating' => $jobOrder->requestor_satisfaction_rating,
                            'comments' => $jobOrder->requestor_comments,
                            'comments_preview' => strlen($jobOrder->requestor_comments) > 80
                                ? substr($jobOrder->requestor_comments, 0, 80) . '...'
                                : $jobOrder->requestor_comments,
                            'requestor_name' => $jobOrder->formRequest->requester->employeeInfo->FirstName . ' ' . $jobOrder->formRequest->requester->employeeInfo->LastName,
                            'job_type' => $jobOrder->formRequest->iomDetails->purpose ?? 'General Request',
                            'feedback_date' => $jobOrder->requestor_feedback_date,
                            'formatted_date' => \Carbon\Carbon::parse($jobOrder->requestor_feedback_date)->format('M j, Y'),
                        ];
                    });

                // Calculate average rating for current month
                $monthlyFeedback = \App\Models\JobOrder::where('status', 'Completed')
                    ->whereNotNull('requestor_satisfaction_rating')
                    ->whereYear('requestor_feedback_date', $now->year)
                    ->whereMonth('requestor_feedback_date', $now->month)
                    ->get();

                if ($monthlyFeedback->count() > 0) {
                    $averageRating = round($monthlyFeedback->avg('requestor_satisfaction_rating'), 1);
                    $totalFeedback = $monthlyFeedback->count();
                }
            }

            // Debug information
            \Log::info('Dashboard Query Info', [
                'user_id' => $user->accnt_id,
                'active_tab' => $activeTab,
                'total_requests' => $counts['all'],
                'filtered_requests' => $requests->total(),
                'current_page_count' => $requests->count(),
            ]);

            return view('dashboard', [
                'departmentName' => $departmentName,
                'position' => $user->position,
                'requests' => $requests,
                'activeTab' => $activeTab,
                'counts' => $counts,
                'monthlyCount' => $monthlyCount,
                'yearlyCount' => $yearlyCount,
                'avgProcessingTime' => $avgProcessingTime,
                'approvalRate' => $approvalRate,
                'recentFeedback' => $recentFeedback,
                'averageRating' => $averageRating,
                'totalFeedback' => $totalFeedback,
                'isPFMOUser' => $user->department && $user->department->dept_code === 'PFMO',
            ]);

        } catch (\Exception $e) {
            // Log the error and return with a friendly message
            \Log::error('Dashboard Error: ' . $e->getMessage());
            return view('dashboard', [
                'departmentName' => $departmentName ?? 'N/A',
                'position' => $user->position ?? 'N/A',
                'requests' => collect([]),
                'activeTab' => 'all',
                'counts' => ['all' => 0, 'pending' => 0, 'completed' => 0, 'rejected' => 0],
                'monthlyCount' => 0,
                'yearlyCount' => 0,
                'avgProcessingTime' => 'N/A',
                'approvalRate' => 0,
                'recentFeedback' => collect(),
                'averageRating' => null,
                'totalFeedback' => 0,
                'isPFMOUser' => false,
                'error' => 'There was an error loading the dashboard. Please try again later.'
            ]);
        }
    }

    /**
     * Load more feedback for PFMO users (AJAX endpoint)
     */
    public function moreFeedback(Request $request)
    {
        $user = Auth::user();

        // Check if user is PFMO
        if (!$user->department || $user->department->dept_code !== 'PFMO') {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $offset = $request->get('offset', 5); // Default offset after first 5
        $limit = 10; // Load 10 more at a time

        try {
            $moreFeedback = \App\Models\JobOrder::with([
                'formRequest.requester.employeeInfo',
                'formRequest.iomDetails'
            ])
                ->where('status', 'Completed')
                ->whereNotNull('requestor_comments')
                ->whereNotNull('requestor_satisfaction_rating')
                ->orderBy('requestor_feedback_date', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(function ($jobOrder) {
                    return [
                        'job_order_number' => $jobOrder->job_order_number,
                        'satisfaction_rating' => $jobOrder->requestor_satisfaction_rating,
                        'comments' => $jobOrder->requestor_comments,
                        'comments_preview' => strlen($jobOrder->requestor_comments) > 80
                            ? substr($jobOrder->requestor_comments, 0, 80) . '...'
                            : $jobOrder->requestor_comments,
                        'requestor_name' => $jobOrder->formRequest->requester->employeeInfo->FirstName . ' ' . $jobOrder->formRequest->requester->employeeInfo->LastName,
                        'job_type' => $jobOrder->formRequest->iomDetails->purpose ?? 'General Request',
                        'feedback_date' => $jobOrder->requestor_feedback_date,
                        'formatted_date' => \Carbon\Carbon::parse($jobOrder->requestor_feedback_date)->format('M j, Y'),
                    ];
                });

            // Check if there are more records
            $hasMore = \App\Models\JobOrder::where('status', 'Completed')
                ->whereNotNull('requestor_comments')
                ->whereNotNull('requestor_satisfaction_rating')
                ->skip($offset + $limit)
                ->exists();

            return response()->json([
                'success' => true,
                'feedback' => $moreFeedback,
                'hasMore' => $hasMore
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading more feedback: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error loading feedback']);
        }
    }
}
