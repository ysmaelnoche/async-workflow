<?php

namespace App\Services;

use App\Models\FormRequest;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobOrderService
{
    /**
     * Check if a form request needs a job order
     */
    public static function needsJobOrder(FormRequest $request): bool
    {
        // Check if it's an IOM request to PFMO department
        if ($request->form_type !== 'IOM') {
            return false;
        }

        // Check if it's going to PFMO department
        $toDepartment = $request->toDepartment;
        if (!$toDepartment || $toDepartment->dept_code !== 'PFMO') {
            return false;
        }

        // Check if it's approved
        if ($request->status !== 'Approved') {
            return false;
        }

        // Check if it already has a job order
        if ($request->jobOrder) {
            return false;
        }

        return true;
    }

    /**
     * Create a job order for a form request
     */
    public static function createJobOrder(FormRequest $request): ?JobOrder
    {
        if (!self::needsJobOrder($request)) {
            return null;
        }

        try {
            DB::beginTransaction();

            // Generate job order number with request ID
            $jobOrderNumber = self::generateJobOrderNumber($request->form_id);

            // Find a PFMO user to assign as creator (preferably head, then staff)
            $pfmoUser = \App\Models\User::whereHas('department', function ($query) {
                $query->where('dept_code', 'PFMO');
            })->where('position', 'Head')->first();

            if (!$pfmoUser) {
                // If no head found, use any PFMO staff
                $pfmoUser = \App\Models\User::whereHas('department', function ($query) {
                    $query->where('dept_code', 'PFMO');
                })->first();
            }

            $createdBy = $pfmoUser ? $pfmoUser->accnt_id : (auth()->id() ?? 53); // Fallback to user 53

            // Create the job order
            $jobOrder = JobOrder::create([
                'job_order_number' => $jobOrderNumber,
                'form_id' => $request->form_id,
                'created_by' => $createdBy,
                'requestor_name' => $request->requester->username ?? 'Unknown',
                'department' => $request->fromDepartment->dept_name ?? 'Unknown',
                'request_description' => '', // Empty - to be filled by requestor
                'status' => 'Pending'
            ]);

            // Don't update form request status - keep it as 'Approved'
            // The job order creation itself indicates the transition

            DB::commit();

            Log::info('Job order created', [
                'job_order_id' => $jobOrder->job_order_id,
                'job_order_number' => $jobOrder->job_order_number,
                'form_id' => $request->form_id
            ]);

            return $jobOrder;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create job order: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a unique job order number including request ID
     */
    private static function generateJobOrderNumber(int $requestId = null): string
    {
        $date = now()->format('Ymd');

        if ($requestId) {
            // Include request ID in job order number: JO-YYYYMMDD-REQ###-####
            $baseNumber = sprintf('JO-%s-REQ%03d', $date, $requestId);
            $counter = 1;

            do {
                $number = sprintf('%s-%03d', $baseNumber, $counter);
                $exists = JobOrder::where('job_order_number', $number)->exists();
                $counter++;
            } while ($exists);

            return $number;
        } else {
            // Fallback to old format
            $counter = 1;
            do {
                $number = sprintf('JO-%s-%04d', $date, $counter);
                $exists = JobOrder::where('job_order_number', $number)->exists();
                $counter++;
            } while ($exists);

            return $number;
        }
    }

    /**
     * Start a job order
     */
    public static function startJob(JobOrder $jobOrder): bool
    {
        if (!$jobOrder->canStart()) {
            return false;
        }

        try {
            $jobOrder->start();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to start job order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a progress update to a job order
     */
    public static function addProgressUpdate(JobOrder $jobOrder, array $data): bool
    {
        try {
            $jobOrder->progress()->create($data);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to add progress update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Request assistance for a job order
     */
    public static function requestAssistance(JobOrder $jobOrder, string $reason): bool
    {
        try {
            $jobOrder->update(['status' => 'For Further Action']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to request assistance: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set priority for a job order
     */
    public static function setPriority(JobOrder $jobOrder, string $priority, string $reason): bool
    {
        try {
            // This would need to be implemented based on your specific requirements
            // For now, just log the priority change
            Log::info('Priority set for job order', [
                'job_order_id' => $jobOrder->job_order_id,
                'priority' => $priority,
                'reason' => $reason
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set priority: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pause a job order
     */
    public static function pauseJob(JobOrder $jobOrder, string $reason): bool
    {
        try {
            // This would need to be implemented based on your specific requirements
            // For now, just log the pause
            Log::info('Job order paused', [
                'job_order_id' => $jobOrder->job_order_id,
                'reason' => $reason
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to pause job order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resume a job order
     */
    public static function resumeJob(JobOrder $jobOrder, string $reason): bool
    {
        try {
            // This would need to be implemented based on your specific requirements
            // For now, just log the resume
            Log::info('Job order resumed', [
                'job_order_id' => $jobOrder->job_order_id,
                'reason' => $reason
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to resume job order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get job order statistics
     */
    public static function getJobOrderStats(): array
    {
        try {
            return [
                'total' => JobOrder::count(),
                'pending' => JobOrder::where('status', 'Pending')->count(),
                'in_progress' => JobOrder::where('status', 'In Progress')->count(),
                'completed' => JobOrder::where('status', 'Completed')->count(),
                'for_further_action' => JobOrder::where('status', 'For Further Action')->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get job order stats: ' . $e->getMessage());
            return [];
        }
    }
}

