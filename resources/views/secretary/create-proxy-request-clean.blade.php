@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Create Proxy Request</h1>
                    <p class="mt-2 text-sm text-gray-600">Submit a request on behalf of {{ $user->employee_info->last_name ?? 'Employee' }}, {{ $user->employee_info->first_name ?? '' }}</p>
                </div>

                <form action="{{ route('secretary.proxy-requests.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Employee Selection -->
                    <div class="mb-6">
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Employee <span class="text-red-500">*</span>
                        </label>
                        <select id="employee_id" name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Choose an employee...</option>
                            @foreach($departmentEmployees as $employee)
                                <option value="{{ $employee->user_id }}">
                                    {{ $employee->first_name }} {{ $employee->last_name }} - {{ $employee->position }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Form Type Selection -->
                    <div class="mb-6">
                        <label for="form_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Form Type <span class="text-red-500">*</span>
                        </label>
                        <select id="form_type" name="form_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Form Type</option>
                            <option value="IOM">IOM (Inter-Office Memorandum)</option>
                            <option value="Leave">Leave Request</option>
                        </select>
                    </div>

                    <!-- Request Type (for IOM only) -->
                    <div id="request-type-container" class="mb-6" style="display: none;">
                        <label for="request_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Request Type <span class="text-red-500">*</span>
                        </label>
                        <select id="request_type" name="request_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Request Type</option>
                            <option value="Vehicle Request">Vehicle Request</option>
                            <option value="Aircondition Repair">Aircondition Repair</option>
                            <option value="Electrical Repair">Electrical Repair</option>
                            <option value="IT Support">IT Support</option>
                            <option value="Plumbing Repair">Plumbing Repair</option>
                            <option value="Carpentry Work">Carpentry Work</option>
                            <option value="Cleaning Service">Cleaning Service</option>
                            <option value="Equipment Maintenance">Equipment Maintenance</option>
                            <option value="Purchase Request">Purchase Request</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Routing Information -->
                    <div id="routing-info" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md" style="display: none;">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Automatic Routing</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p id="routing-description">This request will be automatically routed to the appropriate department.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Form Fields -->
                    <div id="form-specific-fields" style="display: none;">
                        <!-- Fields will be populated by JavaScript -->
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
                            Preview Request
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Submit Proxy Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 FORM SCRIPT LOADED');
    
    // Get form elements
    const formType = document.getElementById('form_type');
    const requestContainer = document.getElementById('request-type-container');
    const requestTypeSelect = document.getElementById('request_type');
    const fieldsContainer = document.getElementById('form-specific-fields');
    const routingInfo = document.getElementById('routing-info');
    const routingDescription = document.getElementById('routing-description');
    
    // Request type routing mapping
    const requestTypeRouting = {
        'Vehicle Request': { department: 'PFMO', description: 'Vehicle Request - routed to Physical Facilities Management Office' },
        'Aircondition Repair': { department: 'PFMO', description: 'AC Repair - routed to Physical Facilities Management Office' },
        'Electrical Repair': { department: 'PFMO', description: 'Electrical Repair - routed to Physical Facilities Management Office' },
        'IT Support': { department: 'IT Department', description: 'IT Support - routed to College of Computer Studies' },
        'Plumbing Repair': { department: 'PFMO', description: 'Plumbing Repair - routed to Physical Facilities Management Office' },
        'Carpentry Work': { department: 'PFMO', description: 'Carpentry Work - routed to Physical Facilities Management Office' },
        'Cleaning Service': { department: 'PFMO', description: 'Cleaning Service - routed to Physical Facilities Management Office' },
        'Equipment Maintenance': { department: 'PFMO', description: 'Equipment Maintenance - routed to Physical Facilities Management Office' },
        'Purchase Request': { department: 'Administration', description: 'Purchase Request - routed to Administration (Finance)' },
        'Other': { department: 'PFMO', description: 'Other requests - routed to Physical Facilities Management Office' }
    };
    
    console.log('🔍 Elements found:', {
        formType: !!formType,
        requestContainer: !!requestContainer,
        requestTypeSelect: !!requestTypeSelect,
        fieldsContainer: !!fieldsContainer,
        routingInfo: !!routingInfo
    });
    
    // INITIAL STATE: Hide everything
    if (requestContainer) {
        requestContainer.style.display = 'none';
    }
    if (fieldsContainer) {
        fieldsContainer.style.display = 'none';
    }
    
    // Form type change handler
    if (formType) {
        formType.addEventListener('change', function() {
            console.log('🔄 Form type changed to:', this.value);
            
            // Reset all fields
            if (requestTypeSelect) {
                requestTypeSelect.value = '';
                requestTypeSelect.required = false;
            }
            
            if (this.value === 'IOM') {
                console.log('📋 Showing IOM request type dropdown');
                if (requestContainer) {
                    requestContainer.style.display = 'block';
                    requestTypeSelect.required = true;
                }
                if (fieldsContainer) {
                    fieldsContainer.style.display = 'none';
                }
                if (routingInfo) {
                    routingInfo.style.display = 'none';
                }
            } else if (this.value === 'Leave') {
                console.log('📝 Showing Leave form fields');
                if (requestContainer) {
                    requestContainer.style.display = 'none';
                }
                if (fieldsContainer) {
                    fieldsContainer.style.display = 'block';
                    loadLeaveFields();
                }
                if (routingInfo) {
                    routingInfo.style.display = 'none';
                }
            } else {
                // No form type selected
                if (requestContainer) {
                    requestContainer.style.display = 'none';
                }
                if (fieldsContainer) {
                    fieldsContainer.style.display = 'none';
                }
                if (routingInfo) {
                    routingInfo.style.display = 'none';
                }
            }
        });
    }
    
    // Request type change handler
    if (requestTypeSelect) {
        requestTypeSelect.addEventListener('change', function() {
            console.log('🎯 Request type changed to:', this.value);
            
            if (this.value && formType && formType.value === 'IOM') {
                console.log('✅ Loading IOM fields for:', this.value);
                if (fieldsContainer) {
                    fieldsContainer.style.display = 'block';
                    loadIOMFields();
                }
                
                // Show routing information
                if (requestTypeRouting[this.value] && routingInfo && routingDescription) {
                    routingDescription.textContent = requestTypeRouting[this.value].description;
                    routingInfo.style.display = 'block';
                } else if (routingInfo) {
                    routingInfo.style.display = 'none';
                }
            } else {
                console.log('❌ No request type - hiding fields');
                if (fieldsContainer) {
                    fieldsContainer.style.display = 'none';
                }
                if (routingInfo) {
                    routingInfo.style.display = 'none';
                }
            }
        });
    }
    
    // Load IOM specific fields
    function loadIOMFields() {
        console.log('📋 Loading IOM fields');
        
        const iomFieldsHTML = '<div class="space-y-6">' +
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
                        '<option value="Urgent">Urgent</option>' +
                        '<option value="Routine">Routine</option>' +
                        '<option value="Rush">Rush</option>' +
                    '</select>' +
                '</div>' +
            '</div>' +
            '<div>' +
                '<label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose <span class="text-red-500">*</span></label>' +
                '<input type="text" id="purpose" name="purpose" maxlength="100" required ' +
                       'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" ' +
                       'placeholder="Brief description of the request purpose">' +
            '</div>' +
            '<div>' +
                '<label for="body" class="block text-sm font-medium text-gray-700 mb-2">Request Details <span class="text-red-500">*</span></label>' +
                '<textarea id="body" name="body" rows="4" required ' +
                          'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" ' +
                          'placeholder="Detailed description of your request..."></textarea>' +
            '</div>' +
        '</div>';
        
        if (fieldsContainer) {
            fieldsContainer.innerHTML = iomFieldsHTML;
        }
        
        // Auto-populate based on request type
        const requestType = requestTypeSelect.value;
        if (requestType) {
            console.log('Auto-populating fields for:', requestType);
            
            const purposeField = document.getElementById('purpose');
            const bodyField = document.getElementById('body');
            
            if (purposeField) {
                purposeField.value = requestType;
            }
            
            if (bodyField && requestType !== 'Other') {
                bodyField.value = 'Request for ' + requestType.toLowerCase() + ' service.';
            }
        }
    }
    
    // Load Leave specific fields
    function loadLeaveFields() {
        console.log('📝 Loading Leave fields');
        
        const leaveFieldsHTML = '<div class="space-y-6">' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">' +
                '<div>' +
                    '<label for="leave_start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date <span class="text-red-500">*</span></label>' +
                    '<input type="date" id="leave_start_date" name="leave_start_date" required ' +
                           'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">' +
                '</div>' +
                '<div>' +
                    '<label for="leave_end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date <span class="text-red-500">*</span></label>' +
                    '<input type="date" id="leave_end_date" name="leave_end_date" required ' +
                           'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">' +
                '</div>' +
            '</div>' +
            '<div>' +
                '<label for="leave_reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Leave <span class="text-red-500">*</span></label>' +
                '<textarea id="leave_reason" name="leave_reason" rows="3" required ' +
                          'class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" ' +
                          'placeholder="Please specify the reason for your leave request..."></textarea>' +
            '</div>' +
        '</div>';
        
        if (fieldsContainer) {
            fieldsContainer.innerHTML = leaveFieldsHTML;
        }
    }
});
</script>
@endpush

@endsection
