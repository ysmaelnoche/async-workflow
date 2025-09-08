<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Job Order: {{ $jobOrder->job_order_number }}
            </h2>
            <div class="flex space-x-2">
                {{-- Print Form Button - Only for PFMO department users and when job order has data --}}
                @if(auth()->user()->department && auth()->user()->department->dept_code === 'PFMO' && $jobOrder->request_description && !empty(trim($jobOrder->request_description)))
                    <a href="{{ route('job-orders.printable-form', $jobOrder) }}" 
                       target="_blank" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Form
                    </a>
                    <a href="{{ route('job-orders.printable-form', [$jobOrder, 'download' => 'pdf']) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download PDF
                    </a>
                @endif
                
                <a href="{{ route('job-orders.index') }}"
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Session Messages -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Job Order Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Job Order Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Job Order Number:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->job_order_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Control Number:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->control_number ?? 'Not assigned' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date Prepared:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->date_prepared ? $jobOrder->date_prepared->format('M j, Y') : '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                    <dd>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $jobOrder->status === 'Pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $jobOrder->status === 'In Progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $jobOrder->status === 'Completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $jobOrder->status === 'For Further Action' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $jobOrder->status }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requestor Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->requestor_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Department:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->department }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Work Information</h3>
                            <dl class="space-y-2">
                                @if($jobOrder->job_started_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Started:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->job_started_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @endif
                                @if($jobOrder->job_completed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->job_completed_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @endif
                                @if($jobOrder->work_duration)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration:</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->work_duration }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Description -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Request Description</h3>
                    <p class="text-gray-700 dark:text-gray-300">{{ $jobOrder->request_description }}</p>
                </div>
            </div>

            <!-- PFMO Work Details -->
            @if($jobOrder->findings || $jobOrder->actions_taken || $jobOrder->recommendations)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">PFMO Work Details</h3>
                    <div class="space-y-4">
                        @if($jobOrder->findings)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Findings:</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->findings }}</dd>
                        </div>
                        @endif
                        @if($jobOrder->actions_taken)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Actions Taken:</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->actions_taken }}</dd>
                        </div>
                        @endif
                        @if($jobOrder->recommendations)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Recommendations:</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->recommendations }}</dd>
                        </div>
                        @endif
                        @if($jobOrder->job_completed_by)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed By:</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->job_completed_by }}</dd>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Requestor Feedback -->
            @if($jobOrder->requestor_comments || $jobOrder->job_completed || $jobOrder->for_further_action)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Requestor Feedback</h3>
                    <div class="space-y-4">
                        <div class="flex space-x-4">
                            <div class="flex items-center">
                                <input type="checkbox" {{ $jobOrder->job_completed ? 'checked' : '' }} disabled 
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Job Completed</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" {{ $jobOrder->for_further_action ? 'checked' : '' }} disabled 
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">For Further Action</label>
                            </div>
                        </div>
                        @if($jobOrder->requestor_comments)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Comments:</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->requestor_comments }}</dd>
                        </div>
                        @endif
                        @if($jobOrder->requestor_signature_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Feedback Date:</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $jobOrder->requestor_signature_date->format('M j, Y') }}</dd>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Progress History -->
            @if($progressHistory && $progressHistory->count() > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Progress History</h3>
                    <div class="space-y-4">
                        @foreach($progressHistory as $progress)
                        <div class="border-l-4 border-blue-400 pl-4 py-2">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            @if($progress->progressUser)
                                                {{ $progress->progressUser->username ?? 'Unknown User' }}
                                            @else
                                                Unknown User
                                            @endif
                                        </span>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($progress->update_type ?? 'update') }}
                                        </span>
                                        @if($progress->percentage_complete > 0)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                            {{ $progress->percentage_complete }}%
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $progress->progress_note ?? 'No notes' }}</p>
                                    
                                    @if($progress->current_location)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        📍 {{ $progress->current_location }}
                                    </p>
                                    @endif
                                    
                                    @if($progress->issues_encountered)
                                    <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded border-l-2 border-red-400">
                                        <p class="text-xs text-red-700 dark:text-red-300">
                                            <strong>Issues:</strong> {{ $progress->issues_encountered }}
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($progress->estimated_time_remaining)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        ⏱️ Estimated time remaining: {{ $progress->estimated_time_remaining_formatted }}
                                    </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $progress->created_at->format('M j, Y') }}
                                    </span>
                                    <br>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $progress->created_at->format('g:i A') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons for PFMO Staff -->
            @if(Auth::user()->department && Auth::user()->department->dept_code === 'PFMO')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions</h3>
                        <div class="flex space-x-4">
                            @if($jobOrder->status === 'Pending')
                                <button onclick="startJob()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Start Job
                                </button>
                            @endif
                            @if($jobOrder->status === 'In Progress')
                                <button onclick="showProgressModal()" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Update Progress
                                </button>
                                <button onclick="showCompleteModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Complete Job
                                </button>
                            @endif
                            @if($jobOrder->status === 'Completed')
                                <span class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium bg-green-100 text-green-800">
                                    ✓ Job Completed
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Progress Update Modal -->
    <div id="progressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-10 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 max-h-screen overflow-y-auto">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 text-center">Update Progress</h3>
                <form id="progressForm" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Progress Note *</label>
                        <textarea name="progress_note" rows="3" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="Describe what you're currently working on..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Progress Percentage *</label>
                        <div class="mt-1 flex items-center space-x-2">
                            <input type="range" name="percentage_complete" min="0" max="100" value="0" class="flex-1" oninput="updateProgressValue(this.value)">
                            <span id="progressValue" class="text-sm font-medium text-gray-900 dark:text-gray-100 min-w-[40px]">0%</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Location</label>
                        <input type="text" name="current_location" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="e.g., Building A - 2nd Floor">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Issues Encountered</label>
                        <textarea name="issues_encountered" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="Any problems or obstacles..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Time Remaining (minutes)</label>
                        <input type="number" name="estimated_time_remaining" min="0" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" placeholder="e.g., 120">
                    </div>
                    <div class="flex justify-end space-x-2 pt-4">
                        <button type="button" onclick="hideProgressModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            Update Progress
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Job Modal -->
    <div id="completeJobModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Complete Job</h3>
                <form id="completeJobForm" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Findings</label>
                        <textarea name="findings" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Actions Taken *</label>
                        <textarea name="actions_taken" rows="3" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recommendations</label>
                        <textarea name="recommendations" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="hideCompleteModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Complete Job
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Start Job Confirmation Modal -->
    <div id="startJobModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Start Job Order
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to start this job order? This will change the status to "In Progress" and you will be able to add progress updates.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="confirmStartJob()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Yes, Start Job
                    </button>
                    <button type="button" onclick="hideStartJobModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Debug: Verify script is loading
        console.log('Job order script loaded successfully');
        
        // Enhanced notification functions using the new system
        window.showSuccessNotification = function(message, title = 'Success!') {
            return window.EnhancedNotifications.success(title, message, {
                actions: [
                    {
                        text: 'View Details',
                        primary: true,
                        handler: () => {
                            // Optional: Add specific action for job order success
                            console.log('Success notification action clicked');
                        }
                    }
                ]
            });
        };
        
        window.showErrorNotification = function(message, title = 'Error!') {
            return window.EnhancedNotifications.error(title, message, {
                persistent: false,
                duration: 8000,
                actions: [
                    {
                        text: 'Try Again',
                        primary: true,
                        handler: () => {
                            // Optional: Add retry logic
                            location.reload();
                        }
                    },
                    {
                        text: 'Dismiss',
                        primary: false,
                        handler: () => {}
                    }
                ]
            });
        };
        
        window.showInfoNotification = function(message, title = 'Information') {
            return window.EnhancedNotifications.info(title, message);
        };
        
        window.showWarningNotification = function(message, title = 'Warning!') {
            return window.EnhancedNotifications.warning(title, message, {
                duration: 7000
            });
        };
        
        // Legacy compatibility function
        window.showNotification = function(message, type = 'success') {
            const titles = {
                success: 'Success!',
                error: 'Error!',
                warning: 'Warning!',
                info: 'Information'
            };
            
            return window.EnhancedNotifications.show({
                type: type,
                title: titles[type] || 'Notification',
                message: message
            });
        };
        
        // Start Job Modal Functions
        window.startJob = function() {
            console.log('startJob function called');
            const modal = document.getElementById('startJobModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                console.error('startJobModal element not found');
            }
        };
        
        window.hideStartJobModal = function() {
            document.getElementById('startJobModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        };
        
        window.confirmStartJob = function() {
            // Show loading state
            const confirmBtn = document.querySelector('#startJobModal button[onclick="confirmStartJob()"]');
            const originalText = confirmBtn.textContent;
            confirmBtn.textContent = 'Starting...';
            confirmBtn.disabled = true;

            fetch(`{{ route('job-orders.start', $jobOrder) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Start Job Response:', response);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Start Job Data:', data);
                window.hideStartJobModal();
                if (data.success) {
                    window.showSuccessNotification('Job started successfully!');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    window.showErrorNotification(data.message || 'An error occurred while starting the job.');
                }
            })
            .catch(error => {
                window.hideStartJobModal();
                console.error('Start Job Error:', error);
                window.showErrorNotification('An error occurred while starting the job. Please try again.');
            })
            .finally(() => {
                confirmBtn.textContent = originalText;
                confirmBtn.disabled = false;
            });
        };
        
        // Complete Job Modal Functions
        window.showCompleteModal = function() {
            document.getElementById('completeJobModal').classList.remove('hidden');
        };

        window.hideCompleteModal = function() {
            document.getElementById('completeJobModal').classList.add('hidden');
        };

        // Progress Modal Functions
        window.showProgressModal = function() {
            document.getElementById('progressModal').classList.remove('hidden');
        };

        window.hideProgressModal = function() {
            document.getElementById('progressModal').classList.add('hidden');
        };

        window.updateProgressValue = function(value) {
            document.getElementById('progressValue').textContent = value + '%';
        };

        // Form Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Complete Job Form
            const completeJobForm = document.getElementById('completeJobForm');
            if (completeJobForm) {
                completeJobForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData);
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = 'Completing Job...';
                    submitBtn.disabled = true;
                    
                    fetch(`{{ route('job-orders.complete', $jobOrder) }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('Complete Job Response:', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Complete Job Data:', data);
                        window.hideCompleteModal();
                        if (data.success) {
                            window.showSuccessNotification('Job completed successfully!');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            window.showErrorNotification(data.message || 'An error occurred while completing the job.');
                        }
                    })
                    .catch(error => {
                        console.error('Complete Job Error:', error);
                        window.showErrorNotification('An error occurred while completing the job. Please try again.');
                    })
                    .finally(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }

            // Progress Update Form  
            const progressForm = document.getElementById('progressForm');
            if (progressForm) {
                progressForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData);
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;
                    submitBtn.textContent = 'Updating Progress...';
                    submitBtn.disabled = true;
                    
                    fetch(`{{ route('job-orders.progress', $jobOrder) }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        console.log('Progress Update Response:', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Progress Update Data:', data);
                        window.hideProgressModal();
                        if (data.success) {
                            window.showSuccessNotification(`Progress updated to ${data.percentage}%!`);
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            window.showErrorNotification(data.message || 'An error occurred while updating progress.');
                        }
                    })
                    .catch(error => {
                        console.error('Progress Update Error:', error);
                        window.showErrorNotification('An error occurred while updating progress. Please try again.');
                    })
                    .finally(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }

            // Auto-open modal based on URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const openModal = urlParams.get('openModal');
            
            if (openModal === 'complete') {
                // Auto-open the complete job modal if the job order is completed
                @if($jobOrder->status === 'Completed')
                    window.showCompleteModal();
                @endif
            }
        });
    </script>
</x-app-layout>
