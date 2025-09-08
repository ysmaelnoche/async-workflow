@extends('layouts.app')

@section('title', 'PFMO Dashboard - Enhanced Workflow')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    .dashboard-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .kpi-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-accent, linear-gradient(90deg, #667eea, #764ba2));
    }
    
    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .kpi-number {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0;
        background: linear-gradient(135deg, #1a202c, #2d3748);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .kpi-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }
    
    .kpi-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .chart-card {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .chart-card .card-header {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        border-bottom: 2px solid #e5e7eb;
        border-radius: 15px 15px 0 0 !important;
        font-weight: 700;
        color: #1a202c;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }
    
    .action-btn {
        background: linear-gradient(135deg, var(--btn-gradient, #667eea 0%, #764ba2 100%));
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        color: white;
        text-decoration: none;
    }
    
    .action-btn.btn-success {
        --btn-gradient: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }
    
    .action-btn.btn-info {
        --btn-gradient: linear-gradient(135deg, #3b82f6, #1d4ed8);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }
    
    .action-btn.btn-warning {
        --btn-gradient: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }
    
    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 2px solid transparent;
    }
    
    .status-pending {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border-color: #fde68a;
    }
    
    .status-in-progress {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e40af;
        border-color: #bfdbfe;
    }
    
    .status-evaluation {
        background: linear-gradient(135deg, #e9d5ff, #d8b4fe);
        color: #7c3aed;
        border-color: #d8b4fe;
    }
    
    .status-decision {
        background: linear-gradient(135deg, #fed7aa, #fdba74);
        color: #ea580c;
        border-color: #fdba74;
    }
    
    .status-approved {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #065f46;
        border-color: #a7f3d0;
    }
    
    .status-rejected {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #dc2626;
        border-color: #fecaca;
    }
    
    .table-modern {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .table-modern thead th {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        border: none;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
        padding: 1rem;
    }
    
    .table-modern tbody tr {
        border: none;
        transition: all 0.3s ease;
    }
    
    .table-modern tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        transform: scale(1.01);
    }
    
    .table-modern tbody td {
        border: none;
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .supervisor-card {
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .supervisor-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }
    
    .quick-actions-grid {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    @media (max-width: 768px) {
        .kpi-number {
            font-size: 2rem;
        }
        
        .kpi-icon {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
        
        .chart-card {
            margin-bottom: 1.5rem;
        }
        
        .action-btn {
            width: 100%;
            margin-bottom: 0.5rem;
            justify-content: center;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush

@section('content')
<div class="dashboard-container">
    {{-- Header --}}
    <div class="dashboard-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-2">PFMO Dashboard</h1>
                    <p class="text-blue-100 text-lg">Physical Facilities Management Office</p>
                    <p class="text-blue-200 text-sm">{{ now()->format('l, F j, Y') }} • Last updated: {{ now()->format('g:i A') }}</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-blue-100 text-sm">Welcome back,</p>
                    <p class="text-white text-xl font-semibold">{{ Auth::user()->name ?? Auth::user()->username }}</p>
                    <p class="text-blue-200 text-sm">{{ Auth::user()->position }} • {{ Auth::user()->department->dept_name ?? 'PFMO' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        {{-- Key Metrics Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8 mb-8">
            {{-- Total Requests --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-up">+12%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $dashboard['stats']['total_requests'] ?? 0 }}</h3>
                    <p class="metric-label">Total Requests</p>
                </div>
            </div>

            {{-- Pending Approval --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-down">-5%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $dashboard['stats']['pending_requests'] ?? 0 }}</h3>
                    <p class="metric-label">Pending Approval</p>
                </div>
            </div>

            {{-- Under Evaluation --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-stable">0%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $dashboard['stats']['under_evaluation'] ?? 0 }}</h3>
                    <p class="metric-label">Under Evaluation</p>
                </div>
            </div>

            {{-- Approved Today --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-up">+8%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $dashboard['stats']['approved_today'] ?? 0 }}</h3>
                    <p class="metric-label">Approved Today</p>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
            {{-- Status Distribution Chart --}}
            <div class="chart-container">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Requests by Status</h3>
                    <div class="flex items-center space-x-2">
                        <button class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100">Last 7 days</button>
                        <button class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100">Last 30 days</button>
                    </div>
                </div>
                <div class="relative h-64 sm:h-72">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            {{-- Trend Chart --}}
            <div class="chart-container">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Request Trends</h3>
                    <div class="flex items-center space-x-2">
                        <button class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100">Daily</button>
                        <button class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100">Weekly</button>
                        <button class="text-xs sm:text-sm font-medium text-blue-600 px-2 py-1 rounded bg-blue-100">Monthly</button>
                    </div>
                </div>
                <div class="relative h-64 sm:h-72">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <a href="{{ route('approvals.index') }}" class="quick-action-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Approval Queue
                </a>
                
                @if(Auth::user()->position === 'Head')
                <a href="{{ route('pfmo.supervisors') }}" class="quick-action-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Manage Supervisors
                </a>
                @endif

                <a href="{{ route('pfmo.facility-requests') }}" class="quick-action-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    View All Requests
                </a>

                <a href="{{ route('pfmo.metrics') }}" class="quick-action-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Performance Reports
                </a>
            </div>
        </div>

        {{-- Recent Requests --}}
        <div class="recent-requests-table mb-8">
            <div class="table-header">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Recent Requests</h3>
                    <a href="{{ route('pfmo.facility-requests') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All →</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden-mobile">Requester</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Type</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Submitted</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dashboard['recent_requests'] ?? [] as $request)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 sm:px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $request->title ?? 'N/A' }}</div>
                                <div class="text-xs sm:text-sm text-gray-500">{{ Str::limit($request->description ?? 'No description', 50) }}</div>
                                <div class="text-xs text-gray-500 sm:hidden mt-1">
                                    {{ $request->requester->employeeInfo->FirstName ?? 'Unknown' }} {{ $request->requester->employeeInfo->LastName ?? '' }}
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap hidden-mobile">
                                <div class="text-sm text-gray-900">{{ $request->requester->employeeInfo->FirstName ?? 'Unknown' }} {{ $request->requester->employeeInfo->LastName ?? '' }}</div>
                                <div class="text-sm text-gray-500">{{ $request->requester->department->dept_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                {{ $request->form_type ?? 'General' }}
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                @php
                                $statusClass = match($request->status) {
                                    'Pending' => 'status-pending',
                                    'In Progress' => 'status-in-progress',
                                    'Under Sub-Department Evaluation' => 'status-evaluation',
                                    'Awaiting PFMO Decision' => 'status-decision',
                                    'Approved' => 'status-approved',
                                    'Rejected' => 'status-rejected',
                                    default => 'status-pending'
                                };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $request->status }}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                {{ $request->date_submitted ? $request->date_submitted->format('M j, Y') : 'N/A' }}
                            </td>
                            <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('approvals.show', $request->form_id) }}" class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm px-2 py-1 rounded bg-blue-50 hover:bg-blue-100">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No requests found</h3>
                                    <p class="mt-1 text-sm text-gray-500">No recent requests to display.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Under Evaluation', 'Awaiting Decision', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $dashboard['stats']['pending_requests'] ?? 0 }},
                    {{ $dashboard['stats']['in_progress'] ?? 0 }},
                    {{ $dashboard['stats']['under_evaluation'] ?? 0 }},
                    {{ $dashboard['stats']['awaiting_decision'] ?? 0 }},
                    {{ $dashboard['stats']['approved_today'] ?? 0 }},
                    {{ $dashboard['stats']['rejected_today'] ?? 0 }}
                ],
                backgroundColor: [
                    '#f59e0b',
                    '#3b82f6',
                    '#8b5cf6',
                    '#ec4899',
                    '#10b981',
                    '#ef4444'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            datasets: [{
                label: 'Requests',
                data: [12, 19, 15, 25, 22, 30, 28, 35, 32],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Auto-refresh every 5 minutes
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 300000);
});
</script>
@endpush
