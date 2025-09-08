<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View as ViewContract;
use App\Models\FormRequest;
use App\Models\EmployeeInfo;
use App\Models\Department;
use App\Models\IomDetail;
use App\Models\LeaveDetail;
use App\Models\JobOrder;
use App\Models\User;
use App\Services\AutomatedRoutingService;
use Carbon\Carbon;

class SecretaryController extends Controller
{
    /**
     * Display the secretary dashboard.
     */
    public function dashboard(Request $request): View
    {
        $user = Auth::user();
        
        // Ensure user is a secretary
        if (!$user->isSecretary()) {
            abort(403, 'Access denied. Secretary role required.');
        }

        $departmentName = $user->department ? $user->department->dept_name : 'N/A';

        // Get proxy requests submitted by this secretary
        $proxyRequests = FormRequest::where('requested_by', $user->accnt_id)
            ->where('is_proxy_submission', true)
            ->with(['fromDepartment', 'toDepartment', 'approvals'])
            ->orderBy('date_submitted', 'desc')
            ->take(10)
            ->get();

        // Get recent employees this secretary has submitted for
        $recentEmployees = FormRequest::where('requested_by', $user->accnt_id)
            ->where('is_proxy_submission', true)
            ->whereNotNull('actual_requestor_name')
            ->select('actual_requestor_name', 'actual_requestor_employee_id', 'actual_requestor_department')
            ->distinct()
            ->take(5)
            ->get();

        // Get department employees (for quick selection)
        $departmentEmployees = EmployeeInfo::whereHas('user', function($query) use ($user) {
            $query->where('department_id', $user->department_id)
                  ->where('position', '!=', 'Secretary'); // Exclude other secretaries
        })->get();

        // Request statistics
        $stats = [
            'total_proxy_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('is_proxy_submission', true)->count(),
            'pending_proxy_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('is_proxy_submission', true)
                ->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval', 'Pending Target Department Approval'])
                ->count(),
            'approved_this_month' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('is_proxy_submission', true)
                ->where('status', 'Approved')
                ->whereMonth('date_approved', Carbon::now()->month)
                ->count(),
            'employees_helped' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('is_proxy_submission', true)
                ->distinct('actual_requestor_employee_id')
                ->count()
        ];

        return view('secretary.dashboard', compact(
            'user', 
            'departmentName', 
            'proxyRequests', 
            'recentEmployees', 
            'departmentEmployees',
            'stats'
        ));
    }

