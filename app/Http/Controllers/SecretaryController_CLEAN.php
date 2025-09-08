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

class SecretaryController extends Controller
{
    /**
     * Display the Secretary dashboard.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied. Secretary role required.');
        }

        // Get statistics for the dashboard
        $stats = [
            'total_requests' => FormRequest::where('requested_by', $user->accnt_id)->count(),
            'pending_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('status', 'Pending')->count(),
            'approved_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('status', 'Approved')->count(),
            'proxy_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('is_proxy_submission', true)->count(),
        ];

        // Get recent requests
        $recentRequests = FormRequest::where('requested_by', $user->accnt_id)
            ->with(['fromDepartment', 'toDepartment'])
            ->orderBy('date_submitted', 'desc')
            ->limit(5)
            ->get();

        // Get employees in same department for quick access
        $recentEmployees = EmployeeInfo::whereHas('user', function($query) use ($user) {
            $query->where('department_id', $user->department_id)
                  ->where('position', '!=', 'Secretary');
        })->with(['user.department'])
        ->orderBy('LastName', 'asc')
        ->limit(5)
        ->get();

        return view('secretary.dashboard', compact('stats', 'recentRequests', 'recentEmployees'));
    }

    /**
     * Show employee selection form.
     */
    public function selectEmployee(): View
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied. Secretary role required.');
        }

        return view('secretary.select-employee');
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
     * Store a new proxy request.
     */
    public function storeProxyRequest(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied. Secretary role required.');
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

            // Create form-specific details based on request type
            if ($validatedData['form_type'] === 'IOM') {
                IomDetail::create([
                    'form_id' => $formRequest->form_id,
                    'iom_type' => $validatedData['urgency'] ?? 'Medium',
                    'description' => $validatedData['purpose'],
                ]);
            } elseif ($validatedData['form_type'] === 'Leave') {
                LeaveDetail::create([
                    'form_id' => $formRequest->form_id,
                    'leave_type' => $validatedData['leave_type'],
                    'start_date' => $validatedData['leave_from'],
                    'end_date' => $validatedData['leave_to'],
                    'reason' => $validatedData['leave_reason'],
                ]);
            } elseif ($validatedData['form_type'] === 'Job Order') {
                // Map service type to appropriate boolean fields
                $serviceType = strtolower($validatedData['service_type']);
                $jobFields = [
                    'form_id' => $formRequest->form_id,
                    'maintenance' => str_contains($serviceType, 'maintenance'),
                    'repair' => str_contains($serviceType, 'repair'),
                    'installation' => str_contains($serviceType, 'install'),
                    'cleaning' => str_contains($serviceType, 'clean'),
                    'inspection' => str_contains($serviceType, 'inspect'),
                    'other_job_type' => !str_contains($serviceType, 'maintenance') && !str_contains($serviceType, 'repair') && 
                                       !str_contains($serviceType, 'install') && !str_contains($serviceType, 'clean') && 
                                       !str_contains($serviceType, 'inspect'),
                    'location' => $validatedData['location'],
                    'description' => $validatedData['special_requirements'] ?? 'Job order request via Secretary',
                ];
                
                JobOrder::create($jobFields);
            }

            // Handle file attachments if present
            if ($request->hasFile('attachments')) {
                $this->handleFileAttachments($request->file('attachments'), $formRequest->form_id);
            }

            return redirect()->route('secretary.dashboard')
                ->with('success', 'Proxy request submitted successfully! Request routed to ' . $routingInfo['department_name'] . ' for ' . $validatedData['actual_requestor_name']);

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to submit request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Handle file attachments for requests.
     */
    private function handleFileAttachments($files, $formId)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('request_attachments', $fileName, 'public');
                
                // Note: In a full implementation, you would create an Attachment model
                // to store file information in the database
            }
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

        // Get employees from recent requests
        $recentEmployeeIds = FormRequest::where('requested_by', $user->accnt_id)
            ->where('is_proxy_submission', true)
            ->orderBy('date_submitted', 'desc')
            ->limit(10)
            ->pluck('actual_requestor_employee_id')
            ->unique();

        $employees = EmployeeInfo::whereIn('Emp_No', $recentEmployeeIds)
            ->with(['user.department'])
            ->get();

        return response()->json($employees);
    }
}
