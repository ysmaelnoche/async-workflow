<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\FormRequest;
use App\Models\User;
use App\Models\JobOrderProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JobOrderAnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user has access to analytics
        if (!$user->department || $user->department->dept_code !== 'PFMO' || $user->position !== 'Head') {
            abort(403, 'Unauthorized access to analytics dashboard.');
        }

        $dateRange = $request->get('range', '30'); // Default to 30 days
        $startDate = Carbon::now()->subDays($dateRange);

        $analytics = [
            'completion_rates' => $this->getCompletionRates($startDate),
            'average_resolution_time' => $this->getAverageResolutionTime($startDate),
            'workload_distribution' => $this->getWorkloadDistribution(),
            'satisfaction_ratings' => $this->getSatisfactionRatings($startDate),
            'issue_frequency' => $this->getIssueFrequency($startDate),
            'service_type_breakdown' => $this->getServiceTypeBreakdown($startDate),
            'performance_trends' => $this->getPerformanceTrends($startDate),
            'top_performers' => $this->getTopPerformers($startDate)
        ];

        return view('job-orders.analytics', compact('analytics', 'dateRange'));
    }

    /**
     * Get completion rates by technician and service type
     */
    private function getCompletionRates($startDate)
    {
        // By technician
        $byTechnician = JobOrder::select('assigned_to', 'tb_account.username')
            ->join('tb_account', 'job_orders.assigned_to', '=', 'tb_account.accnt_id')
            ->where('job_orders.created_at', '>=', $startDate)
            ->groupBy('assigned_to', 'tb_account.username')
            ->selectRaw('
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_jobs,
                ROUND((SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as completion_rate
            ')
            ->get();

        // By service type
        $byServiceType = JobOrder::where('created_at', '>=', $startDate)
            ->selectRaw('
                CASE 
                    WHEN LOWER(request_description) LIKE "%electrical%" THEN "Electrical"
                    WHEN LOWER(request_description) LIKE "%plumbing%" THEN "Plumbing"
                    WHEN LOWER(request_description) LIKE "%aircon%" THEN "Air Conditioning"
                    WHEN LOWER(request_description) LIKE "%carpentry%" THEN "Carpentry"
                    ELSE "General Maintenance"
                END as service_type,
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_jobs,
                ROUND((SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as completion_rate
            ')
            ->groupBy('service_type')
            ->get();

        return [
            'by_technician' => $byTechnician,
            'by_service_type' => $byServiceType
        ];
    }

    /**
     * Get average resolution time by category
     */
    private function getAverageResolutionTime($startDate)
    {
        return JobOrder::where('status', 'Completed')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('job_started_at')
            ->whereNotNull('job_completed_at')
            ->selectRaw('
                CASE 
                    WHEN LOWER(request_description) LIKE "%electrical%" THEN "Electrical"
                    WHEN LOWER(request_description) LIKE "%plumbing%" THEN "Plumbing"
                    WHEN LOWER(request_description) LIKE "%aircon%" THEN "Air Conditioning"
                    WHEN LOWER(request_description) LIKE "%carpentry%" THEN "Carpentry"
                    ELSE "General Maintenance"
                END as service_type,
                COUNT(*) as job_count,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, job_started_at, job_completed_at)), 2) as avg_hours,
                MIN(TIMESTAMPDIFF(HOUR, job_started_at, job_completed_at)) as min_hours,
                MAX(TIMESTAMPDIFF(HOUR, job_started_at, job_completed_at)) as max_hours
            ')
            ->groupBy('service_type')
            ->get();
    }

    /**
     * Get current workload distribution across staff
     */
    private function getWorkloadDistribution()
    {
        return User::select('tb_account.accnt_id', 'tb_account.username')
            ->join('departments', 'tb_account.department_id', '=', 'departments.department_id')
            ->where('departments.dept_code', 'PFMO')
            ->where('tb_account.position', '!=', 'Head')
            ->withCount([
                'assignedJobOrders as pending_jobs' => function ($q) {
                    $q->where('status', 'Pending');
                },
                'assignedJobOrders as in_progress_jobs' => function ($q) {
                    $q->where('status', 'In Progress');
                },
                'assignedJobOrders as completed_jobs' => function ($q) {
                    $q->where('status', 'Completed')
                        ->where('job_completed_at', '>=', Carbon::now()->subDays(30));
                }
            ])
            ->get();
    }

    /**
     * Get satisfaction ratings from feedback
     */
    private function getSatisfactionRatings($startDate)
    {
        $ratings = JobOrder::where('requestor_feedback_submitted', true)
            ->where('requestor_feedback_date', '>=', $startDate)
            ->whereNotNull('requestor_satisfaction_rating')
            ->select('tb_account.username as technician_name')
            ->join('tb_account', 'job_orders.assigned_to', '=', 'tb_account.accnt_id')
            ->selectRaw('
                COUNT(*) as total_ratings,
                ROUND(AVG(requestor_satisfaction_rating), 2) as avg_rating,
                SUM(CASE WHEN requestor_satisfaction_rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN requestor_satisfaction_rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN requestor_satisfaction_rating <= 3 THEN 1 ELSE 0 END) as three_or_less
            ')
            ->groupBy('assigned_to', 'tb_account.username')
            ->get();

        $overall = JobOrder::where('requestor_feedback_submitted', true)
            ->where('requestor_feedback_date', '>=', $startDate)
            ->whereNotNull('requestor_satisfaction_rating')
            ->selectRaw('
                COUNT(*) as total_ratings,
                ROUND(AVG(requestor_satisfaction_rating), 2) as avg_rating,
                SUM(CASE WHEN requestor_satisfaction_rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN requestor_satisfaction_rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN requestor_satisfaction_rating <= 3 THEN 1 ELSE 0 END) as three_or_less
            ')
            ->first();

        return [
            'by_technician' => $ratings,
            'overall' => $overall
        ];
    }

    /**
     * Get issue frequency tracking
     */
    private function getIssueFrequency($startDate)
    {
        return JobOrderProgress::where('update_type', 'issue')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('issues_encountered')
            ->select('job_orders.job_order_number', 'tb_account.username as technician_name')
            ->join('job_orders', 'job_order_progress.job_order_id', '=', 'job_orders.job_order_id')
            ->join('tb_account', 'job_order_progress.user_id', '=', 'tb_account.accnt_id')
            ->selectRaw('
                issues_encountered,
                job_order_progress.created_at as reported_at,
                CASE 
                    WHEN LOWER(issues_encountered) LIKE "%material%" THEN "Materials"
                    WHEN LOWER(issues_encountered) LIKE "%tool%" THEN "Tools"
                    WHEN LOWER(issues_encountered) LIKE "%access%" THEN "Access"
                    WHEN LOWER(issues_encountered) LIKE "%power%" THEN "Power/Utilities"
                    ELSE "Other"
                END as issue_category
            ')
            ->get()
            ->groupBy('issue_category');
    }

    /**
     * Get service type breakdown
     */
    private function getServiceTypeBreakdown($startDate)
    {
        return JobOrder::where('created_at', '>=', $startDate)
            ->selectRaw('
                CASE 
                    WHEN LOWER(request_description) LIKE "%electrical%" THEN "Electrical"
                    WHEN LOWER(request_description) LIKE "%plumbing%" THEN "Plumbing"
                    WHEN LOWER(request_description) LIKE "%aircon%" THEN "Air Conditioning"
                    WHEN LOWER(request_description) LIKE "%carpentry%" THEN "Carpentry"
                    ELSE "General Maintenance"
                END as service_type,
                COUNT(*) as job_count,
                ROUND((COUNT(*) / (SELECT COUNT(*) FROM job_orders WHERE created_at >= ?)) * 100, 2) as percentage
            ', [$startDate])
            ->groupBy('service_type')
            ->orderBy('job_count', 'desc')
            ->get();
    }

    /**
     * Get performance trends over time
     */
    private function getPerformanceTrends($startDate)
    {
        return JobOrder::where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as jobs_created,
                SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as jobs_completed,
                ROUND(AVG(CASE WHEN status = "Completed" AND job_started_at IS NOT NULL AND job_completed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, job_started_at, job_completed_at) END), 2) as avg_completion_time
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get top performing technicians
     */
    private function getTopPerformers($startDate)
    {
        return User::select('tb_account.accnt_id', 'tb_account.username')
            ->join('departments', 'tb_account.department_id', '=', 'departments.department_id')
            ->where('departments.dept_code', 'PFMO')
            ->where('tb_account.position', '!=', 'Head')
            ->withCount([
                'assignedJobOrders as completed_jobs' => function ($q) use ($startDate) {
                    $q->where('status', 'Completed')
                        ->where('job_completed_at', '>=', $startDate);
                }
            ])
            ->withAvg([
                'assignedJobOrders as avg_rating' => function ($q) use ($startDate) {
                    $q->where('requestor_feedback_submitted', true)
                        ->where('requestor_feedback_date', '>=', $startDate)
                        ->whereNotNull('requestor_satisfaction_rating');
                }
            ], 'requestor_satisfaction_rating')
            ->having('completed_jobs', '>', 0)
            ->orderBy('avg_rating', 'desc')
            ->orderBy('completed_jobs', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * API endpoint for real-time analytics data
     */
    public function apiData(Request $request)
    {
        $user = Auth::user();

        if (!$user->department || $user->department->dept_code !== 'PFMO' || $user->position !== 'Head') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $type = $request->get('type', 'overview');
        $range = $request->get('range', '30');
        $startDate = Carbon::now()->subDays($range);

        switch ($type) {
            case 'completion_rates':
                return response()->json($this->getCompletionRates($startDate));

            case 'workload':
                return response()->json($this->getWorkloadDistribution());

            case 'satisfaction':
                return response()->json($this->getSatisfactionRatings($startDate));

            case 'trends':
                return response()->json($this->getPerformanceTrends($startDate));

            default:
                return response()->json([
                    'total_jobs' => JobOrder::where('created_at', '>=', $startDate)->count(),
                    'completed_jobs' => JobOrder::where('status', 'Completed')->where('job_completed_at', '>=', $startDate)->count(),
                    'in_progress_jobs' => JobOrder::where('status', 'In Progress')->count(),
                    'pending_jobs' => JobOrder::where('status', 'Pending')->count(),
                    'avg_satisfaction' => JobOrder::where('requestor_feedback_submitted', true)
                        ->where('requestor_feedback_date', '>=', $startDate)
                        ->avg('requestor_satisfaction_rating')
                ]);
        }
    }
}
