<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\FormRequest;

/*
|--------------------------------------------------------------------------
| Debug Routes for Notification Badge
|--------------------------------------------------------------------------
|
| These routes help debug the notification badge count discrepancy
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/debug/notification-count', function () {
        if (!Auth::check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $user = Auth::user();
        $userAccntId = $user->accnt_id;
        $userDepartmentId = $user->department_id;
        $userPosition = $user->position;
        $userAccessRole = $user->accessRole;

        // Check if VPAA user
        $vpaaDepartment = \App\Models\Department::where('dept_code', 'VPAA')
            ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%')
            ->first();
        $isVPAADepartment = $vpaaDepartment && $user->department_id === $vpaaDepartment->department_id;

        if (
            $user->position === 'VPAA' ||
            ($isVPAADepartment && in_array($user->accessRole, ['Approver', 'Viewer']))
        ) {
            $vpaaRequests = \App\Http\Controllers\FixVPAAApprovals::getRequestsForVPAA();
            $badgeCount = $vpaaRequests->count();
            $debugInfo = [
                'user_type' => 'VPAA',
                'badge_count' => $badgeCount,
                'total_requests' => $vpaaRequests->count(),
                'requests' => $vpaaRequests->map(function ($req) {
                    return [
                        'form_id' => $req->form_id,
                        'title' => $req->title,
                        'status' => $req->status,
                        'form_type' => $req->form_type,
                        'requested_by' => $req->requested_by
                    ];
                })
            ];
        } else {
            // Use the exact same logic as ViewComposerServiceProvider
            $pendingCountQuery = FormRequest::query()
                ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled'])
                ->where(function ($mainQuery) use ($userAccntId, $userDepartmentId, $userPosition, $userAccessRole) {
                    // For Head position - EXACT match with ApprovalController logic
                    if ($userPosition === 'Head') {
                        $mainQuery->where(function ($headQuery) use ($userAccntId, $userDepartmentId) {
                            // Show pending requests from their department that haven't been noted yet
                            // BUT exclude their own leave requests (which should only be visible to VPAA)
                            $headQuery->where(function ($pendingQ) use ($userAccntId, $userDepartmentId) {
                                $pendingQ->where('from_department_id', $userDepartmentId)
                                    ->where('status', 'Pending')
                                    ->where(function ($excludeHeadLeave) use ($userAccntId) {
                                        $excludeHeadLeave->where('form_type', '!=', 'Leave')
                                            ->orWhere('requested_by', '!=', $userAccntId);
                                    })
                                    ->whereDoesntHave('approvals', function ($approvalQ) use ($userAccntId) {
                                        $approvalQ->where('approver_id', $userAccntId)
                                            ->where('action', 'Noted');
                                    });
                            });
                            // Show in-progress requests for their department only after being noted
                            $headQuery->orWhere(function ($inProgressQ) use ($userDepartmentId) {
                                $inProgressQ->where('to_department_id', $userDepartmentId)
                                    ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                    ->whereHas('approvals', function ($approvalQ) {
                                        // Ensure there's at least one 'Noted' approval
                                        $approvalQ->where('action', 'Noted');
                                    });
                            });
                        });
                    } else {
                        // For Staff position (both Approver and Viewer) - EXACT match with ApprovalController logic
                        if ($userPosition === 'Staff') {
                            $mainQuery->where(function ($staffQuery) use ($userDepartmentId) {
                                // Show all requests from their department, but exclude leave requests from Head
                                $staffQuery->where(function ($fromDept) use ($userDepartmentId) {
                                    $fromDept->where('from_department_id', $userDepartmentId)
                                        ->where('status', 'Pending');

                                    // Get all head users in the department
                                    $headUsers = \App\Models\User::where('position', 'Head')
                                        ->where('department_id', $userDepartmentId)
                                        ->pluck('accnt_id')
                                        ->toArray();

                                    // Exclude leave requests from department heads
                                    if (!empty($headUsers)) {
                                        $fromDept->where(function ($query) use ($headUsers) {
                                            $query->where('form_type', '!=', 'Leave')
                                                ->orWhereNotIn('requested_by', $headUsers);
                                        });
                                    }
                                });
                                // Show requests assigned to their department only after being noted by source department head
                                $staffQuery->orWhere(function ($toDept) use ($userDepartmentId) {
                                    $toDept->where('to_department_id', $userDepartmentId)
                                        ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                        ->whereHas('approvals', function ($approvalQ) {
                                            // Ensure there's at least one 'Noted' approval
                                            $approvalQ->where('action', 'Noted');
                                        });
                                });
                            });
                        }
                    }
                });

            $badgeCount = $pendingCountQuery->count();
            $allRequests = $pendingCountQuery->with(['requester.employeeInfo', 'approvals'])->get();

            $debugInfo = [
                'user_type' => $userPosition,
                'user_id' => $userAccntId,
                'department_id' => $userDepartmentId,
                'access_role' => $userAccessRole,
                'badge_count' => $badgeCount,
                'total_requests' => $allRequests->count(),
                'requests' => $allRequests->map(function ($req) {
                    return [
                        'form_id' => $req->form_id,
                        'title' => $req->title,
                        'status' => $req->status,
                        'form_type' => $req->form_type,
                        'requested_by' => $req->requested_by,
                        'requester_name' => $req->requester->employeeInfo->FirstName . ' ' . $req->requester->employeeInfo->LastName,
                        'from_department_id' => $req->from_department_id,
                        'to_department_id' => $req->to_department_id,
                        'approvals_count' => $req->approvals->count(),
                        'has_noted_approval' => $req->approvals->where('action', 'Noted')->count() > 0
                    ];
                })
            ];
        }

        return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
    })->name('debug.notification-count');
});
