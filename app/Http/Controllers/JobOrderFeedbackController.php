<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobOrderFeedbackController extends Controller
{
    /**
     * Show pending job orders that need feedback
     */
    public function pendingFeedback()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login')->with('error', 'You must be logged in to access this page.');
            }

            $pendingJobOrders = JobOrder::needingFeedbackForUser($user->accnt_id);

            return view('job-orders.pending-feedback', compact('pendingJobOrders'));
        } catch (\Exception $e) {
            \Log::error('Error in pendingFeedback method: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'An error occurred while loading pending feedback. Please try again.');
        }
    }

    /**
     * Show feedback form for a specific job order
     */
    public function showFeedbackForm(JobOrder $jobOrder)
    {
        $user = Auth::user();

        // Ensure user is the requestor of this job order
        if ($jobOrder->formRequest->requested_by !== $user->accnt_id) {
            abort(403, 'Unauthorized access to job order feedback.');
        }

        // Ensure job order needs feedback
        if (!$jobOrder->needsFeedback()) {
            return redirect()->route('job-orders.pending-feedback')
                ->with('info', 'This job order does not require feedback or feedback has already been submitted.');
        }

        return view('job-orders.feedback-form', compact('jobOrder'));
    }

    /**
     * Submit feedback for a job order
     */
    public function submitFeedback(Request $request, JobOrder $jobOrder)
    {
        $user = Auth::user();

        // Ensure user is the requestor of this job order
        if ($jobOrder->formRequest->requested_by !== $user->accnt_id) {
            abort(403, 'Unauthorized access to job order feedback.');
        }

        // Ensure job order needs feedback
        if (!$jobOrder->needsFeedback()) {
            return redirect()->route('job-orders.pending-feedback')
                ->with('info', 'This job order does not require feedback or feedback has already been submitted.');
        }

        // Validate feedback data
        $validated = $request->validate([
            'satisfaction_rating' => 'required|integer|min:1|max:5',
            'comments' => 'required|string|max:1000',
        ]);

        // Update job order with feedback
        DB::transaction(function () use ($jobOrder, $validated) {
            $jobOrder->update([
                'requestor_satisfaction_rating' => $validated['satisfaction_rating'],
                'requestor_comments' => $validated['comments'],
                'requestor_feedback_submitted' => true,
                'requestor_feedback_date' => now(),
            ]);
        });

        return redirect()->route('job-orders.pending-feedback')
            ->with('success', 'Thank you for your feedback! You can now submit new IOM requests.');
    }

    /**
     * Check if user has pending feedback (for AJAX requests)
     */
    public function checkPendingFeedback()
    {
        $user = Auth::user();
        $hasPending = JobOrder::userHasPendingFeedback($user->accnt_id);

        return response()->json([
            'has_pending_feedback' => $hasPending,
            'pending_count' => $hasPending ? JobOrder::needingFeedbackForUser($user->accnt_id)->count() : 0
        ]);
    }
}
