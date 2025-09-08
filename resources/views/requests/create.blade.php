<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Form') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Warning for Pending Job Order Fillup -->
                    @if(isset($hasPendingFeedback) && $hasPendingFeedback)
                        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Pending Job Order Fillup Required
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        @if($pendingFeedbackCount >= 2)
                                            <p>You have {{ $pendingFeedbackCount }} completed job order(s) requiring fillup. You cannot submit new requests until you complete at least {{ $pendingFeedbackCount - 1 }} fillup(s).</p>
                                        @else
                                            <p>You have {{ $pendingFeedbackCount }} completed job order(s) requiring fillup. You can still submit this form, but please complete the fillup when convenient.</p>
                                        @endif
                                        <div class="mt-3">
                                            @if($oldestPendingJobOrder)
                                                {{-- Go directly to the oldest pending job order track page --}}
                                                <a href="{{ route('request.track', $oldestPendingJobOrder->form_id) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            @endif
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                                Fill-up Job Order Form
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Session Error Messages for Job Order Fillup -->
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Cannot Submit Request
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>{{ session('error') }}</p>
                                        @if(session('pending_feedback_url'))
                                            <div class="mt-3">
                                                <a href="{{ route('dashboard') }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Go to Track Request
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif                    <form method="POST" action="{{ route('request.store') }}" id="request-form">
                        @csrf
                        <input type="hidden" name="form_type" id="form_type" value="{{ old('form_type', $formData['form_type'] ?? '') }}">
                        <input type="hidden" name="to_department_id" id="to_department_id" value="{{ old('to_department_id', $formData['to_department_id'] ?? '') }}">

                        <!-- Request Type -->
                        <div class="mb-6">
                            <x-input-label for="request_type" :value="__('Form Type')" />
                            <select id="request_type" name="request_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm bg-white dark:bg-gray-900" required>
                                <option value="">-- Select Form Type --</option>
                                <option value="IOM" {{ old('request_type', $formData['request_type'] ?? '') == 'IOM' ? 'selected' : '' }}>Inter-Office Memorandum (IOM)</option>
                                <option value="Leave" {{ old('request_type', $formData['request_type'] ?? '') == 'Leave' ? 'selected' : '' }}>Leave</option>
                            </select>
                            <x-input-error :messages="$errors->get('request_type')" class="mt-2" />
                        </div>

                        {{-- IOM Specific Fields --}}
                        <div id="iom_fields" class="iom-fields-class specific-fields hidden">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 border-b pb-2">IOM Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                                <!-- IOM To (Department) - Autocomplete with Auto-Assignment -->
                                <div class="mb-4">
                                    <x-input-label for="iom_to_department_name_display" :value="__('To (Department)')" />
                                    <div class="relative">
                                        <x-text-input id="iom_to_department_name_display"
                                                    class="block mt-1 w-full bg-white dark:bg-gray-900 pr-10"
                                                    type="text"
                                                    name="iom_to_department_name_display"
                                                    :value="old('iom_to_department_name_display', $formData['iom_to_department_name_display'] ?? '')"
                                                    list="department_list_datalist" 
                                                    placeholder="Type request details below for auto-assignment..." />
                                        <!-- Loading indicator -->
                                        <div id="auto-assignment-loading" class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                                            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <datalist id="department_list_datalist">
                                        @foreach($departments as $department)
                                            <option value="{{ $department->dept_name }} ({{ $department->dept_code }})" data-id="{{ $department->department_id }}"></option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="iom_to_department_id" id="iom_to_department_id" value="{{ old('iom_to_department_id', $formData['iom_to_department_id'] ?? '') }}">
                                    <x-input-error :messages="$errors->get('iom_to_department_id')" class="mt-2" />
                                </div>

                                <!-- IOM Re (Subject) - Now a dropdown -->
                                <div class="mb-4">
                                    <x-input-label for="iom_re" :value="__('Re (Subject)')" />
                                    <select id="iom_re" name="iom_re" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                        <option value="">-- Select Subject/Request Type --</option>
                                        <option value="Computer Repair" data-dept="ICT" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Computer Repair' ? 'selected' : '' }}>Computer Repair</option>
                                        <option value="Network Issue" data-dept="ICT" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Network Issue' ? 'selected' : '' }}>Network/Internet Issue</option>
                                        <option value="Software Support" data-dept="ICT" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Software Support' ? 'selected' : '' }}>Software Support</option>
                                        <option value="Air Conditioning" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Air Conditioning' ? 'selected' : '' }}>Air Conditioning</option>
                                        <option value="Electrical Work" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Electrical Work' ? 'selected' : '' }}>Electrical Work</option>
                                        <option value="Plumbing" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Plumbing' ? 'selected' : '' }}>Plumbing</option>
                                        <option value="Building Maintenance" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Building Maintenance' ? 'selected' : '' }}>Building Maintenance</option>
                                        <option value="Cleaning Services" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Cleaning Services' ? 'selected' : '' }}>Cleaning Services</option>
                                        <option value="Security/Access" data-dept="BLDG" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Security/Access' ? 'selected' : '' }}>Security/Access Issues</option>
                                        <option value="Keys/Locks" data-dept="BLDG" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Keys/Locks' ? 'selected' : '' }}>Keys/Locks</option>
                                        <option value="Request for Facilities" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Request for Facilities' ? 'selected' : '' }}>Request for Facilities</option>
                                        <option value="Request for Computer Laboratory" data-dept="ICT" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Request for Computer Laboratory' ? 'selected' : '' }}>Request for Computer Laboratory</option>
                                        <option value="Request for Venue" data-dept="PFMO" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Request for Venue' ? 'selected' : '' }}>Request for Venue</option>
                                        <option value="Others" {{ old('iom_re', $formData['iom_re'] ?? '') == 'Others' ? 'selected' : '' }}>Others (Specify)</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('iom_re')" class="mt-2" />
                                </div>

                                <!-- Custom Subject Input (shown when Others is selected) -->
                                <div class="mb-4 hidden" id="custom_subject_container">
                                    <x-input-label for="custom_subject" :value="__('Specify Custom Subject')" />
                                    <x-text-input id="custom_subject" 
                                                class="block mt-1 w-full" 
                                                type="text" 
                                                name="custom_subject" 
                                                placeholder="Please specify your subject/request type..." />
                                    <x-input-error :messages="$errors->get('custom_subject')" class="mt-2" />
                                </div>

                                <!-- IOM From -->
                                <div class="mb-4">
                                    <x-input-label for="iom_from" :value="__('From (Your Department)')" />
                                    <x-text-input id="iom_from" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" name="iom_from" :value="old('iom_from', $formData['iom_from'] ?? (Auth::user()->department->dept_name ?? (Auth::user()->username ?? 'Unknown User')))" readonly />
                                </div>
                                
                                <!-- IOM Date Needed -->
                                <div class="mb-4">
                                    <x-input-label for="iom_date_needed" :value="__('Date Needed')" />
                                    <x-text-input id="iom_date_needed" class="block mt-1 w-full" type="date" name="iom_date_needed" :value="old('iom_date_needed', $formData['iom_date_needed'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('iom_date_needed')" class="mt-2" />
                                </div>
                                
                                <div class="mb-4">
                                    <x-input-label for="iom_priority" :value="__('Priority')" />
                                    <select id="iom_priority" name="iom_priority" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="Routine" {{ old('iom_priority', $formData['iom_priority'] ?? '') == 'Routine' ? 'selected' : '' }}>Routine</option>
                                        <option value="Urgent" {{ old('iom_priority', $formData['iom_priority'] ?? '') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="Rush" {{ old('iom_priority', $formData['iom_priority'] ?? '') == 'Rush' ? 'selected' : '' }}>Rush</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('iom_priority')" class="mt-2" />
                                </div>
                                
                                <div class="mb-4">
                                    <x-input-label for="iom_purpose" :value="__('Purpose')" />
                                    <select id="iom_purpose" name="iom_purpose" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">-- Select Purpose --</option>
                                        <option value="For Information" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Information' ? 'selected' : '' }}>For your information</option>
                                        <option value="For Action" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Action' ? 'selected' : '' }}>For your action</option>
                                        <option value="For Signature" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Signature' ? 'selected' : '' }}>For your signature</option>
                                        <option value="For Comments" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Comments' ? 'selected' : '' }}>For comments</option>
                                        <option value="For Approval" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Approval' ? 'selected' : '' }}>For approval</option>
                                        <option value="Others" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('iom_purpose')" class="mt-2" />
                                </div>

                                <!-- Others Purpose Input Field -->
                                <div id="iom_other_purpose_container" class="mb-4 hidden">
                                    <x-input-label for="iom_other_purpose" :value="__('Specify Other Purpose')" />
                                    <x-text-input id="iom_other_purpose" 
                                        class="block mt-1 w-full" 
                                        type="text" 
                                        name="iom_other_purpose" 
                                        :value="old('iom_other_purpose', $formData['iom_other_purpose'] ?? '')" />
                                    <x-input-error :messages="$errors->get('iom_other_purpose')" class="mt-2" />
                                </div>
                            </div>


                            <!-- IOM Description -->
                            <div class="mb-4 md:col-span-2">
                                <x-input-label for="iom_description" :value="__('Description/Body')" />
                                <textarea id="iom_description" name="iom_description" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('iom_description', $formData['iom_description'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('iom_description')" class="mt-2" />
                            </div>
                        </div>
                        
                        {{-- Leave Request Specific Fields --}}
                        <div id="leave_fields" class="leave-fields-class specific-fields hidden">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 border-b pb-2">Leave Details</h3>
                            
                            <!-- Leave Type -->
                            <div class="mb-6">
                                <x-input-label :value="__('Type of Leave')" class="mb-2 font-medium"/>
                                <div class="flex flex-col sm:flex-row sm:gap-x-6 gap-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500" name="leave_type" value="sick" {{ old('leave_type', $formData['leave_type'] ?? '') == 'sick' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Sick Leave') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500" name="leave_type" value="vacation" {{ old('leave_type', $formData['leave_type'] ?? '') == 'vacation' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Vacation Leave') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500" name="leave_type" value="emergency" {{ old('leave_type', $formData['leave_type'] ?? '') == 'emergency' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Emergency Leave') }}</span>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('leave_type')" class="mt-2" />
                            </div>

                            <!-- Date Range of Leave -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <x-input-label for="leave_start_date" :value="__('Start Date')" />
                                    <x-text-input id="leave_start_date" class="block mt-1 w-full" type="date" name="leave_start_date" :value="old('leave_start_date', $formData['leave_start_date'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('leave_start_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="leave_end_date" :value="__('End Date')" />
                                    <x-text-input id="leave_end_date" class="block mt-1 w-full" type="date" name="leave_end_date" :value="old('leave_end_date', $formData['leave_end_date'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('leave_end_date')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Number of Days -->
                            <div class="mb-6">
                                <x-input-label for="leave_days" :value="__('Number of Days')" />
                                <x-text-input id="leave_days" class="block mt-1 w-full" type="number" name="leave_days" min="1" step="1" :value="old('leave_days', $formData['leave_days'] ?? '')" required readonly />
                                <x-input-error :messages="$errors->get('leave_days')" class="mt-2" />
                            </div>

                            <!-- Leave Description/Reason -->
                            <div class="mb-6">
                                <x-input-label for="leave_description" :value="__('Description / Reason')" />
                                <textarea id="leave_description" name="leave_description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm placeholder-gray-400" placeholder="Provide a brief reason for your leave...">{{ old('leave_description', $formData['leave_description'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('leave_description')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Workflow Preview Container -->
                        <div id="workflow-preview-container" class="mt-8">
                            <!-- Preview will be populated by JavaScript -->
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <div class="flex space-x-4">
                                <button type="button" data-action="preview-workflow" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600 hidden" id="fullPreviewButton">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Preview Workflow
                                </button>
                                <x-primary-button type="button" id="reviewButton">
                                    {{ __('Submit') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>

                    <!-- Confirmation Modal -->
                    <div id="confirmationModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden" style="z-index: 50;">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirmation</h3>
                                
                                <div id="confirmationContent" class="space-y-4">
                                    <!-- Content will be populated by JavaScript -->
                                </div>

                                <div class="mt-6 flex justify-end space-x-4 border-t pt-4">
                                    <button type="button" id="editButton" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                                        Back
                                    </button>
                                    <x-primary-button id="confirmSubmitButton">
                                        Confirm & Submit
                                    </x-primary-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.getElementById('request-form');
                            const requestTypeSelect = document.getElementById('request_type');
                            const formTypeHidden = document.getElementById('form_type');
                            const toDepartmentIdHidden = document.getElementById('to_department_id');
                            const iomFields = document.getElementById('iom_fields');
                            const leaveFields = document.getElementById('leave_fields');
                            const reviewButton = document.getElementById('reviewButton');
                            const fullPreviewButton = document.getElementById('fullPreviewButton');
                            const modal = document.getElementById('confirmationModal');
                            const confirmationContent = document.getElementById('confirmationContent');
                            const editButton = document.getElementById('editButton');
                            const confirmSubmitButton = document.getElementById('confirmSubmitButton');
                            const dateNeededInput = document.getElementById('iom_date_needed');

                            // Set min date for iom_date_needed to today
                            if (dateNeededInput) {
                                const today = new Date().toLocaleDateString('en-CA'); // Use yyyy-mm-dd format
                                dateNeededInput.setAttribute('min', today);
                            }

                            // Leave form elements
                            const leaveStartDate = document.getElementById('leave_start_date');
                            const leaveEndDate = document.getElementById('leave_end_date');
                            const leaveDays = document.getElementById('leave_days');

                            // Set min date for leave_start_date and leave_end_date to today
                            if (leaveStartDate) {
                                const today = new Date().toLocaleDateString('en-CA');
                                leaveStartDate.setAttribute('min', today);
                            }
                            if (leaveEndDate) {
                                const today = new Date().toLocaleDateString('en-CA');
                                leaveEndDate.setAttribute('min', today);
                            }

                            // Add event listener to leave_start_date to update min for leave_end_date
                            if (leaveStartDate && leaveEndDate) {
                                leaveStartDate.addEventListener('change', function() {
                                    if (leaveStartDate.value) {
                                        leaveEndDate.setAttribute('min', leaveStartDate.value);
                                        // If end date is before new start date, clear end date
                                        if (leaveEndDate.value < leaveStartDate.value) {
                                            leaveEndDate.value = '';
                                        }
                                    }
                                });
                            }

                            // Elements for the corrected workflow - Keep Purpose, RE as dropdown
                            const iomPurposeSelect = document.getElementById('iom_purpose');
                            const iomOtherPurposeContainer = document.getElementById('iom_other_purpose_container');
                            const iomOtherPurposeInput = document.getElementById('iom_other_purpose');
                            const iomReSelect = document.getElementById('iom_re');
                            const customSubjectContainer = document.getElementById('custom_subject_container');
                            const customSubjectInput = document.getElementById('custom_subject');

                            // For IOM department autocomplete
                            const iomToDepartmentNameDisplay = document.getElementById('iom_to_department_name_display');
                            const iomToDepartmentIdHidden = document.getElementById('iom_to_department_id');
                            const departmentDatalist = document.getElementById('department_list_datalist');

                            // Required attributes map for conditional validation (client-side indication)
                            // Note: Server-side validation is the source of truth.
                            const iomRequiredFields = ['iom_to_department_name_display', 'iom_re', 'iom_priority', 'iom_purpose', 'iom_description', 'iom_date_needed'];
                            const leaveRequiredFields = ['leave_type', 'leave_start_date', 'leave_end_date', 'leave_days', 'leave_description'];

                            function validateForm() {
                                const selectedType = requestTypeSelect.value;
                                if (!selectedType) {
                                    styledAlert('Please select a form type', 'Form Validation', 'warning');
                                    return false;
                                }

                                const requiredFields = selectedType === 'IOM' ? iomRequiredFields : leaveRequiredFields;
                                for (const fieldId of requiredFields) {
                                    const field = document.getElementById(fieldId);
                                    if (!field) continue;
                                    
                                    if (fieldId === 'leave_type') {
                                        const selectedRadio = document.querySelector('input[name="leave_type"]:checked');
                                        if (!selectedRadio) {
                                            styledAlert('Please select a leave type', 'Form Validation', 'warning');
                                            return false;
                                        }
                                    } else if (!field.value.trim()) {
                                        styledAlert(`Please fill in the ${field.name.replace(/_/g, ' ')}`, 'Form Validation', 'warning');
                                        field.focus();
                                        return false;
                                    }
                                }
                                return true;
                            }

                            function togglePurposeFields() {
                                if (iomPurposeSelect && iomOtherPurposeContainer && iomOtherPurposeInput) {
                                    const selectedPurpose = iomPurposeSelect.value;

                                    // Handle Others option
                                    if (selectedPurpose === 'Others') {
                                        iomOtherPurposeContainer.classList.remove('hidden');
                                        iomOtherPurposeInput.setAttribute('required', 'required');
                                    } else {
                                        iomOtherPurposeContainer.classList.add('hidden');
                                        iomOtherPurposeInput.removeAttribute('required');
                                        iomOtherPurposeInput.value = '';
                                    }
                                }
                            }

                            function handleSubjectSelection() {
                                if (iomReSelect && customSubjectContainer && customSubjectInput) {
                                    const selectedValue = iomReSelect.value;
                                    const selectedOption = iomReSelect.options[iomReSelect.selectedIndex];

                                    // Handle "Others" option
                                    if (selectedValue === 'Others') {
                                        customSubjectContainer.classList.remove('hidden');
                                        customSubjectInput.setAttribute('required', 'required');
                                    } else {
                                        customSubjectContainer.classList.add('hidden');
                                        customSubjectInput.removeAttribute('required');
                                        customSubjectInput.value = '';
                                    }

                                    // Auto-assign department based on selection
                                    if (selectedOption && selectedOption.hasAttribute('data-dept')) {
                                        const deptCode = selectedOption.getAttribute('data-dept');
                                        autoAssignDepartmentByCode(deptCode, selectedValue);
                                    }
                                }
                            }

                            // Handle request type dropdown auto-assignment and "Others" option
                            // Auto-assign department by department code
                            function autoAssignDepartmentByCode(deptCode, requestType) {
                                if (!departmentDatalist || !iomToDepartmentNameDisplay || !iomToDepartmentIdHidden) return;

                                // Department mapping
                                const deptMapping = {
                                    'ICT': 'Information and Communications Technology',
                                    'PFMO': 'Physical Facilities Management Office',
                                    'BLDG': 'Building Services'
                                };

                                const deptName = deptMapping[deptCode];
                                if (!deptName) return;

                                // Find the department in the datalist
                                for (let i = 0; i < departmentDatalist.options.length; i++) {
                                    const option = departmentDatalist.options[i];
                                    if (option.value.includes(deptName)) {
                                        iomToDepartmentNameDisplay.value = option.value;
                                        iomToDepartmentIdHidden.value = option.getAttribute('data-id');
                                        toDepartmentIdHidden.value = option.getAttribute('data-id');
                                        
                                        break;
                                    }
                                }
                            }

                            function setInitialToDepartmentDisplay() {
                                const initialDeptId = iomToDepartmentIdHidden.value;
                                if (initialDeptId && departmentDatalist && departmentDatalist.options) {
                                    for (let i = 0; i < departmentDatalist.options.length; i++) {
                                        const option = departmentDatalist.options[i];
                                        if (option.getAttribute('data-id') === initialDeptId) {
                                            iomToDepartmentNameDisplay.value = option.value;
                                            break;
                                        }
                                    }
                                }
                            }

                            function toggleFields() {
                                const selectedType = requestTypeSelect.value;
                                
                                iomFields.classList.add('hidden');
                                leaveFields.classList.add('hidden');
                                reviewButton.disabled = selectedType === '' || selectedType === null;
                                
                                // Update hidden form_type field
                                formTypeHidden.value = selectedType || '';

                                document.querySelectorAll('.specific-fields [required]').forEach(el => el.removeAttribute('required'));

                                if (selectedType === 'IOM') {
                                    iomFields.classList.remove('hidden');
                                    fullPreviewButton.classList.remove('hidden');
                                    iomRequiredFields.forEach(id => {
                                        const el = document.getElementById(id);
                                        if(el) el.setAttribute('required', 'required');
                                    });
                                    // Initialize both handlers
                                    togglePurposeFields();
                                    handleSubjectSelection();
                                } else if (selectedType === 'Leave') {
                                    leaveFields.classList.remove('hidden');
                                    fullPreviewButton.classList.remove('hidden');
                                    leaveRequiredFields.forEach(id => {
                                        const el = document.getElementById(id);
                                        if(el) el.setAttribute('required', 'required');
                                    });
                                } else {
                                    fullPreviewButton.classList.add('hidden');
                                }
                            }

                            function calculateLeaveDays() {
                                if (leaveStartDate && leaveEndDate && leaveDays) {
                                    const start = new Date(leaveStartDate.value);
                                    const end = new Date(leaveEndDate.value);
                                    
                                    if (start && end && !isNaN(start) && !isNaN(end)) {
                                        // Add 1 to include both start and end dates
                                        const diffTime = Math.abs(end - start);
                                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                                        leaveDays.value = diffDays;
                                    } else {
                                        leaveDays.value = '';
                                    }
                                }
                            }

                            // Auto-assignment functionality
                            let autoAssignmentTimeout;
                            let lastAutoAssignText = '';

                            function performAutoAssignment() {
                                const iomRe = document.getElementById('iom_re');
                                const iomDescription = document.getElementById('iom_description');
                                const iomPurpose = document.getElementById('iom_purpose');
                                const iomSpecificRequestType = document.getElementById('iom_specific_request_type');
                                const departmentDisplay = document.getElementById('iom_to_department_name_display');
                                const departmentHidden = document.getElementById('iom_to_department_id');

                                if (!iomRe || !iomDescription || !departmentDisplay) return;

                                const title = iomRe.value.trim();
                                const description = iomDescription.value.trim();
                                const purpose = iomPurpose ? iomPurpose.value : '';
                                const specificRequestType = iomSpecificRequestType ? iomSpecificRequestType.value : '';

                                // Only proceed if we have enough text and it's different from last time
                                const combinedText = title + ' ' + description + ' ' + purpose + ' ' + specificRequestType;
                                if (combinedText.length < 10 || combinedText === lastAutoAssignText) {
                                    return;
                                }

                                lastAutoAssignText = combinedText;

                                // Show loading indicator
                                const loadingIndicator = document.getElementById('auto-assignment-loading');
                                if (loadingIndicator) {
                                    loadingIndicator.classList.remove('hidden');
                                }

                                // Make AJAX request for auto-assignment
                                fetch('{{ route("request.auto-assign") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        title: title,
                                        description: description,
                                        purpose: purpose,
                                        specific_request_type: specificRequestType
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (loadingIndicator) {
                                        loadingIndicator.classList.add('hidden');
                                    }

                                    if (data.success && data.assignment) {
                                        // Update department field
                                        const assignment = data.assignment;
                                        departmentDisplay.value = assignment.department_name;
                                        departmentHidden.value = assignment.department_id;
                                    }
                                })
                                .catch(error => {
                                    console.error('Auto-assignment error:', error);
                                    if (loadingIndicator) {
                                        loadingIndicator.classList.add('hidden');
                                    }
                                });
                            }

                            function setupAutoAssignment() {
                                const iomDescription = document.getElementById('iom_description');

                                // Add event listeners for auto-assignment
                                [iomReSelect, iomDescription].forEach(element => {
                                    if (element) {
                                        element.addEventListener('input', function() {
                                            // Clear existing timeout
                                            if (autoAssignmentTimeout) {
                                                clearTimeout(autoAssignmentTimeout);
                                            }

                                            // Set new timeout for auto-assignment (wait 1 second after user stops typing)
                                            autoAssignmentTimeout = setTimeout(performAutoAssignment, 1000);
                                        });
                                    }
                                });
                            }

                            // Event Listeners
                            requestTypeSelect.addEventListener('change', toggleFields);
                            
                            // Add event listener for Purpose dropdown
                            if (iomPurposeSelect) {
                                iomPurposeSelect.addEventListener('change', togglePurposeFields);
                            }
                            
                            // Add event listener for RE (Subject) dropdown
                            if (iomReSelect) {
                                iomReSelect.addEventListener('change', handleSubjectSelection);
                            }

                            // Add event listener for custom subject input
                            if (customSubjectInput) {
                                customSubjectInput.addEventListener('input', function() {
                                    // Update the RE dropdown value with custom text for form submission
                                    if (this.value.trim()) {
                                        iomReSelect.value = this.value.trim();
                                    }
                                });
                            }

                            if (leaveStartDate && leaveEndDate) {
                                leaveStartDate.addEventListener('change', function() {
                                    if (leaveEndDate.value) {
                                        const startDate = new Date(this.value);
                                        const endDate = new Date(leaveEndDate.value);
                                        if (startDate > endDate) {
                                            leaveEndDate.value = this.value;
                                        }
                                    }
                                    calculateLeaveDays();
                                });

                                leaveEndDate.addEventListener('change', function() {
                                    if (leaveStartDate.value) {
                                        const startDate = new Date(leaveStartDate.value);
                                        const endDate = new Date(this.value);
                                        if (endDate < startDate) {
                                            this.value = leaveStartDate.value;
                                        }
                                    }
                                    calculateLeaveDays();
                                });
                            }

                            if (iomToDepartmentNameDisplay) {
                                // Remove existing options for Administration department from datalist
                                if (departmentDatalist && departmentDatalist.options) {
                                    for (let i = departmentDatalist.options.length - 1; i >= 0; i--) {
                                        const option = departmentDatalist.options[i];
                                        if (option.value.toLowerCase().includes('administration') || 
                                            option.value.toLowerCase().includes('admin')) {
                                            departmentDatalist.removeChild(option);
                                        }
                                    }
                                }
                                
                                iomToDepartmentNameDisplay.addEventListener('input', function() {
                                    const inputValue = this.value;
                                    let found = false;
                                    if (departmentDatalist && departmentDatalist.options) {
                                        for (let i = 0; i < departmentDatalist.options.length; i++) {
                                            const option = departmentDatalist.options[i];
                                            if (option.value === inputValue) {
                                                iomToDepartmentIdHidden.value = option.getAttribute('data-id');
                                                toDepartmentIdHidden.value = option.getAttribute('data-id');
                                                found = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!found) {
                                        iomToDepartmentIdHidden.value = '';
                                        toDepartmentIdHidden.value = '';
                                    } else {
                                        // Check if the department is Administration
                                        if (inputValue.toLowerCase().includes("administration") || inputValue.toLowerCase().includes("admin")) {
                                            // Reset the value and show error message
                                            this.value = '';
                                            iomToDepartmentIdHidden.value = '';
                                            
                                            // Create or update error message
                                            let errorDiv = document.getElementById('admin-dept-error');
                                            if (!errorDiv) {
                                                errorDiv = document.createElement('div');
                                                errorDiv.id = 'admin-dept-error';
                                                errorDiv.className = 'text-sm text-red-600 dark:text-red-400 mt-2';
                                                this.parentNode.appendChild(errorDiv);
                                            }
                                            errorDiv.textContent = 'The Administration department cannot be selected for IOM requests. Please choose another department.';
                                            
                                            // Remove error message after 5 seconds
                                            setTimeout(() => {
                                                if (errorDiv && errorDiv.parentNode) {
                                                    errorDiv.parentNode.removeChild(errorDiv);
                                                }
                                            }, 5000);
                                        } else {
                                            // Remove any existing error message
                                            const errorDiv = document.getElementById('admin-dept-error');
                                            if (errorDiv && errorDiv.parentNode) {
                                                errorDiv.parentNode.removeChild(errorDiv);
                                            }
                                        }
                                    }
                                });

                                iomToDepartmentNameDisplay.addEventListener('change', function() {
                                    if (this.value === '') {
                                        iomToDepartmentIdHidden.value = '';
                                        toDepartmentIdHidden.value = '';
                                    }
                                });
                            }

                            function formatDate(dateString) {
                                if (!dateString) return 'N/A';
                                return new Date(dateString).toLocaleDateString('en-CA', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                });
                            }

                            function showConfirmation() {
                                if (!validateForm()) {
                                    return;
                                }

                                const formData = new FormData(form);
                                const requestType = formData.get('request_type');
                                let content = '';

                                content += `<div class="border rounded-lg p-4 dark:border-gray-700">
                                    <p class="font-medium mb-2">Form Type:</p>
                                    <p class="text-gray-800 dark:text-gray-200 mb-4 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded border-2 border-indigo-300 dark:border-indigo-600 shadow-sm">${requestType}</p>`;

                                if (requestType === 'IOM') {
                                    const dept = document.querySelector('#iom_to_department_name_display').value;
                                    const purpose = formData.get('iom_purpose');
                                    const subject = formData.get('iom_re');
                                    
                                    let displayPurpose = purpose;
                                    let displaySubject = subject;

                                    // Handle Purpose Others
                                    if (purpose === 'Others' && formData.get('iom_other_purpose')) {
                                        displayPurpose = formData.get('iom_other_purpose');
                                    }

                                    // Handle Subject Others
                                    if (subject === 'Others' && formData.get('custom_subject')) {
                                        displaySubject = formData.get('custom_subject');
                                    }

                                    content += `
                                        <p class="font-medium mb-2">To Department:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded border-2 border-indigo-300 dark:border-indigo-600 shadow-sm">${dept}</p>
                                        <p class="font-medium mb-2">Subject:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded border-2 border-indigo-300 dark:border-indigo-600 shadow-sm">${displaySubject}</p>
                                        <p class="font-medium mb-2">Priority:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formData.get('iom_priority')}</p>
                                        <p class="font-medium mb-2">Purpose:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${displayPurpose}</p>
                                        <p class="font-medium mb-2">Date Needed:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formatDate(formData.get('iom_date_needed'))}</p>
                                        <p class="font-medium mb-2">Description:</p>
                                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">${formData.get('iom_description')}</p>`;
                                } else if (requestType === 'Leave') {
                                    content += `
                                        <p class="font-medium mb-2">Leave Type:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formData.get('leave_type')}</p>
                                        <p class="font-medium mb-2">Leave Period:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formatDate(formData.get('leave_start_date'))} to ${formatDate(formData.get('leave_end_date'))} (${formData.get('leave_days')} days)</p>
                                        <p class="font-medium mb-2">Reason:</p>
                                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">${formData.get('leave_description')}</p>`;
                                }

                                content += '</div>';
                                confirmationContent.innerHTML = content;
                                modal.classList.remove('hidden');
                            }

                            // Event Listeners for Modal
                            reviewButton.addEventListener('click', showConfirmation);
                            editButton.addEventListener('click', function() {
                                modal.classList.add('hidden');
                            });

                            confirmSubmitButton.addEventListener('click', function() {
                                // Handle Subject "Others" case before submission
                                if (iomReSelect && customSubjectInput && 
                                    iomReSelect.value === 'Others' && customSubjectInput.value.trim()) {
                                    // Set the dropdown value to the custom text
                                    iomReSelect.value = customSubjectInput.value.trim();
                                }

                                // Handle Purpose "Others" case before submission  
                                if (iomPurposeSelect && iomOtherPurposeInput &&
                                    iomPurposeSelect.value === 'Others' && iomOtherPurposeInput.value.trim()) {
                                    // Keep the dropdown as "Others" and let the text field handle the custom value
                                    // The backend will use iom_other_purpose field for the custom value
                                }
                                
                                form.submit();
                            });

                            setInitialToDepartmentDisplay();
                            toggleFields();
                            setupAutoAssignment();
                        });
                    </script>
                    
                    <!-- Include Workflow Preview JavaScript -->
                    <script src="{{ asset('js/workflow-preview.js') }}"></script>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modal Alert Component -->
    <x-modal-alert />
</x-app-layout>