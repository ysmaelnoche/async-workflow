@extends('layouts.app')

@section('title', 'PFMO Facility Requests - Enhanced Workflow')

@push('styles')
<style>
    .request-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .request-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .request-card.pending-pfmo {
        border-left-color: #007bff;
    }
    
    .request-card.under-evaluation {
        border-left-color: #ffc107;
    }
    
    .request-card.awaiting-decision {
        border-left-color: #fd7e14;
    }
    
    .request-card.approved {
        border-left-color: #28a745;
    }
    
    .request-card.rejected {
        border-left-color: #dc3545;
    }
    
    .sub-department-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.375rem;
        background-color: #e0f2fe;
        color: #0277bd;
    }
    
    .filter-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .enhanced-workflow-badge {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header with Enhanced Workflow Badge -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">PFMO Facility Requests</h1>
            <p class="text-gray-600 mt-1">Enhanced workflow with automated sub-department routing</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <span class="enhanced-workflow-badge">
                ✨ Enhanced Workflow Active
            </span>
            
            <div class="bg-white rounded-lg px-4 py-2 shadow-sm border">
                <div class="text-sm text-gray-600">Total Requests</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters -->
    <div class="filter-section">
        <form method="GET" action="{{ route('pfmo.facility-requests') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Under Sub-Department Evaluation" {{ request('status') === 'Under Sub-Department Evaluation' ? 'selected' : '' }}>Under Evaluation</option>
                        <option value="Awaiting PFMO Decision" {{ request('status') === 'Awaiting PFMO Decision' ? 'selected' : '' }}>Awaiting Decision</option>
                        <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" id="priority" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" {{ request('priority') === 'all' ? 'selected' : '' }}>All Priorities</option>
                        <option value="Urgent" {{ request('priority') === 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Rush" {{ request('priority') === 'Rush' ? 'selected' : '' }}>Rush</option>
                        <option value="Routine" {{ request('priority') === 'Routine' ? 'selected' : '' }}>Routine</option>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Apply Filters
                </button>
                
                <a href="{{ route('pfmo.facility-requests') }}" class="text-gray-600 hover:text-gray-800 underline">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-4 border">
            <div class="text-sm text-gray-600">Pending Approval</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 border">
            <div class="text-sm text-gray-600">Approved</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 border">
            <div class="text-sm text-gray-600">Rush Requests</div>
            <div class="text-2xl font-bold text-orange-600">{{ $stats['rush'] ?? 0 }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 border">
            <div class="text-sm text-gray-600">Total</div>
            <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Requests List -->
    @if($requests && $requests->count() > 0)
        <div class="space-y-4">
            @foreach($requests as $request)
                <div class="request-card bg-white rounded-lg shadow-sm p-6 border
                    @if($request->status === 'In Progress') in-progress
                    @elseif($request->status === 'Under Sub-Department Evaluation') under-evaluation
                    @elseif($request->status === 'Awaiting PFMO Decision') awaiting-decision
                    @elseif($request->status === 'Approved') approved
                    @elseif($request->status === 'Rejected') rejected
                    @endif">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $request->title }}</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Requested by:</span>
                                    <div class="font-medium">
                                        {{ $request->requester->employeeInfo->FirstName ?? 'Unknown' }}
                                        {{ $request->requester->employeeInfo->LastName ?? '' }}
                                    </div>
                                    <div class="text-gray-500">
                                        {{ $request->requester->department->dept_name ?? 'Unknown Department' }}
                                    </div>
                                </div>
                                
                                <div>
                                    <span class="text-gray-600">Date Submitted:</span>
                                    <div class="font-medium">{{ $request->date_submitted->format('M j, Y') }}</div>
                                    <div class="text-gray-500">{{ $request->date_submitted->diffForHumans() }}</div>
                                </div>
                                
                                @if($request->iomDetails && $request->iomDetails->date_needed)
                                <div>
                                    <span class="text-gray-600">Date Needed:</span>
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($request->iomDetails->date_needed)->format('M j, Y') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="ml-6 flex flex-col items-end space-y-2">
                            <!-- Status Badge -->
                            <span class="px-3 py-1 text-sm rounded-full font-medium
                                @if($request->status === 'In Progress') bg-blue-100 text-blue-800
                                @elseif($request->status === 'Under Sub-Department Evaluation') bg-yellow-100 text-yellow-800
                                @elseif($request->status === 'Awaiting PFMO Decision') bg-orange-100 text-orange-800
                                @elseif($request->status === 'Approved') bg-green-100 text-green-800
                                @elseif($request->status === 'Rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $request->status }}
                            </span>
                            
                            <!-- Priority Badge -->
                            @if($request->iomDetails && $request->iomDetails->priority)
                                <span class="px-2 py-1 text-xs rounded font-semibold
                                    @if($request->iomDetails->priority === 'Urgent') bg-red-500 text-white
                                    @elseif($request->iomDetails->priority === 'Rush') bg-yellow-500 text-black
                                    @else bg-green-500 text-white
                                    @endif">
                                    {{ $request->iomDetails->priority }}
                                </span>
                            @endif
                            
                            <!-- Sub-Department Assignment -->
                            @if($request->assigned_sub_department)
                                <span class="sub-department-badge">
                                    {{ ucwords(str_replace('_', ' ', $request->assigned_sub_department)) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Request Description -->
                    @if($request->iomDetails && $request->iomDetails->body)
                        <div class="mb-4">
                            <div class="text-gray-700 text-sm">
                                {{ Str::limit($request->iomDetails->body, 200) }}
                                @if(strlen($request->iomDetails->body) > 200)
                                    <button class="text-blue-600 hover:text-blue-800 ml-1" onclick="toggleDescription({{ $request->form_id }})">
                                        Read more...
                                    </button>
                                @endif
                            </div>
                            
                            @if(strlen($request->iomDetails->body) > 200)
                                <div id="full-description-{{ $request->form_id }}" class="hidden text-gray-700 text-sm mt-2">
                                    {{ $request->iomDetails->body }}
                                    <button class="text-blue-600 hover:text-blue-800 ml-1" onclick="toggleDescription({{ $request->form_id }})">
                                        Show less
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Auto-Assignment Details -->
                    @if($request->auto_assignment_details)
                        @php
                            $autoDetails = json_decode($request->auto_assignment_details, true);
                        @endphp
                        @if($autoDetails && isset($autoDetails['suggested_department']))
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                <div class="text-sm">
                                    <span class="font-medium text-blue-800">Auto-Assignment:</span>
                                    <span class="text-blue-700">
                                        Suggested {{ $autoDetails['suggested_department'] }} 
                                        ({{ $autoDetails['confidence_score'] }}% confidence)
                                        @if($autoDetails['was_auto_assigned'])
                                            ✓ Applied
                                        @else
                                            - User overridden
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endif
                    
                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-4 border-t">
                        <div class="text-sm text-gray-500">
                            Request #{{ $request->form_id }}
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="{{ route('pfmo.request.show', $request->form_id) }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                View Details
                            </a>
                            
                            @if(in_array($request->status, ['In Progress', 'Under Sub-Department Evaluation', 'Awaiting PFMO Decision']))
                                @can('approve-pfmo-requests')
                                    <a href="{{ route('pfmo.request.show', $request->form_id) }}?action=approve" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Process
                                    </a>
                                @endcan
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-8">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="text-gray-400 text-6xl mb-4">📋</div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No Facility Requests Found</h3>
            <p class="text-gray-600">There are no facility requests matching your current filters.</p>
            
            @if(request()->hasAny(['status', 'priority', 'date_from', 'date_to']))
                <a href="{{ route('pfmo.facility-requests') }}" 
                   class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Clear Filters
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function toggleDescription(requestId) {
        const fullDesc = document.getElementById(`full-description-${requestId}`);
        const isHidden = fullDesc.classList.contains('hidden');
        
        if (isHidden) {
            fullDesc.classList.remove('hidden');
            // Hide the truncated version (you might need to adjust this)
        } else {
            fullDesc.classList.add('hidden');
            // Show the truncated version
        }
    }
    
    // Auto-refresh for real-time updates
    let autoRefresh = setInterval(function() {
        if (document.visibilityState === 'visible') {
            // Only refresh if no modal is open and user isn't actively interacting
            if (!document.querySelector('.modal-open') && document.body.scrollTop < 100) {
                window.location.reload();
            }
        }
    }, 120000); // 2 minutes
    
    // Pause auto-refresh when user is actively scrolling
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        clearInterval(autoRefresh);
        
        scrollTimeout = setTimeout(function() {
            autoRefresh = setInterval(function() {
                if (document.visibilityState === 'visible') {
                    window.location.reload();
                }
            }, 120000);
        }, 5000); // Resume after 5 seconds of no scrolling
    });
</script>
@endpush
