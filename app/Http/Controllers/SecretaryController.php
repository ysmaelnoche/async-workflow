<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View as ViewContract;
use App\Models\FormRequest;
use App\Models\FormApproval;
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

        // Get requests submitted by this secretary (recent ones)
        $proxyRequests = FormRequest::where('requested_by', $user->accnt_id)
            ->with(['fromDepartment', 'toDepartment'])
            ->orderBy('date_submitted', 'desc')
            ->take(10)
            ->get();

        // Get recent employees this secretary has submitted for (from IOM details)
        $recentEmployees = collect(); // Empty for now, will be populated when we have more data

        // Get department employees (for quick selection)
        $departmentEmployees = EmployeeInfo::whereHas('user', function($query) use ($user) {
            $query->where('department_id', $user->department_id)
                  ->where('position', '!=', 'Secretary'); // Exclude other secretaries
        })->get();

        // Request statistics (using existing columns only)
        $stats = [
            'total_proxy_requests' => FormRequest::where('requested_by', $user->accnt_id)->count(),
            'pending_proxy_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval', 'Pending Target Department Approval'])
                ->count(),
            'in_progress_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('status', 'In Progress')
                ->count(),
            'approved_this_month' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('status', 'Approved')
                ->whereMonth('updated_at', Carbon::now()->month)
                ->count(),
            'rejected_requests' => FormRequest::where('requested_by', $user->accnt_id)
                ->where('status', 'Rejected')
                ->count(),
            'employees_helped' => $departmentEmployees->count() // Number of employees in department
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

        // Get all departments for the target department dropdown (ensure unique)
        $departments = Department::distinct()->orderBy('dept_name')->get();

        // Get department employees for the employee dropdown and JavaScript
        $departmentEmployees = EmployeeInfo::whereHas('user', function($query) use ($user) {
            $query->where('department_id', $user->department_id)
                  ->where('position', '!=', 'Secretary');
        })->with(['user.department'])
        ->orderBy('LastName', 'asc')
        ->orderBy('FirstName', 'asc')
        ->get();

        // Get available request types with routing information
        $requestTypes = [
            'Vehicle Request' => 'Request for vehicle use or transportation',
            'Aircondition Repair' => 'AC unit repair and maintenance',
            'Electrical Repair' => 'Electrical system repairs and installations',
            'Plumbing Repair' => 'Plumbing system repairs and maintenance',
            'Carpentry Work' => 'Carpentry and woodwork services',
            'Cleaning Service' => 'Cleaning and sanitation services',
            'Equipment Maintenance' => 'Maintenance of equipment and facilities',
            'Facility Booking' => 'Booking of facilities and venues',
            'General Maintenance' => 'General maintenance and repairs',
            'Other PFMO Service' => 'Other services provided by PFMO'
        ];
        
        // Get automated routing for each request type
        $routingInfo = [];
        foreach (array_keys($requestTypes) as $type) {
            $routingInfo[$type] = [
                'department_name' => 'PFMO',
                'description' => $requestTypes[$type]
            ];
        }

        // Pass current user information to the view with employee details
        $currentUser = $user;
        
        // Try multiple approaches to get secretary's employee info
        $currentUserEmployee = null;
        
        // First try: Match by accnt_id (account ID) with Emp_No
        if ($user->accnt_id) {
            $currentUserEmployee = EmployeeInfo::where('Emp_No', $user->accnt_id)->first();
        }
        
        // Second try: Match by username with Emp_No (if accnt_id doesn't work)
        if (!$currentUserEmployee && $user->username) {
            $currentUserEmployee = EmployeeInfo::where('Emp_No', $user->username)->first();
        }
        
        // Third try: Find by department and position
        if (!$currentUserEmployee && $user->department_id) {
            $currentUserEmployee = EmployeeInfo::whereHas('user', function($query) use ($user) {
                $query->where('department_id', $user->department_id)
                      ->where('position', 'Secretary');
            })->first();
        }
        
        return view('secretary.create-proxy-request', compact('employee', 'formType', 'requestTypes', 'routingInfo', 'departments', 'departmentEmployees', 'currentUser', 'currentUserEmployee'));
    }

    /**
     * Store the proxy request.
     */
    public function storeProxyRequest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSecretary()) {
            abort(403, 'Access denied.');
        }

        // Log the incoming request data for debugging
        \Log::info('Secretary form submission data:', $request->all());

        // Validate the request with correct field names
        $validator = validator($request->all(), [
            'employee_id' => 'required|string|exists:tb_account,accnt_id',
            'requested_by' => 'required|string|max:255',
            'request_type' => 'required|string|in:Vehicle Request,Aircondition Repair,Electrical Repair,Plumbing Repair,Carpentry Work,Cleaning Service,Equipment Maintenance,Facility Booking,General Maintenance,Other PFMO Service',
            'title' => 'required|string|max:255',
            'priority' => 'required|string|in:Routine,Rush,Urgent',
            'purpose' => 'required|string|in:For Action,For Your Information,Request,For Approval,For Compliance,For Review,For Implementation,For Records',
            'body' => 'required|string|min:10',
            'date_needed' => 'required|date|after:today',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ], [
            'employee_id.required' => 'Employee selection is required.',
            'employee_id.exists' => 'Selected employee does not exist.',
            'title.required' => 'The request title field is required.',
            'priority.required' => 'The priority field is required.',
            'purpose.required' => 'The purpose field is required.',
            'body.required' => 'The detailed description field is required.',
            'body.min' => 'The detailed description must be at least 10 characters.',
            'date_needed.required' => 'The date needed field is required.',
            'date_needed.after' => 'The date needed must be a future date.',
        ]);

        if ($validator->fails()) {
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();

        try {
            // Get employee information
            $employee = User::with('employeeInfo')->where('accnt_id', $validatedData['employee_id'])->first();
            
            if (!$employee) {
                return back()
                    ->with('error', 'Selected employee not found.')
                    ->withInput();
            }

            // Get PFMO department ID
            $pfmoDepartment = Department::where('dept_name', 'LIKE', '%PFMO%')
                ->orWhere('dept_name', 'LIKE', '%Physical Facilities%')
                ->orWhere('dept_name', 'LIKE', '%Maintenance%')
                ->first();
            
            if (!$pfmoDepartment) {
                // Fallback to department ID 1 or create a default
                $pfmoDepartment = Department::first(); // Use first available department as fallback
            }

            // Create the main form request
            $formRequest = FormRequest::create([
                'form_type' => 'IOM', // PFMO requests are typically IOM type
                'title' => $validatedData['title'],
                'from_department_id' => $user->department_id,
                'to_department_id' => $pfmoDepartment->department_id,
                'requested_by' => $user->accnt_id, // Secretary is the one submitting
                'status' => 'Pending', // Use valid ENUM value
                'date_submitted' => now(),
            ]);

            // Create IOM details for PFMO request with proxy information
            IomDetail::create([
                'form_id' => $formRequest->form_id,
                'body' => $validatedData['body'] . "\n\n[PROXY SUBMISSION] This request was submitted by Secretary " . 
                    ($user->name ?? $user->username) . " on behalf of " . $validatedData['requested_by'] . 
                    " (Employee ID: " . $validatedData['employee_id'] . ")",
                'purpose' => $validatedData['purpose'],
                'priority' => $validatedData['priority'],
                'date_needed' => $validatedData['date_needed']
            ]);

            // Note: We don't create FormApproval record yet since we don't have an approver_id
            // The approval workflow will be handled when the department head reviews the request

            // Initialize PFMO workflow if this is a PFMO request
            if (in_array($validatedData['request_type'], [
                'Vehicle Request', 'Aircondition Repair', 'Electrical Repair', 
                'Plumbing Repair', 'Carpentry Work', 'Cleaning Service', 
                'Equipment Maintenance', 'Facility Booking', 'General Maintenance', 
                'Other PFMO Service'
            ])) {
                // Use PFMOWorkflowService to initialize the workflow
                \App\Services\PFMOWorkflowService::initializePFMOWorkflow(
                    $formRequest->form_id, 
                    $validatedData['request_type']
                );

                // Send notification to Department Head
                try {
                    // Note: Email notification temporarily disabled
                    \Log::info("PFMO request '{$validatedData['title']}' submitted by Secretary and requires department head approval.");
                } catch (\Exception $e) {
                    \Log::warning("Email notification skipped: " . $e->getMessage());
                }
            }

            // Handle file attachments if present
            if ($request->hasFile('attachments')) {
                // File handling logic would go here
                // For now, we'll skip this part
            }

            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PFMO request submitted successfully for ' . $validatedData['requested_by'] . '!',
                    'redirect_url' => route('secretary.dashboard')
                ]);
            }

            return redirect()->route('secretary.dashboard')
                ->with('success', 'PFMO request submitted successfully for ' . $validatedData['requested_by'] . '!');

        } catch (\Exception $e) {
            \Log::error('Secretary form submission error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Line: ' . $e->getLine());
            \Log::error('File: ' . $e->getFile());
            
            // Check if this is an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit request: ' . $e->getMessage(),
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()
                ->with('error', 'Failed to submit request: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Validate request type and target department compatibility
     */
    private function validateRequestTypeDepartmentCompatibility($requestType, $targetDepartmentId): bool
    {
        // Get department information
        $department = Department::find($targetDepartmentId);
        if (!$department) return false;

        $deptName = strtolower($department->dept_name);
        $deptCode = strtolower($department->dept_code ?? '');

        // Define allowed departments for PFMO service types
        $allowedDepartments = ['pfmo', 'physical facilities'];
        
        // Check if the target department is in the allowed list
        foreach ($allowedDepartments as $allowed) {
            if (str_contains($deptName, $allowed) || str_contains($deptCode, $allowed)) {
                return true;
            }
        }

        return false;
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