    /**
     * Show the employee selection form for proxy submission.
     */
    public function selectEmployee(): View
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied.');
        }

        // Get all employees in secretary's department (excluding secretaries)
        $departmentEmployees = EmployeeInfo::whereHas('user', function($query) use ($user) {
            $query->where('department_id', $user->department_id)
                  ->where('position', '!=', 'Secretary');
        })->orderBy('LastName', 'asc')->orderBy('FirstName', 'asc')->get();

        return view('secretary.select-employee', compact('departmentEmployees'));
    }

    /**
     * Show proxy request creation form.
     */
    public function createProxyRequest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied. Secretary role required.');
        }

        $employeeId = $request->get('employee_id');
        $formType = $request->get('form_type');

        // Get employee information
        $employee = EmployeeInfo::with('user.department')->where('Emp_No', $employeeId)->first();
        
        if (!$employee) {
            return redirect()->route('secretary.select-employee')
                ->with('error', 'Employee not found.');
        }

        // Get available request types with routing information
        $requestTypes = AutomatedRoutingService::getAvailableRequestTypes();
        
        // Get automated routing for each request type
        $routingInfo = [];
        foreach (array_keys($requestTypes) as $type) {
            $routingInfo[$type] = AutomatedRoutingService::getDestinationForRequestType($type, $user->department_id);
        }

        return view('secretary.create-proxy-request', compact('employee', 'formType', 'requestTypes', 'routingInfo'));
    }

    /**
     * Store the proxy request.
     */
    public function storeProxyRequest(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied.');
        }

        // Validate the request
        $validator = validator($request->all(), [
            'form_type' => 'required|string|in:IOM,Leave,Job Order,Purchase Request,IT Support,Vehicle Request',
            'title' => 'required|string|max:255',
            'actual_requestor_employee_id' => 'required|string|exists:tb_employeeinfo,Emp_No',
            'actual_requestor_name' => 'required|string|max:255',
            'actual_requestor_department' => 'required|string|max:255',
            'actual_requestor_position' => 'required|string|max:255',
            
            // File attachments
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            
            // IOM specific fields
            'purpose' => 'required_if:form_type,IOM|string|max:1000',
            'date_needed' => 'required_if:form_type,IOM|date|after:today',
            'urgency' => 'required_if:form_type,IOM|string|in:Low,Medium,High,Urgent',
            
            // Leave specific fields
            'leave_type' => 'required_if:form_type,Leave|string|in:Annual,Sick,Emergency,Maternity,Paternity,Bereavement,Personal',
            'leave_from' => 'required_if:form_type,Leave|date',
            'leave_to' => 'required_if:form_type,Leave|date|after_or_equal:leave_from',
            'leave_days' => 'required_if:form_type,Leave|integer|min:1',
            'leave_reason' => 'required_if:form_type,Leave|string|max:500',
            
            // Job Order specific fields
            'service_type' => 'required_if:form_type,Job Order|string|max:255',
            'location' => 'required_if:form_type,Job Order|string|max:255',
            'equipment_needed' => 'nullable|string|max:500',
            'completion_date' => 'required_if:form_type,Job Order|date|after:today',
            'special_requirements' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();
        $user = Auth::user();

        try {
            // Get automated routing information
            $routingInfo = AutomatedRoutingService::getDestinationForRequestType(
                $validatedData['form_type'], 
                $user->department_id
            );

            // Find the department head for initial approval
            $departmentHead = User::where('department_id', $user->department_id)
                ->where('position', 'Head')
                ->where('accessRole', 'Approver')
                ->first();

        try {
            // Find the department head for approval routing
            $departmentHead = User::where('department_id', $user->department_id)
                ->where('position', 'Head')
                ->where('accessRole', 'Approver')
                ->first();

            // Create the main form request with automated routing
            $formRequest = FormRequest::create([
                'form_type' => $validatedData['form_type'],
                'title' => $validatedData['title'],
                'from_department_id' => $user->department_id,
                'to_department_id' => $routingInfo['department_id'],
                'requested_by' => $user->accnt_id,
                'current_approver_id' => $departmentHead ? $departmentHead->accnt_id : $routingInfo['approver_id'],
                'status' => 'Pending',
                'date_submitted' => now(),
                'actual_requestor_name' => $validatedData['actual_requestor_name'],
                'actual_requestor_employee_id' => $validatedData['actual_requestor_employee_id'],
                'actual_requestor_department' => $validatedData['actual_requestor_department'],
                'actual_requestor_position' => $validatedData['actual_requestor_position'],
                'is_proxy_submission' => true,
                'sub_status' => $departmentHead ? 'Awaiting Department Head Approval' : 
                              ($routingInfo['has_approver'] ? 'Awaiting ' . $routingInfo['department_name'] . ' Approval' : 'No Approver Available'),
            ]);

            // Create form-specific details
            if ($validatedData['form_type'] === 'IOM') {
                IomDetail::create([
                    'form_id' => $formRequest->form_id,
                    'iom_type' => $validatedData['iom_type'],
                    'description' => $validatedData['description'],
                ]);
            } elseif ($validatedData['form_type'] === 'Leave') {
                LeaveDetail::create([
                    'form_id' => $formRequest->form_id,
                    'leave_type' => $validatedData['leave_type'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'reason' => $validatedData['reason'],
                ]);
            } elseif ($validatedData['form_type'] === 'Job Order') {
                // Map job_type to appropriate boolean fields
                $jobFields = [
                    'form_id' => $formRequest->form_id,
                    'requestor_name' => $validatedData['actual_requestor_name'],
                    'department' => $validatedData['actual_requestor_department'],
                    'request_description' => $validatedData['work_description'],
                    'status' => 'Pending',
                ];

                // Set appropriate boolean field based on job type
                switch ($validatedData['job_type']) {
                    case 'Repair':
                        $jobFields['repair_repaint'] = true;
                        break;
                    case 'Installation':
                        $jobFields['installation'] = true;
                        break;
                    case 'Cleaning':
                        $jobFields['cleaning'] = true;
                        break;
                    case 'Maintenance':
                        $jobFields['check_up_inspection'] = true;
                        break;
                    case 'Electrical':
                    case 'Plumbing':
                    case 'Other':
                        $jobFields['assistance'] = true;
                        break;
                    default:
                        $jobFields['assistance'] = true;
                }

                JobOrder::create($jobFields);
            }

            // Handle file attachments (to be implemented later)
            if ($request->hasFile('attachments')) {
                // File handling logic will be added in next iteration
            }

            return redirect()->route('secretary.dashboard')
                ->with('success', 'Proxy request submitted successfully for ' . $validatedData['actual_requestor_name']);

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to submit request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get employees via API for AJAX requests.
     */
    public function getEmployees(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $employees = EmployeeInfo::whereHas('user', function($query) use ($user) {
            $query->where('department_id', $user->department_id)
                  ->where('position', '!=', 'Secretary');
        })->with(['user.department'])
        ->orderBy('LastName', 'asc')
        ->orderBy('FirstName', 'asc')
        ->get();

        return response()->json($employees);
    }

    /**
     * Get recent employees via API.
     */
    public function getRecentEmployees(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $recentEmployees = FormRequest::where('requested_by', $user->accnt_id)
            ->where('is_proxy_submission', true)
            ->whereNotNull('actual_requestor_name')
            ->select('actual_requestor_name', 'actual_requestor_employee_id', 'actual_requestor_department')
            ->distinct()
            ->orderBy('date_submitted', 'desc')
            ->take(10)
            ->get();

        return response()->json($recentEmployees);
    }
}
