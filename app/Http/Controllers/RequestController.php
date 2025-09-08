<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use App\Models\IomDetail;
use App\Models\LeaveDetail;
use App\Models\Department;
use App\Models\User;
use App\Models\FormApproval;
use App\Models\JobOrder;
use App\Services\ApprovalCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Carbon\Carbon;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $requests = FormRequest::with(['iomDetails', 'leaveDetails', 'fromDepartment', 'toDepartment'])
            ->where('requested_by', $user->accnt_id)
            ->latest('date_submitted')
            ->paginate(10);

        return view('requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Check if user has pending feedback (for display purposes, not blocking)
        $hasPendingFeedback = JobOrder::userHasPendingFeedback($user->accnt_id);
        $pendingJobOrders = $hasPendingFeedback ? JobOrder::needingFeedbackForUser($user->accnt_id) : collect();
        $pendingFeedbackCount = $pendingJobOrders->count();

        // Get the oldest pending job order (first in chronological order)
        $oldestPendingJobOrder = $pendingJobOrders->sortBy('date_completed')->first();

        // Get all departments except Administration
        $departments = Department::where(function ($query) {
            $query->where('dept_name', '!=', 'Administration')
                ->where('dept_code', '!=', 'ADMIN');
        })->orderBy('dept_name')->get();

        // Pass old input to the view if available (e.g., after a validation error on confirmation page)
        $formData = session()->get('form_data_for_confirmation_edit', []);
        $todayPHT = now()->tz(config('app.timezone'))->toDateString(); // Get current date in PHT

        return view('requests.create', compact('departments', 'formData', 'todayPHT', 'hasPendingFeedback', 'pendingFeedbackCount', 'oldestPendingJobOrder'));
    }

    /**
     * Validate and temporarily store form data for confirmation.
     */
    public function submitForConfirmation(Request $request): RedirectResponse
    {
        $requestType = $request->input('request_type');
        $user = Auth::user();

        // Check for pending job order feedback - only block if 2 or more pending
        if (JobOrder::userHasPendingFeedback($user->accnt_id)) {
            $pendingCount = JobOrder::needingFeedbackForUser($user->accnt_id)->count();

            // Only block if there are 2 or more pending fillups
            if ($pendingCount >= 2) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "You cannot submit a new IOM request because you have $pendingCount completed job order(s) requiring fillup. Please complete the job order forms in your request tracking page before submitting new requests.")
                    ->with('dashboard_url', route('dashboard'));
            }
        }

        // Custom validation messages for better user experience
        $customMessages = [
            'iom_to_department_id.required' => 'Please select a department for your request.',
            'iom_to_department_id.integer' => 'The selected department is invalid.',
            'iom_to_department_id.exists' => 'The selected department does not exist in our records.',
        ];

        $allRules = [
            'request_type' => ['required', 'string', Rule::in(['IOM', 'Leave'])],
        ];

        if ($requestType === 'IOM') {
            $allRules = array_merge($allRules, [
                'iom_to_department_id' => [
                    'required',
                    'integer',
                    'exists:tb_department,department_id',
                    function ($attribute, $value, $fail) {
                        // Check if the department is Administration
                        $department = Department::find($value);
                        if ($department && ($department->dept_name === 'Administration' || $department->dept_code === 'ADMIN')) {
                            $fail('Please select a valid department. The Administration department is not available for IOM requests.');
                        }
                    },
                ],
                'iom_re' => 'required|string|max:255',
                'iom_priority' => ['required', Rule::in(['Routine', 'Urgent', 'Rush'])],
                'iom_purpose' => ['required', Rule::in(['For Information', 'For Action', 'For Signature', 'For Comments', 'For Approval', 'Request', 'Others'])],
                'iom_specific_request_type' => [
                    Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Request'),
                    'nullable',
                    'string',
                    'max:255',
                    Rule::in([
                        'Computer Repair',
                        'Network Issue',
                        'Software Support',
                        'Air Conditioning',
                        'Electrical Work',
                        'Plumbing',
                        'Building Maintenance',
                        'Cleaning Services',
                        'Security/Access',
                        'Keys/Locks',
                        'Request for Facilities',
                        'Request for Computer Laboratory',
                        'Request for Venue',
                        'Others'
                    ])
                ],
                'iom_other_purpose' => [
                    Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Others'),
                    'nullable',
                    'string',
                    'max:255'
                ],
                'iom_description' => 'required|string|max:5000',
                'iom_date_needed' => 'nullable|date|after_or_equal:today',
            ]);
        } elseif ($requestType === 'Leave') {
            $allRules = array_merge($allRules, [
                'leave_type' => ['required', Rule::in(['sick', 'vacation', 'emergency'])],
                'leave_start_date' => 'required|date|after_or_equal:today', // Ensure this is after_or_equal:today
                'leave_end_date' => 'required|date|after_or_equal:leave_start_date',
                'leave_days' => 'required|integer|min:1',
                'leave_description' => 'required|string|max:1000',
            ]);
        }
        // If requestType is invalid or empty, the 'request_type' rule in $allRules will catch it.

        $validatedData = $request->validate($allRules, $customMessages);

        // Flash all validated data to the session for the confirmation page
        session()->flash('form_data_for_confirmation', $validatedData);

        return redirect()->route('request.show_confirmation_page');
    }

    /**
     * Display the confirmation page with form data.
     */
    public function showConfirmationPage(): View|RedirectResponse
    {
        $formData = session('form_data_for_confirmation');

        if (!$formData) {
            return redirect()->route('request.create')
                ->with('error', 'No request data found. Please submit the form again.');
        }

        // Get the user's role and position
        $user = Auth::user();
        $isDepartmentHead = $user->accessRole === 'Approver' && $user->position === 'Head';

        // To display department names instead of IDs (excluding Administration)
        $departments = Department::where(function ($query) {
            $query->where('dept_name', '!=', 'Administration')
                ->where('dept_code', '!=', 'ADMIN');
        })->orderBy('dept_name')->get()->keyBy('department_id');
        $fromDepartmentName = $user->department ? $user->department->dept_name : 'N/A';

        // Re-flash the data for the next request
        session()->flash('form_data_for_confirmation', $formData);

        // If it's a Department Head submitting an IOM, use the special confirmation page
        if ($isDepartmentHead && $formData['request_type'] === 'IOM') {
            return view('requests.confirm-department-head-iom', compact('formData'));
        }

        return view('requests.confirm', compact('formData', 'departments', 'fromDepartmentName', 'user'));
    }

    /**
     * Handle user going back to edit form from confirmation page.
     */
    public function editBeforeConfirmation(): RedirectResponse
    {
        $formDataFromConfirmation = session()->get('form_data_for_confirmation');
        if ($formDataFromConfirmation) {
            session()->flash('form_data_for_confirmation_edit', $formDataFromConfirmation);
        }
        // Keep the original confirmation data in case user navigates back to confirm page via browser back button
        // The showConfirmationPage will re-flash it anyway if it exists.
        // No, explicitly remove it, so if they submit create form again, it generates fresh confirmation data.
        // session()->forget('form_data_for_confirmation');
        // Let's stick to re-flashing the main one from showConfirmationPage and edit one from here.
        // The main concern is that `form_data_for_confirmation` shouldn't persist if they start a *new* form from scratch later.
        // Flashing takes care of this (available for one next request).

        return redirect()->route('request.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('Store method called', ['request_data' => $request->all()]);

        $user = Auth::user();

        // Check for pending job order feedback - only block if 2 or more pending
        if (JobOrder::userHasPendingFeedback($user->accnt_id)) {
            $pendingCount = JobOrder::needingFeedbackForUser($user->accnt_id)->count();

            // Only block if there are 2 or more pending fillups
            if ($pendingCount >= 2) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "You cannot submit a new IOM request because you have $pendingCount completed job order(s) requiring fillup. Please complete the job order forms in your request tracking page before submitting new requests.")
                    ->with('dashboard_url', route('dashboard'));
            }
        }

        try {
            $validatedData = $request->validate([
                'request_type' => ['required', 'string', Rule::in(['IOM', 'Leave'])],
            ]);

            Log::info('Initial validation passed', ['request_type' => $validatedData['request_type']]);
        } catch (\Exception $e) {
            Log::error('Store method error', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            throw $e;
        }

        $requestType = $validatedData['request_type'];
        $fromDepartmentId = $user->department_id;

        try {
            DB::beginTransaction();

            $formRequest = new FormRequest();
            $formRequest->form_type = $requestType;
            $formRequest->requested_by = $user->accnt_id;
            $formRequest->from_department_id = $fromDepartmentId;

            if ($requestType === 'IOM') {
                $iomValidatedData = $request->validate([
                    'iom_to_department_id' => 'required|integer|exists:tb_department,department_id',
                    'iom_re' => 'required|string|max:255',
                    'iom_priority' => ['required', Rule::in(['Routine', 'Urgent', 'Rush'])],
                    'iom_purpose' => ['required', Rule::in(['For Information', 'For Action', 'For Signature', 'For Comments', 'For Approval', 'Request', 'Others'])],
                    'iom_specific_request_type' => [
                        Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Request'),
                        'nullable',
                        'string',
                        'max:255',
                        Rule::in([
                            'Computer Repair',
                            'Network Issue',
                            'Software Support',
                            'Air Conditioning',
                            'Electrical Work',
                            'Plumbing',
                            'Building Maintenance',
                            'Cleaning Services',
                            'Security/Access',
                            'Keys/Locks',
                            'Request for Facilities',
                            'Request for Computer Laboratory',
                            'Request for Venue',
                            'Others'
                        ])
                    ],
                    'iom_other_purpose' => [
                        Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Others'),
                        'nullable',
                        'string',
                        'max:255'
                    ],
                    'iom_description' => 'required|string|max:5000',
                    'iom_date_needed' => 'required|date|after_or_equal:today', // Changed from after:today
                ]);

                $formRequest->title = $iomValidatedData['iom_re'];
                $formRequest->to_department_id = $iomValidatedData['iom_to_department_id'];
                $formRequest->date_submitted = now();

                // Save the form request first to get the form_id
                $formRequest->save();

                // Create IOM details
                IomDetail::create([
                    'form_id' => $formRequest->form_id,
                    'date_needed' => $iomValidatedData['iom_date_needed'],
                    'priority' => $iomValidatedData['iom_priority'],
                    'purpose' => $this->formatIOMPurpose($iomValidatedData),
                    'body' => $iomValidatedData['iom_description'],
                ]);

                // Enhanced Auto-Assignment Logic for PFMO Workflow  
                $autoAssignmentResult = \App\Services\RequestTypeService::autoAssignDepartment(
                    $iomValidatedData['iom_re'],
                    $iomValidatedData['iom_description'],
                    $iomValidatedData['iom_specific_request_type'] ?? null,
                    $iomValidatedData['iom_purpose']
                );

                // If auto-assignment suggests a different department and user hasn't manually overridden
                if (
                    $autoAssignmentResult &&
                    $autoAssignmentResult['confidence_score'] >= 50 &&
                    $autoAssignmentResult['department']->department_id !== $iomValidatedData['iom_to_department_id']
                ) {

                    Log::info('[Auto-Assignment] Suggestion found but user chose different department', [
                        'form_id' => $formRequest->form_id,
                        'suggested_dept' => $autoAssignmentResult['department']->dept_name,
                        'suggested_confidence' => $autoAssignmentResult['confidence_score'],
                        'user_selected_dept_id' => $iomValidatedData['iom_to_department_id'],
                        'category' => $autoAssignmentResult['category']
                    ]);
                }

                // Store auto-assignment details for tracking and analytics
                if ($autoAssignmentResult) {
                    $formRequest->auto_assignment_details = json_encode([
                        'suggested_department' => $autoAssignmentResult['department']->dept_name,
                        'suggested_department_id' => $autoAssignmentResult['department']->department_id,
                        'confidence_score' => $autoAssignmentResult['confidence_score'],
                        'category' => $autoAssignmentResult['category'],
                        'was_auto_assigned' => $autoAssignmentResult['department']->department_id === $iomValidatedData['iom_to_department_id'],
                        'timestamp' => now()->toISOString()
                    ]);

                    // If it's a PFMO request, also determine sub-department
                    if ($autoAssignmentResult['department']->dept_code === 'PFMO') {
                        $subDepartmentAssignment = \App\Services\RequestTypeService::getPFMOSubDepartmentAssignment(
                            $iomValidatedData['iom_re'],
                            $iomValidatedData['iom_description']
                        );

                        if ($subDepartmentAssignment) {
                            $formRequest->assigned_sub_department = $subDepartmentAssignment['sub_department'];

                            Log::info('[PFMO Sub-Department Assignment]', [
                                'form_id' => $formRequest->form_id,
                                'sub_department' => $subDepartmentAssignment['name'],
                                'confidence' => $subDepartmentAssignment['confidence_score'],
                                'is_default' => $subDepartmentAssignment['is_default']
                            ]);
                        }
                    }

                    $formRequest->save();
                }

                // Handle routing based on user role - first get the user's department
                $userDepartment = Department::find($user->department_id);
                $isVPAAUser = $user->position === 'VPAA' && $userDepartment &&
                    (strtoupper($userDepartment->dept_code) === 'VPAA' ||
                        stripos($userDepartment->dept_name, 'Vice President for Academic Affairs') !== false);

                Log::info('[IOM Submission] User position and department check', [
                    'user_id' => $user->accnt_id,
                    'position' => $user->position,
                    'department_id' => $user->department_id,
                    'department_code' => $userDepartment ? $userDepartment->dept_code : 'Unknown',
                    'is_vpaa_user' => $isVPAAUser
                ]);

                if ($user->accessRole === 'Approver' && ($user->position === 'Head' || $isVPAAUser)) {
                    // Check if this is a VPAA user from VPAA department
                    $isVPAAUser = $user->position === 'VPAA' && $user->department && strtoupper($user->department->dept_code) === 'VPAA';

                    if ($isVPAAUser) {
                        Log::info('[VPAA Position IOM] Processing IOM submission by VPAA.', ['user_id' => $user->accnt_id, 'to_department_id' => $formRequest->to_department_id]);
                    } else {
                        Log::info('[Dept Head IOM] Processing IOM submission by Department Head.', ['user_id' => $user->accnt_id, 'to_department_id' => $formRequest->to_department_id]);
                    }
                    // Department Head is creating the request
                    if ($formRequest->to_department_id === $user->department_id) {
                        // If sending to own department, auto-approve
                        if ($isVPAAUser) {
                            Log::info('[VPAA Position IOM] Sending to own department. Auto-approving.', ['department_id' => $user->department_id]);
                        } else {
                            Log::info('[Dept Head IOM] Sending to own department. Auto-approving.', ['department_id' => $user->department_id]);
                        }
                        $formRequest->status = 'Approved';
                        $formRequest->current_approver_id = null;

                        // Create auto-approval record
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id,
                            'action' => 'Approved',
                            'action_date' => now()
                        ]);
                    } else {
                        // If sending to different department, auto-note and route to target
                        if ($isVPAAUser) {
                            Log::info('[VPAA Position IOM] Sending to a different department.', ['target_department_id' => $formRequest->to_department_id]);
                        } else {
                            Log::info('[Dept Head IOM] Sending to a different department.', ['target_department_id' => $formRequest->to_department_id]);
                        }
                        $vpaaDepartment = Department::where('dept_code', 'VPAA')
                            ->orWhere('dept_name', 'Vice President for Academic Affairs')
                            ->first();

                        $targetApprover = null;
                        Log::debug('[Dept Head IOM] Determining target approver.', [
                            'from_user_id' => $user->accnt_id,
                            'to_department_id' => $formRequest->to_department_id,
                            'vpaa_dept_obj_found' => !is_null($vpaaDepartment),
                            'vpaa_dept_id_if_found' => $vpaaDepartment ? $vpaaDepartment->department_id : null
                        ]);

                        if ($vpaaDepartment && $formRequest->to_department_id == $vpaaDepartment->department_id) {
                            Log::info('[Dept Head IOM] Target department is VPAA. Looking for user with VPAA position.', ['target_dept_id' => $formRequest->to_department_id]);
                            $targetApprover = User::where('department_id', $formRequest->to_department_id)
                                ->where('position', 'VPAA')
                                ->where('accessRole', 'Approver')
                                ->first();

                            if (!$targetApprover) {
                                Log::error('[Dept Head IOM] User with VPAA position not found in VPAA department.', ['target_dept_id' => $formRequest->to_department_id]);
                                DB::rollBack();
                                return redirect()->back()->with('error', 'User with VPAA position not found in the VPAA department. Request cannot be submitted.')->withInput();
                            }
                            Log::info('[Dept Head IOM] VPAA position user found for routing.', ['approver_id' => $targetApprover->accnt_id]);
                        } else {
                            Log::info('[Dept Head IOM] Target is not VPAA (or VPAA dept not identified as target). Looking for user with Head position.', ['target_dept_id' => $formRequest->to_department_id]);
                            $targetApprover = User::where('department_id', $formRequest->to_department_id)
                                ->where('position', 'Head')
                                ->where('accessRole', 'Approver')
                                ->first();

                            if (!$targetApprover) {
                                Log::error('[Dept Head IOM] User with Head position not found in target department.', ['target_dept_id' => $formRequest->to_department_id]);
                                DB::rollBack();
                                return redirect()->back()->with('error', 'Target department head not found. Request cannot be submitted.')->withInput();
                            }
                            Log::info('[Dept Head IOM] Head position user found for routing.', ['approver_id' => $targetApprover->accnt_id]);
                        }

                        // Set status to In Progress and route to target department
                        $formRequest->status = 'In Progress';
                        $formRequest->current_approver_id = $targetApprover->accnt_id;

                        // Create auto-approval record with signature and signature style
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id,
                            'action' => 'Approved',
                            'action_date' => now(),
                            'signature_data' => $request->signature ?? null,
                            'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName,
                            'signature_style_id' => $request->signatureStyle
                        ]);
                        if ($isVPAAUser) {
                            Log::info('[VPAA Position IOM] Successfully routed to target approver.', ['approver_id' => $targetApprover->accnt_id, 'status' => $formRequest->status]);
                        } else {
                            Log::info('[Dept Head IOM] Successfully routed to target approver.', ['approver_id' => $targetApprover->accnt_id, 'status' => $formRequest->status]);
                        }
                    }
                } else {
                    // Regular staff is creating the request
                    Log::info('[Regular Staff IOM] Processing IOM submission by regular staff.', [
                        'user_id' => $user->accnt_id,
                        'user_department_id' => $user->department_id,
                        'form_to_department_id' => $formRequest->to_department_id // This is the ultimate destination
                    ]);

                    // Check if user is from VPAA department
                    $userDepartment = Department::find($user->department_id);
                    $isVPAADepartment = $userDepartment && (
                        strtoupper($userDepartment->dept_code) === 'VPAA' ||
                        stripos($userDepartment->dept_name, 'Vice President for Academic Affairs') !== false
                    );

                    Log::info('[Regular Staff IOM] Checking department type for proper routing', [
                        'user_department_id' => $user->department_id,
                        'dept_code' => $userDepartment ? $userDepartment->dept_code : 'Unknown',
                        'is_vpaa_department' => $isVPAADepartment
                    ]);

                    // Staff's IOM goes to their department head (or VPAA if in VPAA department) for noting
                    $departmentHead = null;

                    if ($isVPAADepartment) {
                        // For VPAA department, look for VPAA position first
                        $departmentHead = User::where('department_id', $user->department_id)
                            ->where('position', 'VPAA')
                            ->where('accessRole', 'Approver')
                            ->first();

                        Log::info('[Regular Staff IOM] VPAA department - looking for VPAA position', [
                            'vpaa_position_found' => !is_null($departmentHead)
                        ]);
                    }

                    // If not found or not VPAA department, look for Head position
                    if (!$departmentHead) {
                        $departmentHead = User::where('department_id', $user->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        Log::info('[Regular Staff IOM] Looking for Head position', [
                            'head_position_found' => !is_null($departmentHead)
                        ]);
                    }

                    if (!$departmentHead) {
                        Log::error('[Regular Staff IOM] Department approver not found. Cannot initiate IOM.', [
                            'user_id' => $user->accnt_id,
                            'user_department_id' => $user->department_id,
                            'is_vpaa_department' => $isVPAADepartment
                        ]);
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Your department head could not be found. The request cannot be submitted. Please contact an administrator.')->withInput();
                    }

                    $formRequest->current_approver_id = $departmentHead->accnt_id;
                    $formRequest->status = 'Pending'; // Initial status, awaiting action from their own Head.

                    Log::info('[Regular Staff IOM] IOM routed to user\'s own department head for initial action.', [
                        'form_id' => $formRequest->form_id,
                        'current_approver_id' => $departmentHead->accnt_id,
                        'status' => $formRequest->status,
                        'final_target_department_id' => $formRequest->to_department_id // Log the final destination for clarity
                    ]);

                    // Create a "Submitted" record in FormApprovals by the requester
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id, // This is the staff member who created the request
                        'action' => 'Submitted',
                        'action_date' => now(),
                    ]);
                    Log::info('[Regular Staff IOM] "Submitted" record created for IOM.', ['form_id' => $formRequest->form_id, 'submitted_by_user_id' => $user->accnt_id]);
                }

                $formRequest->save();
                $successMessage = 'IOM Request submitted successfully!';

            } elseif ($requestType === 'Leave') {
                $leaveValidatedData = $request->validate([
                    'leave_type' => ['required', Rule::in(['sick', 'vacation', 'emergency'])],
                    'leave_start_date' => 'required|date|after_or_equal:today',
                    'leave_end_date' => 'required|date|after_or_equal:leave_start_date',
                    'leave_days' => 'required|integer|min:1',
                    'leave_description' => 'required|string|max:1000',
                ]);

                $formRequest->title = 'Leave Request - ' . ucfirst($leaveValidatedData['leave_type']);
                $formRequest->date_submitted = now();

                // Save the form request first to get the form_id
                $formRequest->save();

                // Create leave details
                LeaveDetail::create([
                    'form_id' => $formRequest->form_id,
                    'leave_type' => $leaveValidatedData['leave_type'],
                    'start_date' => $leaveValidatedData['leave_start_date'],
                    'end_date' => $leaveValidatedData['leave_end_date'],
                    'days' => $leaveValidatedData['leave_days'],
                    'description' => $leaveValidatedData['leave_description'],
                ]);

                // Get user department for VPAA check
                $userDepartment = Department::find($user->department_id);
                $isVPAAUser = $user->position === 'VPAA' && $userDepartment &&
                    (strtoupper($userDepartment->dept_code) === 'VPAA' ||
                        stripos($userDepartment->dept_name, 'Vice President for Academic Affairs') !== false);

                Log::info('[Leave Submission] User position and department check', [
                    'user_id' => $user->accnt_id,
                    'position' => $user->position,
                    'department_id' => $user->department_id,
                    'department_code' => $userDepartment ? $userDepartment->dept_code : 'Unknown',
                    'is_vpaa_user' => $isVPAAUser
                ]);

                if ($user->accessRole === 'Approver' && ($user->position === 'Head' || $isVPAAUser)) {
                    // Department Head is creating a Leave request.
                    // New logic: Route to VPAA first.
                    $vpaaDepartment = Department::where('dept_code', 'VPAA')
                        ->orWhere('dept_name', 'Vice President for Academic Affairs')
                        ->first();
                    $vpaaUser = null;
                    if ($vpaaDepartment) {
                        $vpaaUser = User::where('department_id', $vpaaDepartment->department_id)
                            ->where('position', 'VPAA')
                            ->where('accessRole', 'Approver')
                            ->first();
                    }

                    if ($vpaaUser) {
                        // VPAA found, route to VPAA
                        $formRequest->status = 'Pending'; // Pending VPAA "Approved"
                        $formRequest->current_approver_id = $vpaaUser->accnt_id;
                        $formRequest->to_department_id = $vpaaUser->department_id;

                        // Create 'Submitted' record for the requesting Department Head
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id, // The requesting Dept Head
                            'action' => 'Submitted',
                            'action_date' => now(),
                        ]);
                    } else {
                        // VPAA not found, fallback to HR
                        Log::warning('VPAA user or department not found for routing Department Head Leave request. Falling back to HR.', [
                            'form_id' => $formRequest->form_id,
                            'requesting_user_id' => $user->accnt_id
                        ]);

                        $hrDepartment = Department::where('dept_code', 'HR')
                            ->orWhere('dept_code', 'HRD')
                            ->orWhere('dept_code', 'HRMD')
                            ->orWhere('dept_name', 'like', '%Human Resource%')
                            ->first();

                        if (!$hrDepartment) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'HR Department not found for fallback routing. Please contact your administrator.')->withInput();
                        }

                        $hrApprover = User::where('department_id', $hrDepartment->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        if (!$hrApprover) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'HR Approver not found for fallback routing. Please contact your administrator.')->withInput();
                        }

                        // Set status and route to HR
                        $formRequest->status = 'In Progress'; // "In Progress" for HR
                        $formRequest->current_approver_id = $hrApprover->accnt_id;
                        $formRequest->to_department_id = $hrDepartment->department_id;

                        // Create auto-approval record for the requesting Department Head (self-approval) with signature style
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id,
                            'action' => 'Approved',
                            'action_date' => now(),
                            'signature_data' => $request->signature ?? null,
                            'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName,
                            'signature_style_id' => $request->input('signatureStyle')
                        ]);
                    }
                } else {
                    // Regular staff is creating the request
                    // Check if user is from VPAA department
                    $userDepartment = Department::find($user->department_id);
                    $isVPAADepartment = $userDepartment && (
                        strtoupper($userDepartment->dept_code) === 'VPAA' ||
                        stripos($userDepartment->dept_name, 'Vice President for Academic Affairs') !== false
                    );

                    Log::info('[Regular Staff Leave] Checking department type for proper routing', [
                        'user_department_id' => $user->department_id,
                        'dept_code' => $userDepartment ? $userDepartment->dept_code : 'Unknown',
                        'is_vpaa_department' => $isVPAADepartment
                    ]);

                    // Staff's Leave request goes to their department head (or VPAA if in VPAA department) for noting
                    $departmentHead = null;

                    if ($isVPAADepartment) {
                        // For VPAA department, look for VPAA position first
                        $departmentHead = User::where('department_id', $user->department_id)
                            ->where('position', 'VPAA')
                            ->where('accessRole', 'Approver')
                            ->first();

                        Log::info('[Regular Staff Leave] VPAA department - looking for VPAA position', [
                            'vpaa_position_found' => !is_null($departmentHead)
                        ]);
                    }

                    // If not found or not VPAA department, look for Head position
                    if (!$departmentHead) {
                        $departmentHead = User::where('department_id', $user->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        Log::info('[Regular Staff Leave] Looking for Head position', [
                            'head_position_found' => !is_null($departmentHead)
                        ]);
                    }

                    if (!$departmentHead) {
                        Log::error('[Regular Staff Leave] Department approver not found. Cannot initiate Leave request.', [
                            'user_id' => $user->accnt_id,
                            'user_department_id' => $user->department_id,
                            'is_vpaa_department' => $isVPAADepartment
                        ]);
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Your department head could not be found. Please contact your administrator.')->withInput();
                    }

                    // Set initial status and route to department head
                    $formRequest->status = 'Pending';
                    $formRequest->current_approver_id = $departmentHead->accnt_id;

                    // Create submission record
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id,
                        'action' => 'Submitted',
                        'action_date' => now()
                    ]);
                }

                $formRequest->save();
                $successMessage = 'Leave Request submitted successfully!';
            } else {
                DB::rollBack();
                return redirect()->back()->withErrors(['request_type' => 'Invalid request type selected.'])->withInput();
            }

            DB::commit();

            // Clear approval count caches since new request was submitted
            // This ensures approval badge counts are updated immediately
            ApprovalCacheService::clearAllApprovalCaches();

            return redirect()->route('dashboard')->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Form submission error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while submitting your request. Please try again. Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Track a specific request and show its details.
     */
    public function track($formId): View
    {
        $formRequest = FormRequest::with([
            'requester.department',
            'fromDepartment',
            'toDepartment',
            'iomDetails',
            'leaveDetails',
            'approvals.approver.employeeInfo',
            'currentApprover',
            'jobOrder.created_by_user.employeeInfo',
            'jobOrder.progressUpdates.updated_by_user' // Load progress updates with user info
        ])->findOrFail($formId);

        // Check if user has permission to view this request
        if (
            Auth::id() !== $formRequest->requested_by &&
            Auth::user()->accessRole !== 'Approver'
        ) {
            abort(403, 'You do not have permission to view this request.');
        }

        // Get signature styles for the feedback form
        $signatureStyles = \App\Models\SignatureStyle::all(['id', 'name', 'font_family']);

        return view('requests.track', compact('formRequest', 'signatureStyles'));
    }

    /**
     * Display a printable view of a completed request.
     */
    public function printView($formId): View
    {
        $formRequest = FormRequest::with([
            'requester.department',
            'fromDepartment',
            'toDepartment',
            'iomDetails',
            'leaveDetails',
            'approvals.approver.employeeInfo',
            'currentApprover'
        ])->findOrFail($formId);

        // Check if request is completed and user has permission to view
        if ($formRequest->status !== 'Approved') {
            abort(403, 'Only completed requests can be printed.');
        }

        if (
            Auth::id() !== $formRequest->requested_by &&
            Auth::user()->accessRole !== 'Approver'
        ) {
            abort(403, 'You do not have permission to view this request.');
        }

        return view('requests.print', compact('formRequest'));
    }

    private function formatIOMPurpose(array $data): string
    {
        $purpose = $data['iom_purpose'];
        if ($purpose === 'Request' && !empty($data['iom_specific_request_type'])) {
            $purpose .= ' - ' . $data['iom_specific_request_type'];
        } elseif ($purpose === 'Others' && !empty($data['iom_other_purpose'])) {
            $purpose .= ': ' . $data['iom_other_purpose'];
        }
        return $purpose;
    }

    /**
     * AJAX endpoint for auto-assignment of departments based on request content
     */
    public function autoAssign(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|min:3',
                'description' => 'required|string|min:3',
                'purpose' => 'nullable|string',
                'specific_request_type' => 'nullable|string'
            ]);

            // Use the RequestTypeService for auto-assignment
            $assignment = \App\Services\RequestTypeService::autoAssignDepartment(
                $request->input('title'),
                $request->input('description'),
                $request->input('specific_request_type'),
                $request->input('purpose')
            );

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No suitable department found for this request type.'
                ]);
            }

            $responseData = [
                'success' => true,
                'assignment' => [
                    'department_id' => $assignment['department']->department_id,
                    'department_name' => $assignment['department']->dept_name . ' (' . $assignment['department']->dept_code . ')',
                    'category' => $assignment['category'],
                    'confidence_score' => $assignment['confidence_score'],
                    'auto_assigned' => $assignment['auto_assigned']
                ]
            ];

            // If assigned to PFMO, get sub-department assignment
            if ($assignment['category'] === 'facility_maintenance') {
                $subDepartment = \App\Services\RequestTypeService::getPFMOSubDepartmentAssignment(
                    $request->input('title'),
                    $request->input('description')
                );

                if ($subDepartment) {
                    $responseData['assignment']['sub_department'] = [
                        'id' => $subDepartment['sub_department_id'],
                        'name' => $subDepartment['name'],
                        'code' => $subDepartment['code'],
                        'confidence_score' => $subDepartment['confidence_score']
                    ];
                }
            }

            return response()->json($responseData);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Auto-assignment error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during auto-assignment. Please try again.'
            ], 500);
        }
    }

    // ... (show, edit, update, destroy methods can be added later)
}
