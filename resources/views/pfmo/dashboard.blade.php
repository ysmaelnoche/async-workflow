@extends('layouts.app')

@section('title', 'PFMO Dashboard - Enhanced Workflow')

@push('styles')
<style>
    .pfmo-dashboard {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .stats-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        padding: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: transform 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .workflow-stage {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0 8px 8px 0;
    }
    
    .workflow-stage.evaluation {
        border-left-color: #ffc107;
    }
    
    .workflow-stage.decision {
        border-left-color: #28a745;
    }
    
    .workflow-stage.completed {
        border-left-color: #6f42c1;
    }
    
    .sub-dept-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #17a2b8;
    }
    
    .priority-urgent {
        background-color: #dc3545;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .priority-rush {
        background-color: #ffc107;
        color: #212529;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .priority-routine {
        background-color: #28a745;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<!-- BASIC DASHBOARD VIEW MARKER -->
<div class="container mx-auto px-4 py-8">
    <!-- Enhanced PFMO Dashboard Header -->
    <div class="pfmo-dashboard">
        <div class="flex justify-between items-center">
            <div>
                                <h1 class="display-5 fw-bold text-dark mb-2">
                    <i class="fas fa-tachometer-alt text-primary me-3"></i>
                    PFMO Professional Dashboard ✅
                </h1>
                <p class="text-lg opacity-90">Physical Facilities Management Office - Streamlined Process Management</p>
            </div>
            <div class="text-right">
                <div class="text-sm opacity-75">{{ now()->format('F j, Y') }}</div>
                <div class="text-xs opacity-50">{{ now()->format('g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stats-card text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">{{ $dashboard['stats']['total_requests'] ?? 0 }}</div>
            <div class="text-gray-600">Total Requests</div>
        </div>
        
        <div class="stats-card text-center">
            <div class="text-2xl font-bold text-yellow-600 mb-2">{{ $dashboard['stats']['pending_requests'] ?? 0 }}</div>
            <div class="text-gray-600">Pending Approval</div>
        </div>
        
        <div class="stats-card text-center">
            <div class="text-2xl font-bold text-orange-600 mb-2">{{ $dashboard['stats']['under_evaluation'] ?? 0 }}</div>
            <div class="text-gray-600">Under Evaluation</div>
        </div>
        
        <div class="stats-card text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">{{ $dashboard['stats']['approved_today'] ?? 0 }}</div>
            <div class="text-gray-600">Approved Today</div>
        </div>
    </div>

    <!-- Star Feedback and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Average Star Feedback -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Service Feedback & Ratings</h2>
            
            <!-- Average Rating Display -->
            <div class="text-center mb-6">
                <div class="text-4xl font-bold text-yellow-600 mb-2">{{ number_format($averageRating, 1) }}</div>
                <div class="flex justify-center mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= floor($averageRating))
                            <i class="fas fa-star text-yellow-400 text-xl"></i>
                        @elseif ($i <= ceil($averageRating))
                            <i class="fas fa-star-half-alt text-yellow-400 text-xl"></i>
                        @else
                            <i class="far fa-star text-gray-300 text-xl"></i>
                        @endif
                    @endfor
                </div>
                <p class="text-sm text-gray-600">Average Rating from {{ $feedbackData['statistics']['total_feedback'] ?? 0 }} reviews</p>
            </div>
            
            <!-- Rating Distribution -->
            <div class="space-y-2">
                @foreach($ratingDistribution as $rating)
                    <div class="flex items-center">
                        <span class="text-sm w-8">{{ $rating['stars'] }}★</span>
                        <div class="flex-1 bg-gray-200 rounded-full h-2 mx-3">
                            <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $rating['percentage'] }}%"></div>
                        </div>
                        <span class="text-sm text-gray-600 w-12">{{ $rating['count'] }}</span>
                    </div>
                @endforeach
            </div>
            
            <!-- Feedback Stats -->
            <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $feedbackData['statistics']['feedback_today'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Today</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $feedbackData['statistics']['completion_rate'] ?? 0 }}%</div>
                    <div class="text-sm text-gray-600">Response Rate</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Recent Activity</h2>
            
            @if($dashboard['recent_requests'] && count($dashboard['recent_requests']) > 0)
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($dashboard['recent_requests'] as $request)
                        <div class="border-l-4 border-blue-500 pl-4 py-2 bg-gray-50 rounded-r">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $request->title }}</h4>
                                    <p class="text-sm text-gray-600">
                                        From: {{ $request->requester->employeeInfo->FirstName ?? 'Unknown' }} 
                                              {{ $request->requester->employeeInfo->LastName ?? '' }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $request->date_submitted->diffForHumans() }}</p>
                                </div>
                                <div class="ml-4 flex flex-col items-end space-y-1">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($request->status === 'In Progress') bg-blue-100 text-blue-800
                                        @elseif($request->status === 'Under Sub-Department Evaluation') bg-yellow-100 text-yellow-800
                                        @elseif($request->status === 'Awaiting PFMO Decision') bg-orange-100 text-orange-800
                                        @elseif($request->status === 'Approved') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $request->status }}
                                    </span>
                                    @if($request->iomDetails && $request->iomDetails->priority)
                                        <span class="priority-{{ strtolower($request->iomDetails->priority) }}">
                                            {{ $request->iomDetails->priority }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No recent requests found.</p>
            @endif
        </div>
    </div>

    <!-- Sub-Department Status -->
    @if($dashboard['category_breakdown'] && count($dashboard['category_breakdown']) > 0)
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Request Categories & Sub-Departments</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($dashboard['category_breakdown'] as $category => $count)
                <div class="sub-dept-card">
                    <h3 class="font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $category) }}</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $count }}</p>
                    <p class="text-sm text-gray-600">Active Requests</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Performance Metrics -->
    @if($dashboard['performance_metrics'])
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Performance Metrics</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2">
                    {{ $dashboard['performance_metrics']['average_processing_time_hours'] ?? 0 }}h
                </div>
                <div class="text-gray-600">Avg Processing Time</div>
            </div>
            
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">
                    {{ $dashboard['performance_metrics']['total_processed'] ?? 0 }}
                </div>
                <div class="text-gray-600">Total Processed</div>
            </div>
            
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2">
                    {{ round($dashboard['performance_metrics']['efficiency_rating'] ?? 0) }}%
                </div>
                <div class="text-gray-600">Efficiency Rating</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Feedback Overview Section -->
    @if(isset($feedbackData))
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
            <span class="mr-3">💬</span>
            Requestor Feedback & Ratings
        </h2>
        
        <!-- Feedback Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="stats-card text-center bg-gradient-to-br from-yellow-400 to-yellow-600 text-white">
                <div class="text-2xl font-bold mb-2">
                    @if($feedbackData['statistics']['average_rating'] > 0)
                        ⭐ {{ $feedbackData['statistics']['average_rating'] }}/5
                    @else
                        ⭐ N/A
                    @endif
                </div>
                <div class="text-yellow-100">Average Rating</div>
            </div>
            
            <div class="stats-card text-center bg-gradient-to-br from-blue-400 to-blue-600 text-white">
                <div class="text-2xl font-bold mb-2">{{ $feedbackData['statistics']['feedback_today'] ?? 0 }}</div>
                <div class="text-blue-100">New Today</div>
            </div>
            
            <div class="stats-card text-center bg-gradient-to-br from-red-400 to-red-600 text-white">
                <div class="text-2xl font-bold mb-2">{{ $feedbackData['statistics']['needs_action'] ?? 0 }}</div>
                <div class="text-red-100">Need Action</div>
            </div>
            
            <div class="stats-card text-center bg-gradient-to-br from-green-400 to-green-600 text-white">
                <div class="text-2xl font-bold mb-2">{{ $feedbackData['statistics']['completion_rate'] ?? 0 }}%</div>
                <div class="text-green-100">Feedback Rate</div>
            </div>
            
            <div class="stats-card text-center bg-gradient-to-br from-purple-400 to-purple-600 text-white">
                <div class="text-2xl font-bold mb-2">{{ $feedbackData['statistics']['total_feedback'] ?? 0 }}</div>
                <div class="text-purple-100">Total Feedback</div>
            </div>
        </div>

        <!-- Main Feedback Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Feedback List -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-800 flex items-center justify-between">
                    <span>Recent Feedback</span>
                    <span class="text-sm font-normal text-gray-500">Last {{ count($feedbackData['recent_feedback']) }} submissions</span>
                </h3>
                
                @if(count($feedbackData['recent_feedback']) > 0)
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($feedbackData['recent_feedback'] as $feedback)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <h4 class="font-semibold text-gray-900 mr-3">{{ $feedback['job_order_number'] }}</h4>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="text-lg {{ $i <= $feedback['rating'] ? 'text-yellow-400' : 'text-gray-300' }}">⭐</span>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-600">({{ $feedback['rating'] }}/5)</span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-2">"{{ $feedback['comments'] }}"</p>
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>From: {{ $feedback['requester_name'] }}</span>
                                            <span>{{ \Carbon\Carbon::parse($feedback['feedback_date'])->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    @if($feedback['needs_action'])
                                        <span class="ml-3 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                            Action Required
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-600 bg-gray-100 rounded p-2">
                                    <strong>Work:</strong> {{ Str::limit($feedback['request_description'], 100) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <span class="text-4xl mb-4 block">📝</span>
                        <p>No feedback submissions yet</p>
                    </div>
                @endif
            </div>

            <!-- Rating Distribution Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-gray-800">Rating Distribution</h3>
                
                <div class="space-y-3">
                    @foreach($feedbackData['rating_distribution'] as $rating)
                        <div class="flex items-center">
                            <div class="flex items-center w-12">
                                <span class="text-sm font-medium">{{ $rating['stars'] }}</span>
                                <span class="text-yellow-400 ml-1">⭐</span>
                            </div>
                            <div class="flex-1 mx-3">
                                <div class="bg-gray-200 rounded-full h-4">
                                    <div class="h-4 rounded-full transition-all duration-500 
                                        @if($rating['stars'] >= 4) bg-green-500
                                        @elseif($rating['stars'] == 3) bg-yellow-500
                                        @else bg-red-500
                                        @endif" 
                                        style="width: {{ $rating['percentage'] }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="w-16 text-right">
                                <span class="text-sm text-gray-600">{{ $rating['count'] }} ({{ $rating['percentage'] }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @php
                    $totalRatings = array_sum(array_column($feedbackData['rating_distribution'], 'count'));
                @endphp
                <div class="mt-4 pt-4 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">Total Ratings: {{ $totalRatings }}</p>
                </div>
            </div>
        </div>

        <!-- Jobs Requiring Action Alert -->
        @if(count($feedbackData['jobs_needing_action']) > 0)
            <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-red-800 flex items-center">
                    <span class="mr-2">⚠️</span>
                    Jobs Requiring Further Action
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($feedbackData['jobs_needing_action'] as $job)
                        <div class="bg-white border border-red-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900">{{ $job['job_order_number'] }}</h4>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-sm {{ $i <= $job['rating'] ? 'text-yellow-400' : 'text-gray-300' }}">⭐</span>
                                    @endfor
                                    <span class="ml-1 text-sm text-gray-600">({{ $job['rating'] }}/5)</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mb-2">"{{ Str::limit($job['comments'], 100) }}"</p>
                            <div class="text-xs text-gray-500">
                                <p>From: {{ $job['requester_name'] }}</p>
                                <p>{{ \Carbon\Carbon::parse($job['feedback_date'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Low-Rated Jobs Alert -->
        @if(count($feedbackData['low_rated_jobs']) > 0)
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4 text-yellow-800 flex items-center">
                    <span class="mr-2">⭐</span>
                    Low-Rated Jobs (3 stars or below)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($feedbackData['low_rated_jobs'] as $job)
                        <div class="bg-white border border-yellow-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900">{{ $job['job_order_number'] }}</h4>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-sm {{ $i <= $job['rating'] ? 'text-yellow-400' : 'text-gray-300' }}">⭐</span>
                                    @endfor
                                    <span class="ml-1 text-sm text-gray-600">({{ $job['rating'] }}/5)</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 mb-2">"{{ Str::limit($job['comments'], 100) }}"</p>
                            <div class="text-xs text-gray-500">
                                <p>From: {{ $job['requester_name'] }}</p>
                                <p>{{ \Carbon\Carbon::parse($job['feedback_date'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @endif

    <!-- Recommendations -->
    @if($recommendations && count($recommendations) > 0)
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">System Recommendations</h2>
        
        <div class="space-y-3">
            @foreach($recommendations as $recommendation)
                <div class="border-l-4 pl-4 py-3 rounded-r
                    @if($recommendation['priority'] === 'high') border-red-500 bg-red-50
                    @elseif($recommendation['priority'] === 'medium') border-yellow-500 bg-yellow-50
                    @else border-blue-500 bg-blue-50
                    @endif">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-semibold 
                                @if($recommendation['priority'] === 'high') text-red-800
                                @elseif($recommendation['priority'] === 'medium') text-yellow-800
                                @else text-blue-800
                                @endif">
                                {{ $recommendation['title'] }}
                            </h4>
                            <p class="text-gray-700 mt-1">{{ $recommendation['message'] }}</p>
                            @if($recommendation['action'])
                                <p class="text-sm text-gray-600 mt-2">
                                    <strong>Suggested Action:</strong> {{ $recommendation['action'] }}
                                </p>
                            @endif
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full font-semibold
                            @if($recommendation['priority'] === 'high') bg-red-200 text-red-800
                            @elseif($recommendation['priority'] === 'medium') bg-yellow-200 text-yellow-800
                            @else bg-blue-200 text-blue-800
                            @endif">
                            {{ ucfirst($recommendation['priority']) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
        <a href="{{ route('pfmo.facility-requests') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
            View All Requests
        </a>
        
        <a href="{{ route('pfmo.manage-employees') }}" 
           class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
            <i class="fas fa-users me-2"></i>Manage Employees
        </a>
        
        <a href="{{ route('approvals.index') }}" 
           class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
            Approval Queue
        </a>

        @if(Auth::user()->position === 'Head')
        <a href="{{ route('pfmo.supervisors') }}" 
           class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition-colors">
            Manage Supervisors
        </a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        // In a real implementation, you might want to use AJAX to refresh specific sections
        // For now, we'll just reload the page
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 300000); // 5 minutes

    // Add some interactive behavior
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to stats cards
        const statsCards = document.querySelectorAll('.stats-card');
        statsCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.boxShadow = '';
            });
        });
    });
</script>
@endpush
