<?php

namespace App\Services;

use App\Models\JobOrder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PFMOFeedbackService
{
    /**
     * Get comprehensive feedback statistics for PFMO dashboard
     */
    public static function getFeedbackStatistics()
    {
        $stats = [];
        
        // Total feedback submitted
        $stats['total_feedback'] = JobOrder::whereNotNull('requestor_comments')
            ->orWhereNotNull('requestor_satisfaction_rating')
            ->count();
            
        // Feedback submitted today
        $stats['feedback_today'] = JobOrder::whereDate('requestor_feedback_date', today())
            ->whereNotNull('requestor_comments')
            ->count();
            
        // Average rating (overall)
        $stats['average_rating'] = round(
            JobOrder::whereNotNull('requestor_satisfaction_rating')
                ->avg('requestor_satisfaction_rating'), 
            1
        ) ?: 0;
        
        // Jobs requiring further action
        $stats['needs_action'] = JobOrder::where('for_further_action', true)
            ->where('status', 'Completed')
            ->count();
            
        // Feedback completion rate
        $completedJobs = JobOrder::where('status', 'Completed')->count();
        $feedbackSubmitted = JobOrder::where('status', 'Completed')
            ->where('requestor_feedback_submitted', true)
            ->count();
        $stats['completion_rate'] = $completedJobs > 0 
            ? round(($feedbackSubmitted / $completedJobs) * 100, 1)
            : 0;
            
        return $stats;
    }
    
    /**
     * Get recent feedback submissions
     */
    public static function getRecentFeedback($limit = 10)
    {
        return JobOrder::with(['formRequest.requester.employeeInfo'])
            ->whereNotNull('requestor_comments')
            ->whereNotNull('requestor_satisfaction_rating')
            ->orderBy('requestor_feedback_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jobOrder) {
                return [
                    'job_order_number' => $jobOrder->job_order_number,
                    'rating' => $jobOrder->requestor_satisfaction_rating,
                    'comments' => $jobOrder->requestor_comments,
                    'feedback_date' => $jobOrder->requestor_feedback_date,
                    'requester_name' => $jobOrder->formRequest->requester->employeeInfo->FirstName . ' ' . 
                                       $jobOrder->formRequest->requester->employeeInfo->LastName,
                    'request_description' => $jobOrder->request_description ?: 'No description',
                    'needs_action' => $jobOrder->for_further_action,
                    'form_id' => $jobOrder->formRequest->form_id
                ];
            });
    }
    
    /**
     * Get rating distribution for charts
     */
    public static function getRatingDistribution()
    {
        $distribution = [];
        $totalRatings = JobOrder::whereNotNull('requestor_satisfaction_rating')->count();
        
        for ($i = 1; $i <= 5; $i++) {
            $count = JobOrder::where('requestor_satisfaction_rating', $i)->count();
            $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100, 1) : 0;
            
            $distribution[$i] = [
                'stars' => $i,
                'count' => $count,
                'percentage' => $percentage
            ];
        }
        
        return $distribution;
    }
    
    /**
     * Get job orders that need further action based on feedback
     */
    public static function getJobsNeedingAction($limit = 5)
    {
        return JobOrder::with(['formRequest.requester.employeeInfo'])
            ->where('for_further_action', true)
            ->where('status', 'Completed')
            ->orderBy('requestor_feedback_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jobOrder) {
                return [
                    'job_order_number' => $jobOrder->job_order_number,
                    'rating' => $jobOrder->requestor_satisfaction_rating,
                    'comments' => $jobOrder->requestor_comments,
                    'feedback_date' => $jobOrder->requestor_feedback_date,
                    'requester_name' => $jobOrder->formRequest->requester->employeeInfo->FirstName . ' ' . 
                                       $jobOrder->formRequest->requester->employeeInfo->LastName,
                    'request_description' => $jobOrder->request_description ?: 'No description',
                    'form_id' => $jobOrder->formRequest->form_id
                ];
            });
    }
    
    /**
     * Get average rating for specific time period
     */
    public static function getAverageRating($period = '30 days')
    {
        $startDate = Carbon::now()->subtract($period);
        
        return round(
            JobOrder::whereNotNull('requestor_satisfaction_rating')
                ->where('requestor_feedback_date', '>=', $startDate)
                ->avg('requestor_satisfaction_rating'), 
            1
        ) ?: 0;
    }
    
    /**
     * Get feedback trends over time
     */
    public static function getFeedbackTrends($days = 30)
    {
        $trends = [];
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days);
        
        // Get daily feedback counts and ratings
        $dailyData = JobOrder::selectRaw('
                DATE(requestor_feedback_date) as date,
                COUNT(*) as feedback_count,
                AVG(requestor_satisfaction_rating) as avg_rating
            ')
            ->whereNotNull('requestor_feedback_date')
            ->whereBetween('requestor_feedback_date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        return $dailyData->map(function ($data) {
            return [
                'date' => $data->date,
                'feedback_count' => $data->feedback_count,
                'average_rating' => round($data->avg_rating, 1)
            ];
        });
    }
    
    /**
     * Get feedback by department (requesting department)
     */
    public static function getFeedbackByDepartment()
    {
        return DB::table('job_orders')
            ->join('form_requests', 'job_orders.form_id', '=', 'form_requests.form_id')
            ->join('tb_department', 'form_requests.from_department_id', '=', 'tb_department.department_id')
            ->selectRaw('
                tb_department.dept_name,
                tb_department.dept_code,
                COUNT(*) as total_feedback,
                AVG(job_orders.requestor_satisfaction_rating) as avg_rating
            ')
            ->whereNotNull('job_orders.requestor_satisfaction_rating')
            ->groupBy('tb_department.department_id', 'tb_department.dept_name', 'tb_department.dept_code')
            ->orderByDesc('total_feedback')
            ->get()
            ->map(function ($data) {
                return [
                    'department_name' => $data->dept_name,
                    'department_code' => $data->dept_code,
                    'total_feedback' => $data->total_feedback,
                    'average_rating' => round($data->avg_rating, 1)
                ];
            });
    }
    
    /**
     * Get low-rated jobs that may need attention
     */
    public static function getLowRatedJobs($threshold = 3, $limit = 10)
    {
        return JobOrder::with(['formRequest.requester.employeeInfo'])
            ->where('requestor_satisfaction_rating', '<=', $threshold)
            ->whereNotNull('requestor_satisfaction_rating')
            ->orderBy('requestor_feedback_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($jobOrder) {
                return [
                    'job_order_number' => $jobOrder->job_order_number,
                    'rating' => $jobOrder->requestor_satisfaction_rating,
                    'comments' => $jobOrder->requestor_comments,
                    'feedback_date' => $jobOrder->requestor_feedback_date,
                    'requester_name' => $jobOrder->formRequest->requester->employeeInfo->FirstName . ' ' . 
                                       $jobOrder->formRequest->requester->employeeInfo->LastName,
                    'request_description' => $jobOrder->request_description ?: 'No description',
                    'needs_action' => $jobOrder->for_further_action,
                    'form_id' => $jobOrder->formRequest->form_id
                ];
            });
    }
    
    /**
     * Get summary of feedback for dashboard overview
     */
    public static function getDashboardSummary()
    {
        return [
            'statistics' => self::getFeedbackStatistics(),
            'recent_feedback' => self::getRecentFeedback(5),
            'rating_distribution' => self::getRatingDistribution(),
            'jobs_needing_action' => self::getJobsNeedingAction(3),
            'low_rated_jobs' => self::getLowRatedJobs(3, 3),
            'department_feedback' => self::getFeedbackByDepartment()
        ];
    }
}
