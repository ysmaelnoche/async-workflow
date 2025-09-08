<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\FormRequest;
use App\Models\SignatureStyle;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobOrderController extends Controller
{
    /**
     * Check if the current user has access to job order management
     */
    private function checkPfmoAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->department || $user->department->dept_code !== 'PFMO') {
            abort(403, 'Access denied. Job order management is restricted to PFMO department only.');
        }
    }

    public function index()
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        // For PFMO staff/head - show job orders for their department
        $jobOrders = JobOrder::with(['formRequest', 'creator'])
            ->when($user->department && $user->department->dept_code === 'PFMO', function ($query) use ($user) {
                if ($user->position === 'Head') {
                    // Head can see all PFMO job orders
                    $query->whereHas('formRequest', function ($q) {
                        $q->whereHas('toDepartment', function ($dept) {
                            $dept->where('dept_code', 'PFMO');
                        });
                    });
                } else {
                    // Staff see job orders they created or are assigned to their department
                    $query->where(function ($q) use ($user) {
                        $q->where('created_by', $user->accnt_id)
                            ->orWhereHas('formRequest', function ($req) use ($user) {
                                $req->where('to_department_id', $user->department_id);
                            });
                    });
                }
            })
            ->latest()
            ->paginate(15);

        return view('job-orders.index', compact('jobOrders'));
    }

    public function show(JobOrder $jobOrder)
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        // Check if user can view this job order
        if (!$this->canViewJobOrder($jobOrder, $user)) {
            abort(403, 'Unauthorized access to this job order.');
        }

        $jobOrder->load(['formRequest.requester', 'creator', 'progress']);

        // Get progress history for the view
        $progressHistory = $jobOrder->progress;

        return view('job-orders.show', compact('jobOrder', 'progressHistory'));
    }

    public function startJob(JobOrder $jobOrder)
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        if (!$this->canManageJobOrder($jobOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if (!$jobOrder->canStart()) {
            return response()->json(['success' => false, 'message' => 'Job order cannot be started at this time.'], 400);
        }

        try {
            DB::transaction(function () use ($jobOrder) {
                $jobOrder->start();

                // Send notification to requestor
                EmailNotificationService::sendJobStarted($jobOrder);

                // Log the action
                Log::info('Job order started', [
                    'job_order_id' => $jobOrder->job_order_id,
                    'job_order_number' => $jobOrder->job_order_number,
                    'started_by' => auth()->id()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Job order has been started successfully.',
                'status' => $jobOrder->fresh()->status
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting job order: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to start job order.'], 500);
        }
    }

    public function updateProgress(Request $request, JobOrder $jobOrder)
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        if (!$this->canManageJobOrder($jobOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if ($jobOrder->status !== 'In Progress') {
            return response()->json(['success' => false, 'message' => 'Can only update progress for jobs in progress.'], 400);
        }

        // Validate progress data
        $request->validate([
            'progress_note' => 'required|string|max:1000',
            'current_location' => 'nullable|string|max:255',
            'issues_encountered' => 'nullable|string|max:1000',
            'estimated_time_remaining' => 'nullable|integer|min:1',
            'estimated_time_unit' => 'nullable|in:days,weeks,months'
        ]);

        try {
            DB::transaction(function () use ($jobOrder, $request, $user) {
                // Create progress entry
                $progressData = [
                    'job_order_id' => $jobOrder->job_order_id,
                    'user_id' => $user->accnt_id,
                    'update_type' => 'progress',
                    'progress_note' => $request->progress_note,
                    'current_location' => $request->current_location,
                    'issues_encountered' => $request->issues_encountered,
                    'estimated_time_remaining' => $request->estimated_time_remaining,
                    'estimated_time_unit' => $request->estimated_time_unit ?? 'days',
                ];

                $jobOrder->progress()->create($progressData);

                // Log the action
                Log::info('Job order progress updated', [
                    'job_order_id' => $jobOrder->job_order_id,
                    'job_order_number' => $jobOrder->job_order_number,
                    'percentage' => $request->percentage_complete,
                    'updated_by' => $user->accnt_id
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully.',
                'percentage' => $request->percentage_complete
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating job order progress: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update progress. Error: ' . $e->getMessage()], 500);
        }
    }

    public function completeJob(Request $request, JobOrder $jobOrder)
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        if (!$this->canManageJobOrder($jobOrder, $user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if (!$jobOrder->canComplete()) {
            return response()->json(['success' => false, 'message' => 'Job order cannot be completed at this time.'], 400);
        }

        // Validate completion data
        $request->validate([
            'actions_taken' => 'required|string|max:1000',
            'findings' => 'nullable|string|max:500',
            'recommendations' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($jobOrder, $request, $user) {
                $jobOrder->complete([
                    'actions_taken' => $request->actions_taken,
                    'findings' => $request->findings,
                    'recommendations' => $request->recommendations,
                    'date_completed' => now()->toDateString(),
                    'job_completed_by' => $user->username ?? $user->email ?? $user->accnt_id
                ]);

                // Log the action
                Log::info('Job order completed', [
                    'job_order_id' => $jobOrder->job_order_id,
                    'job_order_number' => $jobOrder->job_order_number,
                    'completed_by' => $user->accnt_id
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Job order has been completed successfully.',
                'status' => $jobOrder->fresh()->status
            ]);

        } catch (\Exception $e) {
            Log::error('Error completing job order: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to complete job order. Error: ' . $e->getMessage()], 500);
        }
    }

    public function submitCompleteForm(Request $request, JobOrder $jobOrder)
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        if (!$this->canManageJobOrder($jobOrder, $user)) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Validate form data including signature
        $request->validate([
            'request_description' => 'required|string|max:1000',
            'findings' => 'nullable|string|max:500',
            'actual_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'signature_name' => 'required|string|max:255',
            'signature_style_id' => 'required|exists:signature_styles,id'
        ]);

        try {
            DB::transaction(function () use ($jobOrder, $request) {
                // Complete the job order
                $jobOrder->complete([
                    'request_description' => $request->request_description,
                    'findings' => $request->findings,
                    'actual_cost' => $request->actual_cost,
                    'date_completed' => now()->toDateString(),
                    'notes' => $request->notes
                ]);

                // Store signature information
                $jobOrder->update([
                    'requestor_signature' => $request->signature_name,
                    'signature_style_id' => $request->signature_style_id
                ]);

                Log::info('Job order completed with signature', [
                    'job_order_id' => $jobOrder->job_order_id,
                    'job_order_number' => $jobOrder->job_order_number,
                    'completed_by' => auth()->id()
                ]);
            });

            return redirect()->route('job-orders.show', $jobOrder)
                ->with('success', 'Job order has been completed successfully.');

        } catch (\Exception $e) {
            Log::error('Error completing job order: ' . $e->getMessage());
            return back()->with('error', 'Failed to complete job order. Please try again.');
        }
    }

    /**
     * Display printable form for job order
     */
    public function printableForm(Request $request, JobOrder $jobOrder)
    {
        $this->checkPfmoAccess();
        $user = Auth::user();

        if (!$this->canViewJobOrder($jobOrder, $user)) {
            abort(403, 'Unauthorized access to job order.');
        }

        // Load required relationships
        $jobOrder->load(['formRequest.requester.employeeInfo', 'formRequest.fromDepartment']);

        // Check if PDF download is requested
        if ($request->get('download') === 'pdf') {
            $html = view('job-orders.printable-form', compact('jobOrder'))->render();

            // For now, return the view with PDF-specific headers
            // Later we can integrate with a PDF library like dompdf or wkhtmltopdf
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="job-order-' . $jobOrder->job_order_number . '.html"');
        }

        return view('job-orders.printable-form', compact('jobOrder'));
    }

    // Helper methods
    private function canViewJobOrder(JobOrder $jobOrder, $user): bool
    {
        // PFMO staff can view job orders they created
        if ($jobOrder->created_by === $user->accnt_id) {
            return true;
        }

        // PFMO head can view all PFMO job orders
        if ($user->position === 'Head' && $user->department && $user->department->dept_code === 'PFMO') {
            return true;
        }

        // Requestor can view their own job orders
        if ($jobOrder->formRequest && $jobOrder->formRequest->requested_by === $user->accnt_id) {
            return true;
        }

        // PFMO staff can view job orders for their department
        if ($user->department && $user->department->dept_code === 'PFMO') {
            return true;
        }

        return false;
    }

    private function canManageJobOrder(JobOrder $jobOrder, $user): bool
    {
        // Allow any PFMO department member to manage job orders
        if ($user->department && $user->department->dept_code === 'PFMO') {
            return true;
        }

        // Fallback: Original creator can always manage their own job orders
        if ($jobOrder->created_by === $user->accnt_id) {
            return true;
        }

        return false;
    }

    /**
     * Fill up job order details by requestor
     */
    public function fillUpJobOrder(Request $request, JobOrder $jobOrder)
    {
        $user = Auth::user();

        // Check if user is the requestor of this job order
        if ($user->accnt_id !== $jobOrder->formRequest->requested_by) {
            return back()->with('error', 'You can only fill up your own job order.');
        }

        // Check if job order is already filled up
        if (!empty($jobOrder->request_description) && trim($jobOrder->request_description) !== '') {
            return back()->with('error', 'This job order has already been filled up.');
        }

        // Validate the request
        $request->validate([
            'request_description' => 'required|string|max:1000',
            'requestor_signature' => 'required|string',
            'assistance' => 'nullable|boolean',
            'repair_repaint' => 'nullable|boolean',
            'installation' => 'nullable|boolean',
            'cleaning' => 'nullable|boolean',
            'check_up_inspection' => 'nullable|boolean',
            'construction_fabrication' => 'nullable|boolean',
            'pull_out_transfer' => 'nullable|boolean',
            'replacement' => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($jobOrder, $request, $user) {
                // Update job order with requestor details
                $jobOrder->update([
                    'request_description' => $request->request_description,
                    'requestor_signature' => $request->requestor_signature,
                    'requestor_signature_date' => now()->toDateString(),
                    'requestor_name' => $user->employeeInfo->first_name . ' ' . $user->employeeInfo->last_name,
                    'department' => $user->department->dept_name ?? 'N/A',
                    'assistance' => $request->boolean('assistance'),
                    'repair_repaint' => $request->boolean('repair_repaint'),
                    'installation' => $request->boolean('installation'),
                    'cleaning' => $request->boolean('cleaning'),
                    'check_up_inspection' => $request->boolean('check_up_inspection'),
                    'construction_fabrication' => $request->boolean('construction_fabrication'),
                    'pull_out_transfer' => $request->boolean('pull_out_transfer'),
                    'replacement' => $request->boolean('replacement'),
                    'date_received' => now()->toDateString(),
                    'received_by' => 'PFMO'
                ]);

                // Log the action
                Log::info('Job order filled up by requestor', [
                    'job_order_id' => $jobOrder->job_order_id,
                    'job_order_number' => $jobOrder->job_order_number,
                    'filled_by' => $user->accnt_id
                ]);
            });

            return back()->with('success', 'Job order details have been submitted successfully!');

        } catch (\Exception $e) {
            Log::error('Error filling up job order: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit job order details. Please try again.');
        }
    }

    /**
     * Submit feedback for completed job order
     */
    public function submitFeedback(Request $request, JobOrder $jobOrder)
    {
        $user = Auth::user();

        // Check if user is the requestor of this job order
        if ($user->accnt_id !== $jobOrder->formRequest->requested_by) {
            return back()->with('error', 'You can only provide feedback for your own job order.');
        }

        // Check if job order is completed
        if ($jobOrder->status !== 'Completed') {
            return back()->with('error', 'You can only provide feedback for completed job orders.');
        }

        // Check if feedback is already submitted
        if ($jobOrder->requestor_comments) {
            return back()->with('error', 'You have already submitted feedback for this job order.');
        }

        // Validate the request
        $request->validate([
            'satisfaction_rating' => 'required|integer|between:1,5',
            'requestor_comments' => 'nullable|string|max:1000',
            'for_further_action' => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($jobOrder, $request, $user) {
                // Update job order with feedback
                $jobOrder->update([
                    'requestor_satisfaction_rating' => $request->satisfaction_rating,
                    'requestor_comments' => $request->requestor_comments,
                    'for_further_action' => $request->boolean('for_further_action'),
                    'requestor_feedback_submitted' => true,
                    'requestor_feedback_date' => now(),
                ]);

                // Log the action
                Log::info('Job order feedback submitted', [
                    'job_order_id' => $jobOrder->job_order_id,
                    'job_order_number' => $jobOrder->job_order_number,
                    'satisfaction_rating' => $request->satisfaction_rating,
                    'feedback_by' => $user->accnt_id
                ]);
            });

            return back()->with('success', 'Thank you for your feedback! Your response has been recorded.');

        } catch (\Exception $e) {
            Log::error('Error submitting job order feedback: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit feedback. Please try again.');
        }
    }
}
