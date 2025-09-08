<?php

namespace App\Services;

use App\Models\JobOrder;
use App\Models\FormRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Send job order created notification to assigned technician
     */
    public static function sendJobOrderCreated(JobOrder $jobOrder): bool
    {
        try {
            $assignedUser = $jobOrder->assignedUser;

            if (!$assignedUser || !$assignedUser->email) {
                Log::warning('No assigned user or email for job order notification', [
                    'job_order_id' => $jobOrder->job_order_id
                ]);
                return false;
            }

            $emailData = [
                'job_order_number' => $jobOrder->job_order_number,
                'requestor_name' => $jobOrder->requestor_name,
                'department' => $jobOrder->department,
                'description' => $jobOrder->request_description,
                'assigned_to' => $assignedUser->username,
                'view_url' => route('job-orders.show', $jobOrder->job_order_id),
                'created_date' => $jobOrder->created_at->format('M j, Y g:i A')
            ];

            // For now, just log the email (can be extended to actual email sending)
            Log::info('Job Order Created - Email Notification', $emailData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send job order created notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send job started notification to requestor
     */
    public static function sendJobStarted(JobOrder $jobOrder): bool
    {
        try {
            $requestor = $jobOrder->formRequest->requester;

            if (!$requestor || !$requestor->email) {
                Log::warning('No requestor or email for job started notification', [
                    'job_order_id' => $jobOrder->job_order_id
                ]);
                return false;
            }

            $emailData = [
                'job_order_number' => $jobOrder->job_order_number,
                'requestor_name' => $jobOrder->requestor_name,
                'description' => $jobOrder->request_description,
                'started_date' => $jobOrder->job_started_at->format('M j, Y g:i A'),
                'track_url' => route('request.track', $jobOrder->form_request_id)
            ];

            Log::info('Job Started - Email Notification', $emailData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send job started notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send progress update notification to requestor (optional, configurable)
     */
    public static function sendProgressUpdate(JobOrder $jobOrder, array $progressData): bool
    {
        try {
            // Only send for significant progress milestones (25%, 50%, 75%)
            $percentage = $progressData['percentage_complete'];
            $milestones = [25, 50, 75];

            if (!in_array($percentage, $milestones)) {
                return true; // Skip non-milestone updates
            }

            $requestor = $jobOrder->formRequest->requester;

            if (!$requestor || !$requestor->email) {
                return false;
            }

            $emailData = [
                'job_order_number' => $jobOrder->job_order_number,
                'requestor_name' => $jobOrder->requestor_name,
                'percentage' => $percentage,
                'progress_note' => $progressData['progress_note'],
                'current_location' => $progressData['current_location'] ?? '',
                'estimated_time_remaining' => $progressData['estimated_time_remaining'] ?? null,
                'track_url' => route('request.track', $jobOrder->form_request_id)
            ];

            Log::info('Progress Update - Email Notification', $emailData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send progress update notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send job completed notification to requestor
     */
    public static function sendJobCompleted(JobOrder $jobOrder): bool
    {
        try {
            $requestor = $jobOrder->formRequest->requester;

            if (!$requestor || !$requestor->email) {
                return false;
            }

            $emailData = [
                'job_order_number' => $jobOrder->job_order_number,
                'requestor_name' => $jobOrder->requestor_name,
                'description' => $jobOrder->request_description,
                'completed_date' => $jobOrder->job_completed_at->format('M j, Y g:i A'),
                'findings' => $jobOrder->findings ?? '',
                'actions_taken' => $jobOrder->actions_taken ?? '',
                'recommendations' => $jobOrder->recommendations ?? '',
                'feedback_url' => route('request.track', $jobOrder->form_request_id) . '#feedback-required'
            ];

            Log::info('Job Completed - Email Notification (Feedback Required)', $emailData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send job completed notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send issues encountered notification to PFMO head
     */
    public static function sendIssuesEncountered(JobOrder $jobOrder, string $issues): bool
    {
        try {
            $pfmoHead = User::whereHas('department', function ($q) {
                $q->where('dept_code', 'PFMO');
            })
                ->where('position', 'Head')
                ->first();

            if (!$pfmoHead || !$pfmoHead->email) {
                return false;
            }

            $emailData = [
                'job_order_number' => $jobOrder->job_order_number,
                'assigned_technician' => $jobOrder->assignedUser->username ?? 'Unknown',
                'requestor_name' => $jobOrder->requestor_name,
                'description' => $jobOrder->request_description,
                'issues_encountered' => $issues,
                'view_url' => route('job-orders.show', $jobOrder->job_order_id),
                'reported_date' => now()->format('M j, Y g:i A')
            ];

            Log::info('Issues Encountered - Email Notification to PFMO Head', $emailData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send issues encountered notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send assignment notification to technician
     */
    public static function sendJobAssignment(JobOrder $jobOrder): bool
    {
        try {
            $assignedUser = $jobOrder->assignedUser;

            if (!$assignedUser || !$assignedUser->email) {
                return false;
            }

            $emailData = [
                'job_order_number' => $jobOrder->job_order_number,
                'technician_name' => $assignedUser->username,
                'requestor_name' => $jobOrder->requestor_name,
                'department' => $jobOrder->department,
                'description' => $jobOrder->request_description,
                'priority' => 'Normal', // Can be enhanced with actual priority field
                'view_url' => route('job-orders.show', $jobOrder->job_order_id),
                'assigned_date' => now()->format('M j, Y g:i A')
            ];

            Log::info('Job Assignment - Email Notification', $emailData);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send job assignment notification: ' . $e->getMessage());
            return false;
        }
    }
}
