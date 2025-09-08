@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&family=Pacifico&family=Great+Vibes&family=Allura&family=Alex+Brush&family=Satisfy&family=Kaushan+Script&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

<style>
    .signature-preview {
        transition: all 0.2s ease;
    }
    .signature-preview:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .signature-preview.selected {
        border-color: #3b82f6 !important;
        background-color: #dbeafe !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
    }
    #signatureCanvas {
        touch-action: none;
    }
</style>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Create PFMO Request</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Submit a Physical Facilities Management Office request on behalf of 
                        @if($employee)
                            <span class="font-semibold text-blue-600">{{ $employee->FirstName }} {{ $employee->LastName }}</span>
                        @else
                            an employee
                        @endif
                    </p>
                    
                    <!-- Enhanced Notification Demo Section -->
                </div>

                <form id="proxy-request-form" action="{{ route('secretary.store-proxy-request') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Request Submitted Successfully!</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        {{ session('success') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Error Messages - Enhanced Display --}}
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Error
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        {{ session('error') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Validation Errors - Enhanced Display --}}
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        There {{ $errors->count() == 1 ? 'was' : 'were' }} {{ $errors->count() }} error{{ $errors->count() == 1 ? '' : 's' }} with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul role="list" class="list-disc space-y-1 pl-5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    {{-- Debug Information (only show if errors exist) --}}
                    @if($errors->any() && config('app.debug'))
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Debug Information</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p><strong>Old Input:</strong></p>
                                        <pre class="text-xs bg-yellow-100 p-2 rounded mt-1">{{ json_encode(old(), JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Employee Selection -->
                    <div class="mb-6">
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Employee <span class="text-red-500">*</span>
                        </label>
                        <select id="employee_id" name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('employee_id') border-red-500 @enderror">
                            <option value="">Select employee above to auto-fill</option>
                            @if(isset($departmentEmployees))
                                @foreach($departmentEmployees as $emp)
                                    <option value="{{ $emp->user->accnt_id }}" 
                                            data-name="{{ $emp->FirstName }} {{ $emp->LastName }}"
                                            {{ old('employee_id') == $emp->user->accnt_id ? 'selected' : '' }}
                                            @if(isset($employee) && $employee->Emp_No == $emp->Emp_No) selected @endif>
                                        {{ $emp->FirstName }} {{ $emp->LastName }} - {{ $emp->user->department->dept_name ?? 'No Department' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('employee_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-xs text-gray-500">Select the employee on whose behalf you are submitting this request</p>
                        @enderror
                    </div>
                    
                    <!-- Request Preparation Details -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-md">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Request Authentication</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Requested By (Staff Member) -->
                            <div>
                                <label for="requested_by" class="block text-sm font-medium text-gray-700 mb-2">
                                    Requested By (Staff Member) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="requested_by" name="requested_by" required
                                       class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Select employee above to auto-fill"
                                       readonly>
                                <p class="mt-1 text-xs text-gray-500">Auto-filled when employee is selected</p>
                            </div>
                            
                            <!-- Encoded By (Auto-filled for Secretary) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Encoded By <span class="text-red-500">*</span>
                                </label>
                                @php
                                    $secretaryName = 'Secretary';
                                    if ($currentUserEmployee && $currentUserEmployee->FirstName && $currentUserEmployee->LastName) {
                                        $secretaryName = $currentUserEmployee->FirstName . ' ' . $currentUserEmployee->LastName;
                                    } elseif ($currentUser && $currentUser->name) {
                                        $secretaryName = $currentUser->name;
                                    } elseif ($currentUser && $currentUser->username) {
                                        $secretaryName = $currentUser->username;
                                    }
                                @endphp
                                <input type="text" value="Secretary - {{ $secretaryName }}" 
                                       class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm"
                                       readonly>
                                <input type="hidden" name="encoded_by_role" value="secretary">
                                <p class="mt-1 text-xs text-gray-500">Automatically set for Secretary account</p>
                            </div>
                        </div>
                        
                        <!-- Encoder Details (Auto-filled) -->
                        <div id="encoder-details" class="mt-4" style="display: none;">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="encoded_by_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Encoder Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="encoded_by_name" name="encoded_by_name"
                                           class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Auto-filled based on role selection"
                                           readonly>
                                    <p class="mt-1 text-xs text-gray-500">Automatically set based on your role</p>
                                </div>
                                
                                <div>
                                    <label for="encoder_pin" class="block text-sm font-medium text-gray-700 mb-2">
                                        Personal PIN <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" id="encoder_pin" name="encoder_pin" maxlength="6"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('encoder_pin') border-red-500 @enderror"
                                           placeholder="Enter your 4-6 digit PIN" value="{{ old('encoder_pin') }}">
                                    @error('encoder_pin')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @else
                                        <p class="mt-1 text-xs text-gray-500">Personal PIN to verify your identity</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Authority Check -->
                            <div id="authority-check" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md" style="display: none;">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-blue-800">Authorization Level</h4>
                                        <div class="mt-1 text-sm text-blue-700">
                                            <p id="authority-message">Authority level will be checked based on request priority and encoder role.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Type Selection - Must match database enum -->
                    <div class="mb-6">
                        <label for="form_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Request Category <span class="text-red-500">*</span>
                        </label>
                        <select id="form_type" name="form_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Request Type</option>
                            <option value="IOM" {{ old('form_type') == 'IOM' || old('form_type') == '' ? 'selected' : '' }}>Internal Office Memorandum (IOM)</option>
                            <option value="Leave" {{ old('form_type') == 'Leave' ? 'selected' : '' }}>Leave Request</option>
                            <option value="Budget Slip" {{ old('form_type') == 'Budget Slip' ? 'selected' : '' }}>Budget Slip</option>
                            <option value="Vehicle Reservation and Trip Ticket" {{ old('form_type') == 'Vehicle Reservation and Trip Ticket' ? 'selected' : '' }}>Vehicle Reservation and Trip Ticket</option>
                            <option value="Loan" {{ old('form_type') == 'Loan' ? 'selected' : '' }}>Loan Request</option>
                            <option value="Job Orders" {{ old('form_type') == 'Job Orders' ? 'selected' : '' }}>Job Orders</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">All requests will be routed to Physical Facilities Management Office</p>
                    </div>

                    <!-- PFMO Request Type -->
                    <div class="mb-6">
                        <label for="request_type" class="block text-sm font-medium text-gray-700 mb-2">
                            PFMO Service Type <span class="text-red-500">*</span>
                        </label>
                        <select id="request_type" name="request_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Service Type</option>
                            <option value="Vehicle Request" {{ old('request_type') == 'Vehicle Request' ? 'selected' : '' }}>Vehicle Request</option>
                            <option value="Aircondition Repair" {{ old('request_type') == 'Aircondition Repair' ? 'selected' : '' }}>Aircondition Repair</option>
                            <option value="Electrical Repair" {{ old('request_type') == 'Electrical Repair' ? 'selected' : '' }}>Electrical Repair</option>
                            <option value="Plumbing Repair" {{ old('request_type') == 'Plumbing Repair' ? 'selected' : '' }}>Plumbing Repair</option>
                            <option value="Carpentry Work" {{ old('request_type') == 'Carpentry Work' ? 'selected' : '' }}>Carpentry Work</option>
                            <option value="Cleaning Service" {{ old('request_type') == 'Cleaning Service' ? 'selected' : '' }}>Cleaning Service</option>
                            <option value="Equipment Maintenance" {{ old('request_type') == 'Equipment Maintenance' ? 'selected' : '' }}>Equipment Maintenance</option>
                            <option value="Facility Booking" {{ old('request_type') == 'Facility Booking' ? 'selected' : '' }}>Facility Booking</option>
                            <option value="General Maintenance" {{ old('request_type') == 'General Maintenance' ? 'selected' : '' }}>General Maintenance</option>
                            <option value="Other PFMO Service" {{ old('request_type') == 'Other PFMO Service' ? 'selected' : '' }}>Other PFMO Service</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Select the specific PFMO service you need</p>
                    </div>

                    <!-- Target Department -->
                    <div class="mb-6">
                        <label for="target_department" class="block text-sm font-medium text-gray-700 mb-2">
                            Target Department <span class="text-red-500">*</span>
                        </label>
                        <select id="target_department" name="target_department"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('target_department') border-red-500 @enderror">
                            <option value="">Select Target Department</option>
                            @if(isset($departments))
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->department_id }}" 
                                            data-code="{{ $dept->dept_code ?? '' }}"
                                            data-name="{{ $dept->dept_name }}"
                                            {{ old('target_department') == $dept->department_id ? 'selected' : '' }}>
                                        {{ $dept->dept_name }} @if($dept->dept_code)({{ $dept->dept_code }})@endif
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('target_department')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @else
                            <p id="target_department_help" class="mt-1 text-xs text-gray-500">The department that will handle this request (auto-filled based on request type)</p>
                        @enderror
                    </div>

                    <!-- Request Title -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Request Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" required maxlength="255"
                               value="{{ old('title') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter a brief title for this request">
                    </div>

                    <!-- IOM Details Section -->
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Request Details</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Date Needed -->
                            <div>
                                <label for="date_needed" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date Needed <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="date_needed" name="date_needed"
                                       value="{{ old('date_needed', date('Y-m-d', strtotime('+3 days'))) }}"
                                       min="{{ date('Y-m-d', strtotime('tomorrow')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Priority (must match database enum) -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                    Priority Level <span class="text-red-500">*</span>
                                </label>
                                <select id="priority" name="priority" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Priority</option>
                                    <option value="Routine" {{ old('priority') == 'Routine' ? 'selected' : '' }}>Routine</option>
                                    <option value="Rush" {{ old('priority') == 'Rush' ? 'selected' : '' }}>Rush</option>
                                    <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Purpose (IOM dropdown options) -->
                        <div class="mb-6">
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                                Purpose <span class="text-red-500">*</span>
                            </label>
                            <select id="purpose" name="purpose"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Purpose</option>
                                <option value="For Action" {{ old('purpose') == 'For Action' ? 'selected' : '' }}>For Action</option>
                                <option value="For Your Information" {{ old('purpose') == 'For Your Information' ? 'selected' : '' }}>For Your Information</option>
                                <option value="Request" {{ old('purpose') == 'Request' ? 'selected' : '' }}>Request</option>
                                <option value="For Approval" {{ old('purpose') == 'For Approval' ? 'selected' : '' }}>For Approval</option>
                                <option value="For Compliance" {{ old('purpose') == 'For Compliance' ? 'selected' : '' }}>For Compliance</option>
                                <option value="For Review" {{ old('purpose') == 'For Review' ? 'selected' : '' }}>For Review</option>
                                <option value="For Implementation" {{ old('purpose') == 'For Implementation' ? 'selected' : '' }}>For Implementation</option>
                                <option value="For Records" {{ old('purpose') == 'For Records' ? 'selected' : '' }}>For Records</option>
                            </select>
                        </div>

                        <!-- Body (detailed description) -->
                        <div class="mb-6">
                            <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                                Detailed Description <span class="text-red-500">*</span>
                            </label>
                            <textarea id="body" name="body" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Provide detailed description of the request including location/room if needed">{{ old('body') }}</textarea>
                        </div>
                    </div>

                    <!-- PFMO Workflow Information -->
                    <div id="routing-info" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">PFMO Approval Workflow</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p id="routing-description">This request will follow the department-based approval workflow before reaching PFMO for processing.</p>
                                    <div id="workflow-path" class="mt-2 p-2 bg-white rounded border text-xs">
                                        <span class="font-medium">Workflow Path:</span>
                                        <span id="workflow-steps">Will be determined based on requestor's department</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PFMO Request Fields (Static IOM fields already defined above) -->
                    <div id="form-specific-fields" style="display: none;">
                        <!-- Dynamic fields disabled - using static IOM fields above -->
                    </div>

                    <!-- Attachments -->
                    <div class="mb-6">
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                            Attachments (Optional)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload files</span>
                                        <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    PNG, JPG, PDF up to 10MB each
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Preview PFMO Request
                        </button>
                        <button type="button" id="submitRequestBtn"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Submit PFMO Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Signature Modal (Dean Encoding) -->
    <div id="signatureModal" class="fixed inset-0 bg-black bg-opacity-50 hidden h-full w-full z-50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="w-[640px] max-w-2xl shadow-2xl rounded-2xl bg-white dark:bg-gray-800 transform transition-all duration-300 mx-auto my-auto p-0 flex flex-col justify-center items-center">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-8 pt-8 pb-6 rounded-t-2xl w-full flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Dean Digital Signature Required
                </h3>
                <button type="button" onclick="closeSignatureModal()" class="text-white hover:text-gray-200 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-8 w-full">
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Dean Approval Required:</strong> As the Dean, your digital signature will automatically approve this request and route it to PFMO for processing.
                    </p>
                </div>

                <!-- Signature Tabs -->
                <div class="mb-6">
                    <div class="flex space-x-4 border-b border-gray-200">
                        <button type="button" id="textSignatureTab" 
                                class="py-2 px-4 text-sm font-medium border-b-2 border-blue-500 text-blue-600 active">
                            📝 Text Signature
                        </button>
                        <button type="button" id="drawSignatureTab" 
                                class="py-2 px-4 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-300">
                            🎨 Draw Signature
                        </button>
                    </div>
                </div>

                <!-- Text Signature Method -->
                <div id="textSignatureMethod">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Signature Style:</label>
                        <div id="signatureStyles" class="max-h-48 overflow-y-auto space-y-2">
                            <!-- Signature styles will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Draw Signature Method -->
                <div id="drawSignatureMethod" class="hidden">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-3">Draw your signature in the canvas below</p>
                            <canvas id="signatureCanvas" 
                                    width="400" 
                                    height="100" 
                                    class="border-2 border-gray-300 rounded-xl bg-white mx-auto cursor-crosshair shadow-lg"
                                    style="touch-action: none;">
                                Your browser does not support canvas
                            </canvas>
                            <div class="mt-3 flex justify-center space-x-2">
                                <button type="button" onclick="clearCanvas()" class="px-4 py-2 text-sm bg-gray-500 text-white rounded-lg hover:bg-gray-600">Clear</button>
                                <button type="button" onclick="undoCanvas()" class="px-4 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600">Undo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for signature data -->
                <input type="hidden" id="signatureData" name="signature_data">
                <input type="hidden" id="signatureStyle" name="signature_style" value="none">
                
                <!-- Signature validation error display -->
                @error('signature_data')
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    </div>
                @enderror

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeSignatureModal()"
                            class="px-6 py-2 text-base font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all duration-200">
                        Cancel
                    </button>
                    <button type="button" onclick="submitWithSignature()"
                            class="px-6 py-2 text-base font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-all duration-200">
                        ✍️ Sign & Submit Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 PFMO MULTI-DEPARTMENT WORKFLOW LOADED');
    
    // Get form elements
    const formType = document.getElementById('form_type');
    const employeeSelect = document.getElementById('employee_id');
    const requestContainer = document.getElementById('request-type-container');
    const requestTypeSelect = document.getElementById('request_type');
    const fieldsContainer = document.getElementById('form-specific-fields');
    const routingInfo = document.getElementById('routing-info');
    const routingDescription = document.getElementById('routing-description');
    const workflowSteps = document.getElementById('workflow-steps');
    
    // Department data from backend
    const departmentEmployees = @json($departmentEmployees);
    
    // PFMO service type descriptions
    const pfmoServiceDescriptions = {
        'Vehicle Request': 'Vehicle Request - PFMO will coordinate vehicle allocation and scheduling',
        'Aircondition Repair': 'Aircondition Repair - PFMO maintenance team will assess and repair AC units',
        'Electrical Repair': 'Electrical Repair - PFMO electricians will handle electrical maintenance and repairs',
        'Plumbing Repair': 'Plumbing Repair - PFMO plumbing team will address water and sewage issues',
        'Carpentry Work': 'Carpentry Work - PFMO carpenters will handle woodwork and structural repairs',
        'Cleaning Service': 'Cleaning Service - PFMO janitorial team will provide specialized cleaning services',
        'Equipment Maintenance': 'Equipment Maintenance - PFMO will service and maintain institutional equipment',
        'Facility Booking': 'Facility Booking - PFMO will manage room and facility reservations',
        'General Maintenance': 'General Maintenance - PFMO will handle general facility maintenance tasks',
        'Other PFMO Service': 'Other PFMO Service - PFMO will review and assign appropriate personnel'
    };

    // Target department auto-population and filtering based on request type
    const targetDepartmentSelect = document.getElementById('target_department');
    const targetDepartmentHelp = document.getElementById('target_department_help');
    
    // Define which departments are allowed for each request type
    const allowedDepartmentsByRequestType = {
        'Vehicle Request': ['PFMO', 'Physical Facilities'],
        'Aircondition Repair': ['PFMO', 'Physical Facilities'],
        'Electrical Repair': ['PFMO', 'Physical Facilities'], 
        'Plumbing Repair': ['PFMO', 'Physical Facilities'],
        'Carpentry Work': ['PFMO', 'Physical Facilities'],
        'Cleaning Service': ['PFMO', 'Physical Facilities'],
        'Equipment Maintenance': ['PFMO', 'Physical Facilities'],
        'Facility Booking': ['PFMO', 'Physical Facilities'],
        'General Maintenance': ['PFMO', 'Physical Facilities'],
        'Other PFMO Service': ['PFMO', 'Physical Facilities']
    };
    
    function updateTargetDepartmentOptions() {
        const requestType = requestTypeSelect?.value;
        
        if (!requestType || !targetDepartmentSelect) return;
        
        // Get allowed departments for this request type
        const allowedDepartments = allowedDepartmentsByRequestType[requestType] || [];
        
        // Show/hide options based on allowed departments
        const options = targetDepartmentSelect.querySelectorAll('option');
        let pfmoOption = null;
        let validOptionsCount = 0;
        
        options.forEach(option => {
            if (option.value === '') {
                // Keep empty option visible
                option.style.display = '';
                option.disabled = false;
                return;
            }
            
            const deptName = option.textContent.toLowerCase();
            const deptCode = option.dataset.code?.toLowerCase() || '';
            
            // Check if this department is allowed for the current request type
            const isAllowed = allowedDepartments.length === 0 || allowedDepartments.some(allowed => 
                deptName.includes(allowed.toLowerCase()) || 
                deptCode.includes(allowed.toLowerCase())
            );
            
            if (isAllowed) {
                option.style.display = '';
                option.disabled = false;
                validOptionsCount++;
                // Remember PFMO option for auto-selection
                if (deptCode === 'pfmo' || deptName.includes('pfmo') || deptName.includes('physical facilities')) {
                    pfmoOption = option;
                }
            } else {
                option.style.display = 'none';
                option.disabled = true;
            }
        });
        
        // Auto-select PFMO if it's available and nothing is selected
        if (pfmoOption && (!targetDepartmentSelect.value || targetDepartmentSelect.selectedOptions[0]?.disabled)) {
            targetDepartmentSelect.value = pfmoOption.value;
            targetDepartmentSelect.dispatchEvent(new Event('change'));
            console.log('🎯 Auto-selected PFMO for:', requestType);
        }
        
        // Update the help text to reflect restrictions
        if (targetDepartmentHelp && allowedDepartments.length > 0) {
            targetDepartmentHelp.textContent = `This ${requestType} request can only be sent to: ${allowedDepartments.join(', ')} (${validOptionsCount} option${validOptionsCount !== 1 ? 's' : ''} available)`;
        }
    }
    
    function resetTargetDepartmentOptions() {
        if (!targetDepartmentSelect) return;
        
        // Show all options when no request type is selected
        const options = targetDepartmentSelect.querySelectorAll('option');
        options.forEach(option => {
            option.style.display = '';
            option.disabled = false;
        });
        
        // Reset help text
        if (targetDepartmentHelp) {
            targetDepartmentHelp.textContent = 'The department that will handle this request (auto-filled based on request type)';
        }
    }

    // Add event listener for request type changes
    if (requestTypeSelect) {
        requestTypeSelect.addEventListener('change', function() {
            console.log('🎯 PFMO Service type changed to:', this.value);
            
            if (this.value) {
                console.log('✅ Service type selected:', this.value);
                updateTargetDepartmentOptions();
                
                // Update routing information with service-specific description
                if (pfmoServiceDescriptions[this.value] && routingDescription) {
                    routingDescription.textContent = pfmoServiceDescriptions[this.value];
                }
                
                updateWorkflowPath();
            } else {
                console.log('❌ No service type selected');
                resetTargetDepartmentOptions();
                if (routingDescription) {
                    routingDescription.textContent = 'This request will follow the department-based approval workflow before reaching PFMO for processing.';
                }
            }
        });
    }

    // Prevent manual selection of restricted departments
    if (targetDepartmentSelect) {
        targetDepartmentSelect.addEventListener('change', function(e) {
            const selectedOption = this.selectedOptions[0];
            
            // If user tries to select a disabled/hidden option, prevent it
            if (selectedOption && (selectedOption.disabled || selectedOption.style.display === 'none')) {
                e.preventDefault();
                
                // Reset to a valid option
                const validOptions = Array.from(this.options).filter(opt => 
                    opt.value !== '' && !opt.disabled && opt.style.display !== 'none'
                );
                
                if (validOptions.length > 0) {
                    this.value = validOptions[0].value;
                    
                    // Show warning message
                    alert('This department is not available for the selected request type. Please choose from the available options.');
                }
            }
        });
        
        // Make the select searchable by allowing typing
        targetDepartmentSelect.addEventListener('keydown', function(e) {
            // Allow normal dropdown behavior but also enable search on typing
            if (e.key.length === 1 && e.key.match(/[a-zA-Z0-9\s]/)) {
                // This is a character key, don't prevent default
                setTimeout(() => {
                    // After the character is typed, filter options
                    this.dispatchEvent(new Event('input'));
                }, 0);
            }
        });
        
        // Add search functionality when typing in the select
        let searchTerm = '';
        let searchTimeout;
        
        targetDepartmentSelect.addEventListener('keydown', function(e) {
            if (e.key.length === 1 && e.key.match(/[a-zA-Z0-9\s]/)) {
                searchTerm += e.key.toLowerCase();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Find matching option
                const options = Array.from(this.options);
                const matchingOption = options.find(option => {
                    if (option.disabled || option.style.display === 'none') return false;
                    return option.textContent.toLowerCase().includes(searchTerm) ||
                           option.dataset.code?.toLowerCase().includes(searchTerm);
                });
                
                if (matchingOption) {
                    this.value = matchingOption.value;
                    this.dispatchEvent(new Event('change'));
                }
                
                // Reset search term after 1 second
                searchTimeout = setTimeout(() => {
                    searchTerm = '';
                }, 1000);
            } else if (e.key === 'Backspace') {
                searchTerm = searchTerm.slice(0, -1);
            } else if (e.key === 'Escape') {
                searchTerm = '';
            }
        });
    }
    
    // Initialize on page load
    if (requestTypeSelect?.value) {
        updateTargetDepartmentOptions();
    }
    
    console.log('🔍 Elements found:', {
        formType: !!formType,
        employeeSelect: !!employeeSelect,
        requestContainer: !!requestContainer,
        requestTypeSelect: !!requestTypeSelect,
        fieldsContainer: !!fieldsContainer,
        routingInfo: !!routingInfo
    });
    
    // Employee selection change handler - Update workflow path
    if (employeeSelect) {
        console.log('🎯 Employee select found, setting up change handler');
        
        employeeSelect.addEventListener('change', function() {
            console.log('🔄 Employee changed to:', this.value);
            // Auto-fill requested_by field
            const selectedOption = this.options[this.selectedIndex];
            const requestedByField = document.getElementById('requested_by');
            
            console.log('📝 Selected option:', selectedOption);
            console.log('📄 Requested by field:', requestedByField);
            console.log('🔍 Value check - this.value:', this.value);
            console.log('🔍 Value check - Boolean check:', !!this.value);
            console.log('🔍 Value check - Length:', this.value ? this.value.length : 'N/A');
            console.log('🔍 Selected index:', this.selectedIndex);
            console.log('🔍 Selected option text:', selectedOption ? selectedOption.text : 'No option');
            
            // Change logic: check if we have a valid selection (not the first empty option)
            if (this.selectedIndex > 0 && selectedOption) {
                console.log('✅✅✅ ENTERING AUTO-FILL LOGIC (by index check) ✅✅✅');
                // Use data-name attribute for cleaner name
                const dataName = selectedOption.getAttribute('data-name');
                const textName = selectedOption.text.split(' - ')[0];
                const employeeName = dataName || textName;
                
                console.log('🏷️ Data-name attribute:', dataName);
                console.log('� Text-based name:', textName);
                console.log('�👤 Final employee name to fill:', employeeName);
                
                if (requestedByField) {
                    requestedByField.value = employeeName;
                    console.log('✅ Field updated with value:', requestedByField.value);
                } else {
                    console.error('❌ requested_by field not found!');
                }
            } else {
                console.log('❌ No valid selection (selectedIndex <= 0 or no option)');
                if (requestedByField) {
                    requestedByField.value = '';
                }
            }
            
            updateWorkflowPath();
        });
        
        // Initialize auto-fill if employee is already selected on page load
        console.log('🚀 Checking for pre-selected employee - selectedIndex:', employeeSelect.selectedIndex);
        if (employeeSelect.selectedIndex > 0) {
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            const requestedByField = document.getElementById('requested_by');
            console.log('🎯 Pre-selected option:', selectedOption);
            console.log('📄 Pre-load requested by field:', requestedByField);
            
            if (selectedOption && requestedByField) {
                const dataName = selectedOption.getAttribute('data-name');
                const textName = selectedOption.text.split(' - ')[0];
                const employeeName = dataName || textName;
                
                console.log('🏷️ Pre-load Data-name:', dataName);
                console.log('📝 Pre-load Text-name:', textName);
                console.log('👤 Pre-filling with final name:', employeeName);
                
                requestedByField.value = employeeName;
                console.log('✅ Pre-fill completed with value:', requestedByField.value);
            }
            updateWorkflowPath();
        }
    } else {
        console.error('❌ Employee select element not found!');
    }
    
    // Encoder role selection handling with auto-fill
    const encodedByRoleSelect = document.getElementById('encoded_by_role');
    if (encodedByRoleSelect) {
        encodedByRoleSelect.addEventListener('change', function() {
            const encoderDetailsDiv = document.getElementById('encoder-details');
            const authorityCheckDiv = document.getElementById('authority-check');
            const encoderNameField = document.getElementById('encoded_by_name');
            
            if (this.value) {
                encoderDetailsDiv.style.display = 'block';
                authorityCheckDiv.style.display = 'block';
                
                // Auto-fill encoder name based on role
                autoFillEncoderName(this.value);
                updateAuthorityCheck();
            } else {
                encoderDetailsDiv.style.display = 'none';
                authorityCheckDiv.style.display = 'none';
                encoderNameField.value = '';
            }
        });
    }
    
    // Priority change handling for authority check
    const prioritySelect = document.getElementById('priority');
    if (prioritySelect) {
        prioritySelect.addEventListener('change', function() {
            updateAuthorityCheck();
        });
    }
    
    // Submit button handling - Check if Dean signature is needed
    const submitBtn = document.getElementById('submitRequestBtn');
    console.log('Submit button found:', submitBtn);
    
    // Also prevent any direct form submission
    const form = document.getElementById('proxy-request-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('🔥 Form submit event triggered - preventing default');
            e.preventDefault();
            return false;
        });
    }
    
    if (submitBtn) {
        console.log('Adding click event listener to submit button');
        submitBtn.addEventListener('click', function(e) {
            console.log('🔥 Submit button clicked!');
            e.preventDefault();
            console.log('🔥 Default prevented');
            
            try {
                // Check if form is valid first
                const form = document.getElementById('proxy-request-form');
                console.log('🔥 Form found:', form);
                
                // Check all required fields before validation
                const requiredFields = form.querySelectorAll('[required]');
                console.log('🔥 Required fields count:', requiredFields.length);
                
                // Log empty required fields (but exclude hidden ones)
                const emptyRequired = [];
                requiredFields.forEach(field => {
                    // Skip validation for hidden fields or fields in hidden containers
                    if (field.style.display === 'none' || 
                        field.closest('[style*="display: none"]') ||
                        field.closest('.hidden') ||
                        field.offsetParent === null) {
                        console.log(`🔥 Skipping hidden field: ${field.name || field.id}`);
                        field.removeAttribute('required'); // Temporarily remove required
                        return;
                    }
                    
                    if (!field.value || field.value.trim() === '') {
                        emptyRequired.push(field.name || field.id);
                        console.log(`🔥 Empty required field: ${field.name || field.id}`);
                    }
                });
                
                if (!form.checkValidity()) {
                    console.log('🔥 Form validation failed');
                    console.log('🔥 Empty required fields:', emptyRequired);
                    
                    // Find which fields are invalid
                    const invalidFields = form.querySelectorAll(':invalid');
                    console.log('🔥 Invalid fields:', invalidFields);
                    invalidFields.forEach(field => {
                        console.log(`🔥 Invalid field: ${field.name || field.id} - ${field.validationMessage}`);
                    });
                    
                    // Show user-friendly error message
                    if (emptyRequired.length > 0) {
                        showErrorToast(`Please fill in all required fields: ${emptyRequired.join(', ')}`);
                    } else {
                        showErrorToast('Please check the form for validation errors');
                    }
                    
                    form.reportValidity();
                    return;
                }
                
                console.log('🔥 Form validation passed');
                
                const encoderRole = document.getElementById('encoded_by_role')?.value;
                console.log('🔥 Encoder role:', encoderRole);
                
                if (encoderRole === 'dean') {
                    console.log('🔥 Dean encoding - opening signature modal');
                    // Dean encoding - show signature modal
                    openSignatureModal();
                } else {
                    console.log('🔥 Secretary encoding - calling submitFormWithErrorHandling');
                    // Secretary encoding - submit with error handling
                    submitFormWithErrorHandling(form, submitBtn);
                }
            } catch (error) {
                console.error('🔥 Error in submit handler:', error);
            }
        });
    } else {
        console.error('❌ Submit button not found! ID: submitRequestBtn');
    }

    // Submit form with proper error handling
    async function submitFormWithErrorHandling(form, button) {
        console.log('🚀 submitFormWithErrorHandling called');
        try {
            console.log('🚀 Starting form submission...');
            
            // Show loading state
            showSubmitLoading(button);
            console.log('🚀 Loading state shown');
            
            // Create FormData from the form
            const formData = new FormData(form);
            
            // Log form data for debugging
            console.log('Form action:', form.action);
            console.log('Form data entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}:`, value);
            }
            
            // Submit using fetch to catch errors
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (contentType && contentType.includes('application/json')) {
                const jsonResult = await response.json();
                console.log('JSON Response:', jsonResult);
                
                if (jsonResult.success === false) {
                    // Handle JSON validation errors
                    const errorMessage = jsonResult.message || jsonResult.error || (jsonResult.errors ? jsonResult.errors.join('. ') : 'Validation failed');
                    console.log('JSON validation error detected:', errorMessage);
                    showErrorToast(errorMessage);
                    resetSubmitButton(button);
                    return;
                } else if (jsonResult.success === true) {
                    // Handle JSON success
                    resetSubmitButton(button);
                    showSuccessNotification('Request submitted successfully! Your proxy request has been submitted for approval.');
                    proxyForm.reset();
                    const requestContainer = document.getElementById('request-type-container');
                    if (requestContainer) {
                        requestContainer.style.display = 'none';
                    }
                    const workflowSteps = document.getElementById('workflow-steps');
                    if (workflowSteps) {
                        workflowSteps.textContent = 'Select employee and service type to see workflow path';
                    }
                    return;
                }
            }
            
            if (response.ok) {
                const result = await response.text();
                console.log('Response text:', result.substring(0, 200) + '...');
                
                // Check if the response contains Laravel error indicators
                if (result.includes('validation-errors') || 
                    result.includes('alert-danger') || 
                    result.includes('error') ||
                    result.includes('failed') ||
                    result.includes('withErrors') ||
                    result.includes('Form validation failed') ||
                    result.includes('is-invalid') ||
                    result.includes('invalid-feedback')) {
                    
                    // Parse error from Laravel response
                    let errorMessage = 'Submission failed - please check your input';
                    
                    // Try to extract specific error message
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(result, 'text/html');
                    
                    // Look for Laravel validation errors first
                    const validationErrors = [];
                    
                    // Check for blade error directives
                    const bladeErrors = doc.querySelectorAll('[class*="text-red"], [class*="error"], .invalid-feedback');
                    bladeErrors.forEach(errorEl => {
                        const text = errorEl.textContent.trim();
                        if (text && !text.includes('*') && text.length > 2) {
                            validationErrors.push(text);
                        }
                    });
                    
                    // Check for alert messages
                    const alertErrors = doc.querySelectorAll('.alert-danger, .alert-error');
                    alertErrors.forEach(alertEl => {
                        const text = alertEl.textContent.trim();
                        if (text && !text.includes('*') && text.length > 2) {
                            validationErrors.push(text);
                        }
                    });
                    
                    // If we found specific validation errors, use them
                    if (validationErrors.length > 0) {
                        errorMessage = validationErrors.join('. ');
                    } else {
                        // Fallback: look for any error-related text
                        const fallbackErrors = doc.querySelectorAll('li, p, div, span');
                        const errorTexts = [];
                        
                        fallbackErrors.forEach(el => {
                            const text = el.textContent.trim();
                            // Look for validation-related keywords
                            if (text && (
                                text.includes('required') || 
                                text.includes('field') || 
                                text.includes('must') || 
                                text.includes('invalid') ||
                                text.includes('error')
                            ) && !text.includes('*') && text.length > 5 && text.length < 200) {
                                errorTexts.push(text);
                            }
                        });
                        
                        if (errorTexts.length > 0) {
                            errorMessage = errorTexts.slice(0, 3).join('. '); // Take first 3 errors
                        }
                    }
                    
                    console.log('Validation error detected:', errorMessage);
                    showErrorToast(errorMessage);
                    resetSubmitButton(button);
                    return;
                }
                
                // Check for success indicators
                if (result.includes('success') || 
                    result.includes('submitted successfully') ||
                    result.includes('alert-success')) {
                    
                    // Reset button state
                    resetSubmitButton(button);
                    
                    // Show success notification
                    showSuccessNotification('Request submitted successfully! Your proxy request has been submitted for approval.');
                    
                    // Clear the form after successful submission
                    form.reset();
                    
                    // Reset any dynamic content
                    const requestContainer = document.getElementById('request-type-container');
                    if (requestContainer) {
                        requestContainer.style.display = 'none';
                    }
                    
                    // Reset workflow steps
                    const workflowSteps = document.getElementById('workflow-steps');
                    if (workflowSteps) {
                        workflowSteps.textContent = 'Select employee and service type to see workflow path';
                    }
                } else {
                    // No clear success indicator found
                    console.log('Unclear response - treating as error');
                    showErrorToast('Unexpected response from server. Please check if your request was submitted.');
                    resetSubmitButton(button);
                }
                
            } else {
                // Handle HTTP errors (including 422 validation errors)
                const errorText = await response.text();
                console.log('Error response:', errorText.substring(0, 500));
                
                let errorMessage = 'Submission failed';
                
                // Handle 422 validation errors specifically
                if (response.status === 422) {
                    try {
                        const errorData = JSON.parse(errorText);
                        if (errorData.errors && Array.isArray(errorData.errors)) {
                            errorMessage = errorData.errors.join('. ');
                        } else if (errorData.errors && typeof errorData.errors === 'object') {
                            errorMessage = Object.values(errorData.errors).flat().join('. ');
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        errorMessage = 'Validation failed. Please check your form fields.';
                    }
                } else {
                    // Handle other error types (500, etc.)
                    console.log('HTTP Error Status:', response.status);
                    console.log('Raw error text:', errorText.substring(0, 1000));
                    
                    try {
                        const errorData = JSON.parse(errorText);
                        console.log('Parsed error data:', errorData);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        } else if (errorData.error) {
                            errorMessage = errorData.error;
                        } else if (errorData.errors) {
                            errorMessage = Object.values(errorData.errors).flat().join(', ');
                        } else {
                            errorMessage = `Server error (${response.status}): Please check the console for details`;
                        }
                    } catch (e) {
                        console.log('Failed to parse JSON, trying to extract HTML error');
                        console.log('Parse error:', e.message);
                        
                        // If not JSON, extract Laravel error from HTML
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(errorText, 'text/html');
                        
                        // Try multiple selectors for Laravel error messages
                        const errorSelectors = [
                            '.exception-message',
                            '.error-message', 
                            'h1',
                            '.title',
                            '.exception-title',
                            '.whoops-container h1',
                            '.exception .message'
                        ];
                        
                        let foundError = false;
                        for (const selector of errorSelectors) {
                            const errorElement = doc.querySelector(selector);
                            if (errorElement && errorElement.textContent.trim()) {
                                errorMessage = errorElement.textContent.trim();
                                foundError = true;
                                console.log(`Found error using selector ${selector}:`, errorMessage);
                                break;
                            }
                        }
                        
                        if (!foundError) {
                            // Extract from title if available
                            const titleElement = doc.querySelector('title');
                            if (titleElement && titleElement.textContent.includes('Error')) {
                                errorMessage = titleElement.textContent.trim();
                                console.log('Found error in title:', errorMessage);
                            } else {
                                errorMessage = `Server error (${response.status}). Check browser console for full details.`;
                            }
                        }
                        
                        // Log more details for debugging
                        console.log('Full error HTML (first 1500 chars):', errorText.substring(0, 1500));
                    }
                }
                
                console.log('Parsed error message:', errorMessage);
                showErrorToast(errorMessage);
                resetSubmitButton(button);
            }
            
        } catch (error) {
            console.error('Submission error:', error);
            showErrorToast(`Network error: ${error.message}`);
            resetSubmitButton(button);
        }
    }

    // Reset submit button to original state
    function resetSubmitButton(button) {
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Submit Request
        `;
    }

    // Show error toast with detailed message
    function showErrorToast(message) {
        // Create error toast element
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'transform 0.3s ease-in-out';
        
        toast.innerHTML = `
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-white flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <div class="font-medium text-sm">Submission Failed</div>
                    <div class="text-xs mt-1 opacity-90">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto remove after 8 seconds (longer for error messages)
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 8000);
    }

    // Show success notification
    function showSuccessNotification(message) {
        // Try to use enhanced notification system if available
        if (window.EnhancedNotifications) {
            window.EnhancedNotifications.show({
                type: 'success',
                title: 'Success!',
                message: message,
                duration: 5000
            });
        } else {
            // Fallback to simple toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-md';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'transform 0.3s ease-in-out';
            
            toast.innerHTML = `
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-white flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <div class="font-medium text-sm">Success!</div>
                        <div class="text-xs mt-1 opacity-90">${message}</div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }
    }
    
    // Show loading state on submit
    function showSubmitLoading(button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
        `;
        
        // Reset button if form submission fails (fallback)
        setTimeout(() => {
            if (button.disabled) {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }, 10000); // 10 second timeout
    }
    
    // Auto-fill encoder name based on role selection
    function autoFillEncoderName(role) {
        const encoderNameField = document.getElementById('encoded_by_name');
        
        // Get current department info from user
        const departmentName = '{{ $currentUser->department->dept_name ?? "Department" }}';
        
        if (role === 'dean') {
            // Auto-fill with department-specific dean name
            const deanName = getDeanNameForDepartment(departmentName);
            encoderNameField.value = deanName;
        } else if (role === 'secretary') {
            // Auto-fill with department-specific secretary name  
            const secretaryName = getSecretaryNameForDepartment(departmentName);
            encoderNameField.value = secretaryName;
        }
    }
    
    // Get dean name for specific department
    function getDeanNameForDepartment(departmentName) {
        const deanMapping = {
            'College of Computer Studies': 'Dean Regie Ellana',
            'College of Engineering': 'Dr. John Rodriguez',
            'College of Business': 'Dr. Ana Dela Cruz',
            'College of Arts and Sciences': 'Dr. Michael Johnson',
            'College of Education': 'Dr. Sarah Williams',
            // Add more mappings as needed
        };
        
        return deanMapping[departmentName] || 'Dean'; // Default dean name
    }
    
    // Get secretary name for specific department
    function getSecretaryNameForDepartment(departmentName) {
        // Use the actual logged-in user's full name instead of hardcoded mapping
        @if(isset($currentUserEmployee))
            return '{{ $currentUserEmployee->FirstName }} {{ $currentUserEmployee->LastName }}';
        @elseif(isset($currentUser))
            return '{{ $currentUser->username }}';
        @endif
        
        const secretaryMapping = {
            'College of Computer Studies': 'Mr. Andro Philip Banag',
            'College of Engineering': 'Ms. Carmen Lopez',
            'College of Business': 'Ms. Lisa Chen',
            'College of Arts and Sciences': 'Ms. Rachel Brown',
            'College of Education': 'Ms. Anna Garcia',
            // Add more mappings as needed
        };
        
        return secretaryMapping[departmentName] || 'Secretary'; // Default secretary name
    }
    
    // Function to update authority check based on encoder role and priority
    function updateAuthorityCheck() {
        const encoderRole = document.getElementById('encoded_by_role')?.value;
        const priority = document.getElementById('priority')?.value;
        const authorityMessage = document.getElementById('authority-message');
        
        if (!encoderRole || !authorityMessage) return;
        
        let message = '';
        let isAuthorized = true;
        
        if (priority === 'emergency') {
            if (encoderRole === 'secretary') {
                message = '⚠️ EMERGENCY requests require Dean authorization and signature. Please have the Dean encode this request.';
                isAuthorized = false;
            } else {
                message = '✅ Dean has full authority for Emergency requests. Request will be auto-approved with Dean signature.';
            }
        } else if (priority === 'high') {
            if (encoderRole === 'secretary') {
                message = '⚠️ HIGH PRIORITY requests will need Dean approval and signature after encoding.';
                isAuthorized = true; // Allow but warn
            } else {
                message = '✅ Dean can auto-approve High Priority requests with signature.';
            }
        } else {
            if (encoderRole === 'dean') {
                message = '✅ Dean can auto-approve and sign all request types.';
            } else {
                message = '✅ Secretary can encode routine requests. Dean approval and signature will be required.';
            }
        }
        
        authorityMessage.textContent = message;
        
        // Update visual feedback
        const authorityDiv = document.getElementById('authority-check');
        if (authorityDiv) {
            if (isAuthorized) {
                authorityDiv.className = 'mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md';
            } else {
                authorityDiv.className = 'mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md';
            }
        }
    }
    
    // Signature Modal Functions
    let signatureCanvas, signatureCtx;
    let isDrawing = false;
    let signaturePoints = [];
    let selectedSignatureFont = '';
    
    function openSignatureModal() {
        const modal = document.getElementById('signatureModal');
        if (modal) {
            modal.classList.remove('hidden');
            initializeSignatureCanvas();
            loadSignatureStyles();
        }
    }
    
    function closeSignatureModal() {
        const modal = document.getElementById('signatureModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
    
    function initializeSignatureCanvas() {
        signatureCanvas = document.getElementById('signatureCanvas');
        if (signatureCanvas) {
            signatureCtx = signatureCanvas.getContext('2d');
            signatureCtx.strokeStyle = '#000000';
            signatureCtx.lineWidth = 2;
            signatureCtx.lineCap = 'round';
            
            // Mouse events
            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseout', stopDrawing);
        }
    }
    
    function startDrawing(e) {
        isDrawing = true;
        const rect = signatureCanvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        signatureCtx.beginPath();
        signatureCtx.moveTo(x, y);
        signaturePoints.push([]);
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        const rect = signatureCanvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        signatureCtx.lineTo(x, y);
        signatureCtx.stroke();
        
        if (signaturePoints.length > 0) {
            signaturePoints[signaturePoints.length - 1].push({x, y});
        }
    }
    
    function stopDrawing() {
        isDrawing = false;
    }
    
    function clearCanvas() {
        if (signatureCtx) {
            signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            signaturePoints = [];
        }
    }
    
    function undoCanvas() {
        if (signaturePoints.length > 0) {
            signaturePoints.pop();
            redrawCanvas();
        }
    }
    
    function redrawCanvas() {
        if (!signatureCtx) return;
        
        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        
        signaturePoints.forEach(stroke => {
            if (stroke.length > 0) {
                signatureCtx.beginPath();
                signatureCtx.moveTo(stroke[0].x, stroke[0].y);
                stroke.forEach(point => {
                    signatureCtx.lineTo(point.x, point.y);
                });
                signatureCtx.stroke();
            }
        });
    }
    
    function loadSignatureStyles() {
        const stylesContainer = document.getElementById('signatureStyles');
        const deanName = document.getElementById('encoded_by_name')?.value || 'Dean Name';
        
        const fonts = [
            'Dancing Script', 'Pacifico', 'Great Vibes', 'Allura', 
            'Alex Brush', 'Satisfy', 'Kaushan Script', 'Amatic SC'
        ];
        
        stylesContainer.innerHTML = '';
        
        fonts.forEach((font, index) => {
            const preview = document.createElement('div');
            preview.className = 'signature-preview p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors';
            preview.style.fontFamily = `'${font}', cursive`;
            preview.style.fontSize = '1.5rem';
            preview.style.textAlign = 'center';
            preview.textContent = deanName.toUpperCase();
            
            preview.addEventListener('click', function() {
                document.querySelectorAll('.signature-preview').forEach(p => {
                    p.classList.remove('border-blue-500', 'bg-blue-50');
                    p.classList.add('border-gray-200');
                });
                
                this.classList.remove('border-gray-200');
                this.classList.add('border-blue-500', 'bg-blue-50');
                selectedSignatureFont = font;
            });
            
            stylesContainer.appendChild(preview);
        });
    }
    
    // Tab switching for signature methods
    document.addEventListener('DOMContentLoaded', function() {
        const textTab = document.getElementById('textSignatureTab');
        const drawTab = document.getElementById('drawSignatureTab');
        const textMethod = document.getElementById('textSignatureMethod');
        const drawMethod = document.getElementById('drawSignatureMethod');
        
        if (textTab && drawTab) {
            textTab.addEventListener('click', function() {
                textTab.classList.add('border-blue-500', 'text-blue-600');
                drawTab.classList.remove('border-blue-500', 'text-blue-600');
                drawTab.classList.add('text-gray-500', 'border-transparent');
                
                textMethod.classList.remove('hidden');
                drawMethod.classList.add('hidden');
            });
            
            drawTab.addEventListener('click', function() {
                drawTab.classList.add('border-blue-500', 'text-blue-600');
                textTab.classList.remove('border-blue-500', 'text-blue-600');
                textTab.classList.add('text-gray-500', 'border-transparent');
                
                drawMethod.classList.remove('hidden');
                textMethod.classList.add('hidden');
            });
        }
    });
    
    function submitWithSignature() {
        const activeTab = document.getElementById('textSignatureTab').classList.contains('text-blue-600') ? 'text' : 'draw';
        let signatureData = '';
        
        if (activeTab === 'text') {
            if (!selectedSignatureFont) {
                alert('Please select a signature style');
                return;
            }
            const deanName = document.getElementById('encoded_by_name')?.value || 'Dean Name';
            signatureData = JSON.stringify({
                type: 'text',
                name: deanName,
                font: selectedSignatureFont
            });
        } else {
            if (signaturePoints.length === 0) {
                alert('Please draw your signature');
                return;
            }
            signatureData = signatureCanvas.toDataURL();
        }
        
        // Add signature data to form
        document.getElementById('signatureData').value = signatureData;
        document.getElementById('signatureStyle').value = activeTab;
        
        // Show loading state on signature submit button
        const signatureSubmitBtn = document.querySelector('button[onclick="submitWithSignature()"]');
        if (signatureSubmitBtn) {
            signatureSubmitBtn.disabled = true;
            signatureSubmitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting with Signature...
            `;
        }
        
        // Submit the form with error handling
        const form = document.getElementById('proxy-request-form');
        submitFormWithErrorHandling(form, signatureSubmitBtn);
    }
    
    // Update workflow path based on selected employee's department
    function updateWorkflowPath() {
        if (!employeeSelect.value || !requestTypeSelect.value) {
            if (workflowSteps) {
                workflowSteps.textContent = 'Select employee and service type to see workflow path';
            }
            return;
        }
        
        // Find selected employee's department
        const selectedEmployee = departmentEmployees.find(emp => emp.user.id == employeeSelect.value);
        
        if (selectedEmployee && selectedEmployee.user.department) {
            const department = selectedEmployee.user.department;
            const isAcademic = department.category === 'Teaching';
            const serviceType = requestTypeSelect.value;
            
            let workflowPath = '';
            
            if (isAcademic) {
                // Academic Department Workflow: Secretary/Head → VPAA → PFMO
                workflowPath = `${department.dept_name} → VPAA → PFMO`;
                console.log('🎓 Academic workflow:', workflowPath);
            } else {
                // Non-Academic Department Workflow: Secretary/Head → VP Admin → PFMO
                workflowPath = `${department.dept_name} → VP Admin → PFMO`;
                console.log('🏢 Non-Academic workflow:', workflowPath);
            }
            
            if (workflowSteps) {
                workflowSteps.innerHTML = `<span class="text-green-600 font-medium">${workflowPath}</span>`;
            }
            
            // Update routing description with workflow-specific info
            if (routingDescription) {
                const workflowType = isAcademic ? 'Academic (Teaching)' : 'Non-Academic (Administrative)';
                routingDescription.textContent = `${pfmoServiceDescriptions[serviceType] || 'PFMO service request'} - ${workflowType} department workflow applies.`;
            }
        }
    }
    
    // Load PFMO specific fields
    function loadPFMOFields() {
        console.log('🏢 Loading PFMO request fields');
        
        const pfmoFieldsHTML = '<div class="space-y-6">' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">' +
                '<div>' +
                    '<label for="date_needed" class="block text-sm font-medium text-gray-700 mb-2">Date Needed <span class="text-red-500">*</span></label>' +
                    '<input type="date" id="date_needed" name="date_needed" required ' +
                           'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">' +
                '</div>' +
                '<div>' +
                    '<label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority Level <span class="text-red-500">*</span></label>' +
                    '<select id="priority" name="priority" required ' +
                            'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">' +
                        '<option value="">Select Priority</option>' +
                        '<option value="Urgent">Urgent - Immediate attention required</option>' +
                        '<option value="Rush">Rush - Complete within 24-48 hours</option>' +
                        '<option value="Routine">Routine - Normal processing time</option>' +
                    '</select>' +
                '</div>' +
            '</div>' +
            '<div>' +
                '<label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location/Room <span class="text-red-500">*</span></label>' +
                '<input type="text" id="location" name="location" required ' +
                       'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" ' +
                       'placeholder="Building, room number, or specific location">' +
            '</div>' +
            '<div>' +
                '<label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose/Title <span class="text-red-500">*</span></label>' +
                '<input type="text" id="purpose" name="purpose" maxlength="100" required ' +
                       'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" ' +
                       'placeholder="Brief description of the request purpose">' +
            '</div>' +
            '<div>' +
                '<label for="body" class="block text-sm font-medium text-gray-700 mb-2">Detailed Description <span class="text-red-500">*</span></label>' +
                '<textarea id="body" name="body" rows="4" required ' +
                          'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" ' +
                          'placeholder="Provide detailed information about the request, including any specific requirements, materials needed, or special instructions..."></textarea>' +
            '</div>' +
        '</div>';
        
        if (fieldsContainer) {
            fieldsContainer.innerHTML = pfmoFieldsHTML;
        }
        
        // Auto-populate based on service type
        const serviceType = requestTypeSelect.value;
        if (serviceType) {
            console.log('Auto-populating fields for PFMO service:', serviceType);
            
            const purposeField = document.getElementById('purpose');
            
            if (purposeField) {
                purposeField.value = serviceType;
            }
        }
    }
    
    // Initialize workflow path on page load if employee is pre-selected
    updateWorkflowPath();
    
    // Initialize auto-fill functionality on page load
    // 1. Auto-fill requested_by field if employee is already selected
    if (employeeSelect && employeeSelect.selectedIndex > 0) {
        console.log('🎯 Initializing auto-fill for pre-selected employee');
        const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
        const requestedByField = document.getElementById('requested_by');
        
        if (selectedOption && requestedByField) {
            const dataName = selectedOption.getAttribute('data-name');
            const textName = selectedOption.text.split(' - ')[0];
            const employeeName = dataName || textName;
            requestedByField.value = employeeName;
            console.log('✅ Auto-filled requested_by with:', employeeName);
        }
    }
    
    // 2. Auto-update target department if request type is already selected
    if (requestTypeSelect && requestTypeSelect.value) {
        console.log('🎯 Initializing target department for pre-selected request type:', requestTypeSelect.value);
        updateTargetDepartmentOptions();
    }
    
    // Enhanced Notification Demo Functions (only in debug mode)
    @if(config('app.debug'))
    window.demoSuccessNotification = function() {
        window.EnhancedNotifications.success(
            'Form Saved Successfully!', 
            'Your PFMO request has been submitted and is now being processed. You will receive email updates on its progress.',
            {
                actions: [
                    {
                        text: 'View Request',
                        primary: true,
                        handler: () => {
                            window.showInfoNotification('Demo Action', 'This would take you to the request details page!');
                        }
                    },
                    {
                        text: 'Create Another',
                        primary: false,
                        handler: () => {
                            window.showInfoNotification('Demo Action', 'This would reset the form for a new request!');
                        }
                    }
                ]
            }
        );
    };
    
    window.demoErrorNotification = function() {
        window.EnhancedNotifications.error(
            'Validation Error!', 
            'Please check the form fields. The employee field is required and the target department must be selected.',
            {
                duration: 8000,
                actions: [
                    {
                        text: 'Fix Issues',
                        primary: true,
                        handler: () => {
                            window.showInfoNotification('Demo Action', 'This would highlight the problematic fields!');
                        }
                    }
                ]
            }
        );
    };
    
    window.demoWarningNotification = function() {
        window.EnhancedNotifications.warning(
            'Unsaved Changes!', 
            'You have unsaved changes in this form. Are you sure you want to leave without saving?',
            {
                actions: [
                    {
                        text: 'Save & Continue',
                        primary: true,
                        handler: () => {
                            window.showSuccessNotification('Demo Action', 'Changes saved successfully!');
                        }
                    },
                    {
                        text: 'Discard Changes',
                        primary: false,
                        handler: () => {
                            window.showInfoNotification('Demo Action', 'Changes discarded.');
                        }
                    }
                ]
            }
        );
    };
    
    window.demoInfoNotification = function() {
        window.EnhancedNotifications.info(
            'System Maintenance Scheduled', 
            'The system will undergo scheduled maintenance tomorrow from 2:00 AM to 4:00 AM. Some features may be temporarily unavailable.',
            {
                duration: 10000
            }
        );
    };
    
    window.demoInteractiveNotification = function() {
        window.EnhancedNotifications.show({
            type: 'info',
            title: 'Interactive Features Demo!',
            message: 'This notification showcases all interactive features: progress bar, hover to pause, multiple actions, and enhanced animations!',
            duration: 15000,
            actions: [
                {
                    text: 'Show Success',
                    primary: true,
                    closeOnClick: false,
                    handler: () => {
                        window.demoSuccessNotification();
                    }
                },
                {
                    text: 'Show Error',
                    primary: false,
                    closeOnClick: false,
                    handler: () => {
                        window.demoErrorNotification();
                    }
                },
                {
                    text: 'Clear All',
                    primary: false,
                    handler: () => {
                        window.EnhancedNotifications.clear();
                        window.showInfoNotification('Demo Action', 'All notifications cleared!');
                    }
                }
            ],
            onClick: () => {
                console.log('Notification clicked!');
            }
        });
    };
    @endif
    
    
    // Debug form submission
    const proxyForm = document.getElementById('proxy-request-form');
    if (proxyForm) {
        proxyForm.addEventListener('submit', function(e) {
            console.log('🔍 Form submission attempt...');
            
            const formData = new FormData(proxyForm);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            console.log('📝 Form data being submitted:', data);
            
            // Check for required fields
            const requiredFields = ['employee_id', 'requested_by', 'request_type', 'title', 'priority', 'purpose', 'body', 'date_needed'];
            const missingFields = [];
            
            requiredFields.forEach(field => {
                if (!data[field] || data[field].trim() === '') {
                    missingFields.push(field);
                }
            });
            
            if (missingFields.length > 0) {
                console.log('❌ Missing required fields:', missingFields);
            } else {
                console.log('✅ All required fields present');
            }
        });
    }
    
    console.log('✅ DOMContentLoaded completed - All event listeners attached');
});
</script>
@endpush

@endsection
