<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Track Request') }} - ID: {{ $formRequest->form_id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100 space-y-6">
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    {{-- Request Timeline --}}
                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-semibold mb-4">Request Timeline</h3>
                        @if($formRequest->status === 'Approved')
                            <div class="mb-4 flex justify-end">
                                <a href="{{ route('request.print', $formRequest->form_id) }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print Request
                                </a>
                            </div>
                        @endif
                        <div class="relative">
                            {{-- Timeline line --}}
                            <div class="absolute h-full w-1 bg-gradient-to-b from-blue-500 via-blue-300 to-gray-200 left-4 top-4"></div>
                            
                            <ul class="space-y-8 relative">
                                {{-- Initial submission --}}
                                <li class="flex items-start">
                                    <div class="flex items-center justify-center">
                                        <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-6">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">Request Submitted by {{ $formRequest->requester->username }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $formRequest->date_submitted->format('M j, Y') }} at {{ $formRequest->date_submitted->format('g:i A') }}
                                        </div>
                                    </div>
                                </li>

                                {{-- Approval history --}}
                                @foreach ($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted' && $approval->action !== 'Evaluate')
                                        <li class="flex items-start">
                                            <div class="flex items-center justify-center">
                                                <div class="
                                                    @if($approval->action === 'Rejected') bg-red-500
                                                    @elseif($approval->action === 'Approved') bg-green-500
                                                    @elseif($approval->action === 'Send Feedback') bg-green-500
                                                    @else bg-blue-500
                                                    @endif
                                                    rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                    @if($approval->action === 'Rejected')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    @elseif($approval->action === 'Approved')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    @elseif($approval->action === 'Send Feedback')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-6">
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $approval->action }} by {{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}
                                                    @if($approval->approver->position === 'Head')
                                                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Head)</span>
                                                    @endif
                                                </div>
                                                @if($approval->comments)
                                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                                        "{{ $approval->comments }}"
                                                    </div>
                                                @endif
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($approval->action_date)->format('M j, Y') }} at {{ \Carbon\Carbon::parse($approval->action_date)->format('g:i A') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Show Completed status after all approvals --}}
                                @if($formRequest->status === 'Approved')
                                    <li class="flex items-start">
                                        <div class="flex items-center justify-center">
                                            <div class="bg-green-500 rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-6">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                Completed
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ \Carbon\Carbon::parse($formRequest->approvals->sortByDesc('action_date')->first()->action_date)->setTimezone('Asia/Manila')->format('M j, Y g:i A') }}
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                {{-- Current status if not completed --}}
                                @if(!in_array($formRequest->status, ['Approved', 'Rejected', 'Cancelled']))
                                    <li class="flex items-start">
                                        <div class="flex items-center justify-center">
                                            <div class="bg-yellow-500 rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-6">
                                            @if($formRequest->status === 'Under Sub-Department Evaluation')
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">Currently Under Sub-Department Evaluation</div>
                                                @php
                                                    $subDepartment = null;
                                                    if($formRequest->assigned_sub_department) {
                                                        $subDepartment = $formRequest->assigned_sub_department;
                                                    } elseif($currentApprover = App\Models\User::with('employeeInfo', 'department')->find($formRequest->current_approver_id)) {
                                                        if($currentApprover->department) {
                                                            $subDepartment = $currentApprover->department->dept_name;
                                                        }
                                                    }
                                                @endphp
                                                @if($subDepartment)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        Sub-Department: {{ ucwords(str_replace('_', ' ', $subDepartment)) }}
                                                    </div>
                                                @endif
                                            @else
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">Currently {{ $formRequest->status }}</div>
                                                @if($formRequest->status === 'Under Sub-Department Evaluation')
                                                    {{-- For PFMO sub-department evaluation, show the sub-department name --}}
                                                    @php
                                                        $subDeptName = 'PFMO Sub-Department';
                                                        if ($formRequest->assigned_sub_department) {
                                                            switch($formRequest->assigned_sub_department) {
                                                                case 'warehouse':
                                                                    $subDeptName = 'PFMO Warehouse Section (ROY MORALES)';
                                                                    break;
                                                                case 'construction':
                                                                    $subDeptName = 'PFMO Construction Section (ALBERT AYAP)';
                                                                    break;
                                                                case 'general_services':
                                                                    $subDeptName = 'PFMO General Services (ROS BALTAZAR)';
                                                                    break;
                                                                case 'housekeeping':
                                                                    $subDeptName = 'PFMO Housekeeping (VIRGILIO VITERBO)';
                                                                    break;
                                                                case 'transportation':
                                                                    $subDeptName = 'PFMO Transportation Section (MICHAEL DELA CRUZ)';
                                                                    break;
                                                                default:
                                                                    $subDeptName = 'PFMO ' . ucwords(str_replace('_', ' ', $formRequest->assigned_sub_department)) . ' Section';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        Awaiting action from {{ $subDeptName }}
                                                    </div>
                                                @elseif($currentApprover = App\Models\User::with('employeeInfo', 'department')->find($formRequest->current_approver_id))
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        @if($currentApprover->position === 'VPAA' || ($currentApprover->department && $currentApprover->department->dept_code === 'VPAA'))
                                                            Awaiting action from {{ $currentApprover->employeeInfo->FirstName ?? '' }} {{ $currentApprover->employeeInfo->LastName ?? '' }} ({{ $currentApprover->username }})
                                                        @else
                                                            Awaiting action from {{ $currentApprover->username }}
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- Job Order Status (if exists) --}}
                    @if($formRequest->jobOrder)
                        @php
                            $jobOrder = $formRequest->jobOrder;
                            $statusConfig = [
                                'Pending' => [
                                    'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
                                    'border' => 'border-yellow-200 dark:border-yellow-800',
                                    'icon_color' => 'text-yellow-600',
                                    'text_color' => 'text-yellow-800 dark:text-yellow-200',
                                    'sub_color' => 'text-yellow-600 dark:text-yellow-300',
                                    'title' => 'Job Order Pending',
                                    'description' => 'Your job order is waiting to be processed by PFMO.',
                                    'show_fillup' => false // No fill-up when pending
                                ],
                                'In Progress' => [
                                    'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                                    'border' => 'border-blue-200 dark:border-blue-800',
                                    'icon_color' => 'text-blue-600',
                                    'text_color' => 'text-blue-800 dark:text-blue-200',
                                    'sub_color' => 'text-blue-600 dark:text-blue-300',
                                    'title' => 'Job Order In Progress',
                                    'description' => 'PFMO is currently working on your request.',
                                    'show_fillup' => false
                                ],
                                'Completed' => [
                                    'bg' => 'bg-green-50 dark:bg-green-900/20',
                                    'border' => 'border-green-200 dark:border-green-800',
                                    'icon_color' => 'text-green-600',
                                    'text_color' => 'text-green-800 dark:text-green-200',
                                    'sub_color' => 'text-green-600 dark:text-green-300',
                                    'title' => 'Job Order Completed',
                                    'description' => 'Your job order has been completed successfully.',
                                    'show_fillup' => (empty($jobOrder->request_description) || trim($jobOrder->request_description) === '') || 
                                                   (!$jobOrder->requestor_comments) // Show if form not filled OR feedback not given
                                ]
                            ];
                            $config = $statusConfig[$jobOrder->status] ?? $statusConfig['Pending'];
                        @endphp
                        
                        <div class="mb-4">
                            <div class="{{ $config['bg'] }} border {{ $config['border'] }} rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg class="w-5 h-5 {{ $config['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($jobOrder->status === 'Completed')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @elseif($jobOrder->status === 'In Progress')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                @endif
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium {{ $config['text_color'] }}">
                                                {{ $config['title'] }}
                                            </div>
                                            <div class="text-sm {{ $config['sub_color'] }}">
                                                Job Order #{{ $jobOrder->job_order_number }} - {{ $config['description'] }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        {{-- Combined Fill-up & Feedback Button (for completed jobs only) --}}
                                        @if($jobOrder->status === 'Completed' && ($config['show_fillup'] ?? false))
                                            <button onclick="openJobOrderModal()" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-md transition duration-150 ease-in-out">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Complete Job Order
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Progress Information --}}
                                @if($jobOrder->status === 'In Progress' || $jobOrder->status === 'Completed')
                                    @php
                                        $latestProgress = $jobOrder->latestProgress;
                                        $progressPercentage = $jobOrder->progressPercentage;
                                        $estimatedTimeFormatted = $jobOrder->estimatedTimeRemainingFormatted;
                                    @endphp
                                    
                                    @if($latestProgress)
                                        <div class="mt-4 pt-4 border-t {{ $config['border'] }}">
                                            {{-- Progress Bar --}}
                                            <div class="mb-3">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-sm font-medium {{ $config['text_color'] }}">Progress</span>
                                                    <span class="text-sm font-bold {{ $config['text_color'] }}">{{ $progressPercentage }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="h-2 rounded-full transition-all duration-500 ease-out
                                                        @if($jobOrder->status === 'Completed') bg-green-600
                                                        @else bg-blue-600
                                                        @endif" 
                                                        style="width: {{ $progressPercentage }}%">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Latest Update Information --}}
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                @if($latestProgress->progress_note)
                                                    <div class="md:col-span-2">
                                                        <div class="flex items-start">
                                                            <svg class="w-4 h-4 {{ $config['icon_color'] }} mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            <div>
                                                                <span class="font-medium {{ $config['text_color'] }}">Latest Update:</span>
                                                                <p class="{{ $config['sub_color'] }} mt-1">{{ $latestProgress->progress_note }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($latestProgress->current_location)
                                                    <div>
                                                        <div class="flex items-center">
                                                            <svg class="w-4 h-4 {{ $config['icon_color'] }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            </svg>
                                                            <span class="font-medium {{ $config['text_color'] }}">Location:</span>
                                                        </div>
                                                        <p class="{{ $config['sub_color'] }} ml-6">{{ $latestProgress->current_location }}</p>
                                                    </div>
                                                @endif

                                                @if($estimatedTimeFormatted)
                                                    <div>
                                                        <div class="flex items-center">
                                                            <svg class="w-4 h-4 {{ $config['icon_color'] }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="font-medium {{ $config['text_color'] }}">
                                                                @if($jobOrder->status === 'Completed')
                                                                    Completion Time:
                                                                @else
                                                                    Estimated Remaining:
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <p class="{{ $config['sub_color'] }} ml-6">{{ $estimatedTimeFormatted }}</p>
                                                    </div>
                                                @endif

                                                @if($latestProgress->updated_at)
                                                    <div class="md:col-span-2">
                                                        <div class="flex items-center">
                                                            <svg class="w-4 h-4 {{ $config['icon_color'] }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span class="font-medium {{ $config['text_color'] }}">Last Updated:</span>
                                                            <span class="{{ $config['sub_color'] }} ml-2">{{ $latestProgress->updated_at->format('M j, Y g:i A') }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- View Progress History Button --}}
                                            @if($jobOrder->progressUpdates->count() > 1)
                                                <div class="mt-3 pt-3 border-t {{ $config['border'] }}">
                                                    <button onclick="toggleProgressHistory()" 
                                                            class="inline-flex items-center text-sm {{ $config['text_color'] }} hover:{{ $config['icon_color'] }} transition duration-150 ease-in-out">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                        </svg>
                                                        <span id="progressHistoryToggleText">View Progress History ({{ $jobOrder->progressUpdates->count() }} updates)</span>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Progress History (Collapsible) --}}
                                        @if($jobOrder->progressUpdates->count() > 1)
                                            <div id="progressHistory" class="hidden mt-4 pt-4 border-t {{ $config['border'] }}">
                                                <h4 class="text-sm font-semibold {{ $config['text_color'] }} mb-3">Complete Progress History</h4>
                                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                                    @foreach($jobOrder->progressHistoryForTrackView as $progress)
                                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div class="flex items-center">
                                                                    <div class="w-8 h-2 rounded-full mr-3
                                                                        @if($progress->percentage_complete >= 100) bg-green-500
                                                                        @elseif($progress->percentage_complete >= 75) bg-blue-500
                                                                        @elseif($progress->percentage_complete >= 50) bg-yellow-500
                                                                        @else bg-gray-400
                                                                        @endif">
                                                                    </div>
                                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $progress->percentage_complete }}% Complete</span>
                                                                </div>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $progress->updated_at->format('M j, g:i A') }}</span>
                                                            </div>
                                                            
                                                            @if($progress->progress_note)
                                                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $progress->progress_note }}</p>
                                                            @endif
                                                            
                                                            <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 dark:text-gray-400">
                                                                @if($progress->current_location)
                                                                    <div>
                                                                        <span class="font-medium">Location:</span> {{ $progress->current_location }}
                                                                    </div>
                                                                @endif
                                                                @if($progress->estimated_time_remaining)
                                                                    <div>
                                                                        <span class="font-medium">Est. Time:</span> {{ $progress->estimatedTimeRemainingFormatted }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- IOM Specific Details --}}
                    @if ($formRequest->form_type === 'IOM' && $formRequest->iomDetails)
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold">IOM Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                                <p><strong>To Department:</strong> {{ $formRequest->toDepartment->dept_name ?? 'N/A' }} ({{ $formRequest->toDepartment->dept_code ?? 'N/A' }})</p>
                                <p><strong>Subject/Re:</strong> {{ $formRequest->title }}</p>
                                <p><strong>Date Needed:</strong> {{ $formRequest->iomDetails->date_needed ? \Carbon\Carbon::parse($formRequest->iomDetails->date_needed)->format('M j, Y') : 'N/A' }}</p>
                                <p><strong>Priority:</strong> {{ $formRequest->iomDetails->priority ?? 'N/A' }}</p>
                                <p class="md:col-span-2"><strong>Purpose:</strong> {{ $formRequest->iomDetails->purpose ?? 'N/A' }}</p>
                                <div class="md:col-span-2">
                                    <p class="font-semibold">Description/Body:</p>
                                    <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md whitespace-pre-wrap">{{ $formRequest->iomDetails->body ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Signatures Section - Only show final approvals/rejections visible to requestor --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Signatures</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if(in_array($approval->action, ['Approved', 'Rejected', 'Send Feedback']) && ($approval->signature_data || $approval->signature_name))
                                        <div class="signature-card bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            @if($approval->signature_data && (strpos($approval->signature_data, 'data:image/') === 0 || filter_var($approval->signature_data, FILTER_VALIDATE_URL)))
                                                <div class="signature-image-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-6 py-2">
                                                    <img src="{{ $approval->signature_data }}"
                                                         alt="Digital Signature"
                                                         class="max-h-20 object-contain">
                                                </div>
                                            @elseif($approval->signature_style_id && $approval->signature_name)
                                                <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-6">
                                                    <div class="text-xl font-signature text-center" style="font-family: '{{ \App\Models\SignatureStyle::find($approval->signature_style_id)->font_family }}', cursive; line-height: 1.2; letter-spacing: 0.5px; padding: 10px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                                        {{ strtoupper($approval->signature_name) }}
                                                    </div>
                                                </div>
                                            @elseif($approval->signature_name && $approval->approver && $approval->approver->signatureStyle)
                                                <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-6">
                                                    <div class="text-xl font-signature text-center" style="font-family: '{{ $approval->approver->signatureStyle->font_family }}', cursive; line-height: 1.2; letter-spacing: 0.5px; padding: 10px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                                        {{ strtoupper($approval->signature_name) }}
                                                    </div>
                                                </div>
                                            @elseif($approval->signature_name)
                                                <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-6">
                                                    <div class="text-xl font-signature text-center" style="font-family: 'Dancing Script', cursive; line-height: 1.2; letter-spacing: 0.5px; padding: 10px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                                        {{ strtoupper($approval->signature_name) }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-6">
                                                    <div class="text-sm text-gray-500 italic">No signature image/style</div>
                                                </div>
                                            @endif
                                            <div class="mt-3 text-center">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}
                                                </p>
                                                <div class="flex items-center justify-center gap-2 mt-1">
                                                    <span class="text-xs px-2 py-1 rounded-full {{ 
                                                        $approval->action === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                        ($approval->action === 'Rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') 
                                                    }}">
                                                        {{ $approval->action }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ \Carbon\Carbon::parse($approval->action_date)->format('M j, Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Leave Specific Details --}}
                    @if ($formRequest->form_type === 'Leave' && $formRequest->leaveDetails)
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold mb-4">Leave Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="font-medium">Leave Type:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ ucfirst($formRequest->leaveDetails->leave_type) }} Leave</p>
                                </div>
                                <div>
                                    <p class="font-medium">Duration:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ $formRequest->leaveDetails->days }} day(s)</p>
                                </div>
                                <div>
                                    <p class="font-medium">Start Date:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ $formRequest->leaveDetails->start_date->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <p class="font-medium">End Date:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ $formRequest->leaveDetails->end_date->format('F j, Y') }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="font-medium">Description / Reason:</p>
                                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap bg-gray-50 dark:bg-gray-700 p-3 rounded-md mt-1">{{ $formRequest->leaveDetails->description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Signatures Section -->
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold mb-4">Signatures</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted')
                                    <div class="border rounded-lg p-4 flex flex-col items-center justify-center">
                                        @if($approval->signature_data && (strpos($approval->signature_data, 'data:image/') === 0 || filter_var($approval->signature_data, FILTER_VALIDATE_URL)))
                                            <div class="signature-image-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                <img src="{{ $approval->signature_data }}" alt="Digital Signature" class="max-h-20 object-contain">
                                            </div>
                                        @elseif($approval->signature_name && $approval->approver && $approval->approver->signatureStyle)
                                            <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-2">
                                                <div class="text-xl font-signature text-center" style="font-family: '{{ $approval->approver->signatureStyle->font_family }}', cursive; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ strtoupper($approval->signature_name) }}</div>
                                            </div>
                                        @elseif($approval->signature_name)
                                            <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-2">
                                                <div class="text-xl font-signature text-center" style="font-family: 'Dancing Script', cursive; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ strtoupper($approval->signature_name) }}</div>
                                            </div>
                                        @else
                                            <div class="signature-text-container h-24 flex items-center justify-center border-b border-gray-100 dark:border-gray-700 px-2">
                                                 <div class="text-sm text-gray-500 italic">No signature image/style</div>
                                            </div>
                                        @endif
                                        <div class="text-center">
                                            <p class="font-medium">{{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}</p>
                                            <p class="text-sm text-gray-600">
                                                <span class="px-2 py-1 rounded {{ 
                                                    $approval->action === 'Approved' ? 'bg-green-100 text-green-800' : 
                                                    ($approval->action === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') 
                                                }}">
                                                    {{ $approval->action }}
                                                </span>
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">{{ $approval->action_date->format('M j, Y') }}</p>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Back to Dashboard Link --}}
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" 
                            class="inline-flex items-center px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg transition-all duration-200 hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Combined Job Order Fill-up & Feedback Modal --}}
    @if($formRequest->jobOrder && $formRequest->jobOrder->status === 'Completed')
    <div id="jobOrderModal" class="fixed inset-0 bg-gray-900 bg-opacity-30 hidden overflow-y-auto h-full w-full z-70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full mx-4 relative">
            {{-- Header with gradient based on current step --}}
            <div id="modalHeader" class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 id="modalTitle" class="text-lg font-semibold text-white">Complete Job Order</h3>
                        <p id="modalSubtitle" class="text-green-100 text-sm">{{ $formRequest->jobOrder->job_order_number }}</p>
                    </div>
                    <button type="button" onclick="closeJobOrderModal()" class="text-white hover:text-gray-200 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                {{-- Progress Steps --}}
                <div class="mt-4">
                    <div class="flex items-center space-x-4">
                        <div id="step1Indicator" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-white text-green-600 rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                            <span class="text-white font-medium">Fill-up Details</span>
                        </div>
                        <div class="flex-1 h-1 bg-green-300 rounded"></div>
                        <div id="step2Indicator" class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-300 text-green-600 rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                            <span class="text-green-100 font-medium">Provide Feedback</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Content --}}
            <div class="p-6">
                {{-- Step 1: Fill-up Job Order Details --}}
                <div id="step1Content" class="step-content">
                    <form id="jobOrderForm" action="{{ route('job-order.fill-up', $formRequest->jobOrder) }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            {{-- Pre-filled Information --}}
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Request Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Requestor:</span> 
                                        <span class="text-gray-900 dark:text-gray-100">{{ $formRequest->requester->employeeInfo->first_name ?? '' }} {{ $formRequest->requester->employeeInfo->last_name ?? '' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Department:</span> 
                                        <span class="text-gray-900 dark:text-gray-100">{{ $formRequest->requester->department->dept_name ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Control Number:</span> 
                                        <span class="text-gray-900 dark:text-gray-100">{{ $formRequest->jobOrder->control_number ?? 'To be assigned' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Date Completed:</span> 
                                        <span class="text-gray-900 dark:text-gray-100">{{ now()->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Service Types --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Service Types Received</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="assistance" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Assistance</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="repair_repaint" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Repair/Repaint</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="installation" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Installation</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="cleaning" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Cleaning</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="check_up_inspection" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Check-up/Inspection</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="construction_fabrication" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Construction/Fabrication</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="pull_out_transfer" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Pull-out/Transfer</span>
                                    </label>
                                    <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="replacement" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Replacement</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Request Description --}}
                            <div>
                                <label for="request_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Request Description <span class="text-red-500">*</span>
                                </label>
                                <textarea name="request_description" id="request_description" rows="4" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Please confirm the work that was completed..."></textarea>
                            </div>

                            {{-- Digital Signature --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Digital Signature <span class="text-red-500">*</span>
                                </label>
                                <div class="border border-gray-300 dark:border-gray-600 rounded-md p-4 bg-gray-50 dark:bg-gray-700">
                                    <div class="mb-3">
                                        <label for="signature_style" class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Choose signature style:</label>
                                        <select name="signature_style" id="signature_style" required onchange="updateSignaturePreview()"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-gray-100">
                                            <option value="">Select signature style</option>
                                            <option value="style1">Elegant Script</option>
                                            <option value="style2">Handwritten</option>
                                            <option value="style3">Artistic</option>
                                            <option value="style4">Flowing</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <div id="signaturePreview" class="h-16 border border-dashed border-gray-300 dark:border-gray-600 rounded flex items-center justify-center text-gray-500 dark:text-gray-400">
                                            Signature preview will appear here
                                        </div>
                                    </div>
                                    <input type="hidden" name="requestor_signature" id="requestor_signature">
                                </div>
                            </div>
                        </div>

                        {{-- Step 1 Footer --}}
                        <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-600 space-x-3 mt-6">
                            <button type="button" onclick="closeJobOrderModal()" 
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition duration-150">
                                Cancel
                            </button>
                            <button type="button" onclick="goToStep2()" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-150 font-medium">
                                Next: Provide Feedback
                                <svg class="w-4 h-4 ml-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Step 2: Feedback --}}
                <div id="step2Content" class="step-content hidden">
                    <form id="feedbackForm" action="{{ route('job-order.feedback', $formRequest->jobOrder) }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div class="text-center">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="mt-2 text-lg font-medium text-gray-900 dark:text-gray-100">Job Order Completed!</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please share your experience with the completed work</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    How satisfied are you with the completed work?
                                </label>
                                <div class="flex justify-center space-x-2">
                                    @for($i = 1; $i <= 5; $i++)
                                    <label class="flex flex-col items-center">
                                        <input type="radio" name="satisfaction_rating" value="{{ $i }}" required class="sr-only">
                                        <div class="star-rating cursor-pointer text-3xl text-gray-300 hover:text-yellow-400 transition-colors" onclick="setRating({{ $i }})">
                                            ⭐
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $i }}</span>
                                    </label>
                                    @endfor
                                </div>
                                <div class="text-center mt-2">
                                    <span id="ratingText" class="text-sm text-gray-600 dark:text-gray-400">Click stars to rate</span>
                                </div>
                            </div>

                            <div>
                                <label for="requestor_comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Comments or Feedback
                                </label>
                                <textarea name="requestor_comments" id="requestor_comments" rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Share your experience with the job order completion..."></textarea>
                            </div>

                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                                <label class="flex items-start">
                                    <input type="checkbox" name="for_further_action" value="1" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 mt-1">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-amber-800 dark:text-amber-300">This job order requires further action</span>
                                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Check this if additional work is needed</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Step 2 Footer --}}
                        <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-600 mt-6">
                            <button type="button" onclick="goToStep1()" 
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition duration-150">
                                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Back
                            </button>
                            <button type="button" onclick="submitBothForms()" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-150 font-medium">
                                Complete Job Order
                                <svg class="w-4 h-4 ml-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

</x-app-layout>

{{-- JavaScript for Two-Step Modal --}}
<script>
// Progress History Toggle Function
function toggleProgressHistory() {
    const historyDiv = document.getElementById('progressHistory');
    const toggleText = document.getElementById('progressHistoryToggleText');
    const toggleIcon = event.target.querySelector('svg') || event.target.parentElement.querySelector('svg');
    
    if (historyDiv.classList.contains('hidden')) {
        historyDiv.classList.remove('hidden');
        toggleText.textContent = toggleText.textContent.replace('View', 'Hide');
        if (toggleIcon) {
            toggleIcon.style.transform = 'rotate(90deg)';
        }
    } else {
        historyDiv.classList.add('hidden');
        toggleText.textContent = toggleText.textContent.replace('Hide', 'View');
        if (toggleIcon) {
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    }
}

// Two-Step Modal Management
let currentStep = 1;

// Modal Functions
function openJobOrderModal() {
    document.getElementById('jobOrderModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    goToStep1(); // Always start at step 1
}

function closeJobOrderModal() {
    document.getElementById('jobOrderModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    currentStep = 1;
    resetForms();
}

function goToStep1() {
    currentStep = 1;
    updateStepDisplay();
}

function goToStep2() {
    // Validate step 1 first
    if (!validateStep1()) {
        return;
    }
    
    currentStep = 2;
    updateStepDisplay();
}

function updateStepDisplay() {
    const step1Content = document.getElementById('step1Content');
    const step2Content = document.getElementById('step2Content');
    const step1Indicator = document.getElementById('step1Indicator');
    const step2Indicator = document.getElementById('step2Indicator');
    const modalHeader = document.getElementById('modalHeader');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    
    if (currentStep === 1) {
        // Show step 1
        step1Content.classList.remove('hidden');
        step2Content.classList.add('hidden');
        
        // Update indicators
        step1Indicator.querySelector('div').className = 'w-8 h-8 bg-white text-green-600 rounded-full flex items-center justify-center text-sm font-semibold';
        step1Indicator.querySelector('span').className = 'text-white font-medium';
        step2Indicator.querySelector('div').className = 'w-8 h-8 bg-green-300 text-green-600 rounded-full flex items-center justify-center text-sm font-semibold';
        step2Indicator.querySelector('span').className = 'text-green-100 font-medium';
        
        // Update header
        modalHeader.className = 'bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4 rounded-t-xl';
        modalTitle.textContent = 'Fill-up Job Order Details';
        modalSubtitle.textContent = 'Step 1 of 2: Service Information';
    } else {
        // Show step 2
        step1Content.classList.add('hidden');
        step2Content.classList.remove('hidden');
        
        // Update indicators
        step1Indicator.querySelector('div').className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-semibold';
        step1Indicator.querySelector('span').className = 'text-green-100 font-medium';
        step2Indicator.querySelector('div').className = 'w-8 h-8 bg-white text-green-600 rounded-full flex items-center justify-center text-sm font-semibold';
        step2Indicator.querySelector('span').className = 'text-white font-medium';
        
        // Update header  
        modalHeader.className = 'bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 rounded-t-xl';
        modalTitle.textContent = 'Provide Feedback';
        modalSubtitle.textContent = 'Step 2 of 2: Share Your Experience';
    }
}

function validateStep1() {
    const description = document.getElementById('request_description').value.trim();
    const signature = document.getElementById('requestor_signature').value.trim();
    
    if (!description) {
        alert('Please provide a request description.');
        document.getElementById('request_description').focus();
        return false;
    }
    
    if (!signature) {
        alert('Please select a signature style.');
        document.getElementById('signature_style').focus();
        return false;
    }
    
    return true;
}

function resetForms() {
    document.getElementById('jobOrderForm').reset();
    document.getElementById('feedbackForm').reset();
    document.getElementById('signaturePreview').innerHTML = 'Signature preview will appear here';
    document.getElementById('requestor_signature').value = '';
    resetRating();
}

// Signature Preview Function
function updateSignaturePreview() {
    const style = document.getElementById('signature_style').value;
    const preview = document.getElementById('signaturePreview');
    const hiddenInput = document.getElementById('requestor_signature');
    
    const userName = '{{ $formRequest->requester->employeeInfo->first_name ?? "" }} {{ $formRequest->requester->employeeInfo->last_name ?? "" }}';
    
    if (style && userName) {
        const styleMap = {
            'style1': { fontFamily: 'Mr Dafoe, cursive', fontSize: '24px' },
            'style2': { fontFamily: 'Homemade Apple, cursive', fontSize: '20px' },
            'style3': { fontFamily: 'Pacifico, cursive', fontSize: '22px' },
            'style4': { fontFamily: 'Dancing Script, cursive', fontSize: '26px' }
        };
        
        const selectedStyle = styleMap[style];
        preview.innerHTML = `<div style="font-family: ${selectedStyle.fontFamily}; font-size: ${selectedStyle.fontSize}; color: #1f2937;">${userName}</div>`;
        preview.classList.remove('text-gray-500');
        preview.classList.add('text-gray-900');
        
        hiddenInput.value = `${userName}|${style}`;
    } else {
        preview.innerHTML = 'Signature preview will appear here';
        preview.classList.add('text-gray-500');
        preview.classList.remove('text-gray-900');
        hiddenInput.value = '';
    }
}

// Star Rating Functions
let currentRating = 0;

function setRating(rating) {
    currentRating = rating;
    const stars = document.querySelectorAll('.star-rating');
    const ratingText = document.getElementById('ratingText');
    const ratingInput = document.querySelector('input[name="satisfaction_rating"][value="' + rating + '"]');
    
    if (ratingInput) {
        ratingInput.checked = true;
    }
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
    
    const ratingLabels = ['', 'Very Poor', 'Poor', 'Average', 'Good', 'Excellent'];
    ratingText.textContent = ratingLabels[rating] || 'Click stars to rate';
}

function resetRating() {
    currentRating = 0;
    const stars = document.querySelectorAll('.star-rating');
    const ratingText = document.getElementById('ratingText');
    
    stars.forEach(star => {
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300');
    });
    
    if (ratingText) {
        ratingText.textContent = 'Click stars to rate';
    }
}

// Combined Form Submission
function submitBothForms() {
    // Validate feedback form
    const rating = document.querySelector('input[name="satisfaction_rating"]:checked');
    if (!rating) {
        alert('Please provide a satisfaction rating.');
        return;
    }
    
    // Create a combined form data
    const jobOrderForm = document.getElementById('jobOrderForm');
    const feedbackForm = document.getElementById('feedbackForm');
    
    const formData = new FormData();
    
    // Add job order data
    const jobOrderData = new FormData(jobOrderForm);
    for (let [key, value] of jobOrderData.entries()) {
        formData.append(key, value);
    }
    
    // Add feedback data
    const feedbackData = new FormData(feedbackForm);
    for (let [key, value] of feedbackData.entries()) {
        formData.append(key, value);
    }
    
    // Submit via AJAX or create a combined route
    // For now, let's submit both forms sequentially
    submitJobOrderAndFeedback(formData);
}

async function submitJobOrderAndFeedback(formData) {
    try {
        // Show loading state
        const submitBtn = document.querySelector('button[onclick="submitBothForms()"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';
        submitBtn.disabled = true;
        
        // Submit job order first
        const jobOrderResponse = await fetch(document.getElementById('jobOrderForm').action, {
            method: 'POST',
            body: new FormData(document.getElementById('jobOrderForm')),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        if (jobOrderResponse.ok) {
            // Submit feedback
            const feedbackResponse = await fetch(document.getElementById('feedbackForm').action, {
                method: 'POST',
                body: new FormData(document.getElementById('feedbackForm')),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            if (feedbackResponse.ok) {
                // Success - reload page
                location.reload();
            } else {
                throw new Error('Failed to submit feedback');
            }
        } else {
            throw new Error('Failed to submit job order details');
        }
        
    } catch (error) {
        console.error('Submission error:', error);
        alert('There was an error submitting your information. Please try again.');
        
        // Reset button
        const submitBtn = document.querySelector('button[onclick="submitBothForms()"]');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const jobOrderModal = document.getElementById('jobOrderModal');
    
    if (event.target == jobOrderModal) {
        closeJobOrderModal();
    }
}
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Mr+Dafoe&family=Homemade+Apple&family=Pacifico&family=Dancing+Script&display=swap');
</style>