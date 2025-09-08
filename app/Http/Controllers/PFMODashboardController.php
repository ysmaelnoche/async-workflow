<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormRequest;
use App\Models\JobOrder;
use App\Models\User;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PFMODashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has access to PFMO
        if (!$user->can('access-pfmo')) {
            abort(403, 'Unauthorized access to PFMO dashboard.');
        }

        // Get dashboard statistics
        $totalRequests = FormRequest::where('currentApprover', 'PFMO Head')->count();
        $pendingRequests = FormRequest::where('currentApprover', 'PFMO Head')
            ->where('currentStatus', 'Pending')->count();
        $approvedRequests = FormRequest::where('currentApprover', 'PFMO Head')
            ->where('currentStatus', 'Approved')->count();
        
        // Get recent requests
        $recentRequests = FormRequest::where('currentApprover', 'PFMO Head')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get job order statistics
        $totalJobOrders = JobOrder::count();
        $activeJobOrders = JobOrder::where('status', 'In Progress')->count();
        $completedJobOrders = JobOrder::where('status', 'Completed')->count();
        
        // Get sub-department statistics
        $subDepartments = SubDepartment::withCount(['employees'])->get();
        $totalEmployees = User::whereHas('department', function($query) {
            $query->where('dept_code', 'PFMO');
        })->count();
        
        // Get supervisors count
        $totalSupervisors = SubDepartment::whereNotNull('supervisor_id')->count();

        return view('pfmo.dashboard-professional', compact(
            'totalRequests',
            'pendingRequests', 
            'approvedRequests',
            'recentRequests',
            'totalJobOrders',
            'activeJobOrders',
            'completedJobOrders',
            'subDepartments',
            'totalEmployees',
            'totalSupervisors'
        ));
    }
}
