@extends('layouts.app')

@section('title', 'Secretary Dashboard - Enhanced Workflow')

@push('styles')
<style>
    * {
        box-sizing: border-box;
    }
    
    .dashboard-container {
        background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        min-height: 100vh;
        padding: 0;
        overflow-x: hidden;
    }
    
    .dashboard-header {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 1.5rem 0;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .metric-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        height: auto;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }
    
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #06b6d4, #3b82f6);
        border-radius: 20px 20px 0 0;
    }
    
    .metric-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 1);
    }
    
    .metric-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .metric-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1a202c;
        margin: 0;
        line-height: 1;
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: #4a5568;
        margin: 0.5rem 0 0 0;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .quick-action-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: block;
        border: 1px solid rgba(255, 255, 255, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .quick-action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #06b6d4, #3b82f6);
        border-radius: 20px 20px 0 0;
    }
    
    .quick-action-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 1);
        text-decoration: none;
    }
    
    .quick-action-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .chart-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }
    
    .chart-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1rem 0;
            margin-bottom: 1.5rem;
        }
        
        .dashboard-header .flex {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .metric-card {
            padding: 1.2rem;
            min-height: 120px;
        }
        
        .metric-value {
            font-size: 2rem;
        }
        
        .metric-icon {
            width: 48px;
            height: 48px;
        }
        
        .chart-container {
            padding: 1.5rem;
        }
        
        .quick-action-card {
            padding: 1.2rem;
        }
        
        .quick-action-icon {
            width: 56px;
            height: 56px;
        }
    }
    
    @media (max-width: 640px) {
        .dashboard-container {
            padding: 0;
        }
        
        .max-w-7xl {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .grid {
            gap: 1rem;
        }
        
        .metric-card {
            padding: 1rem;
            min-height: 100px;
        }
        
        .metric-value {
            font-size: 1.8rem;
        }
        
        .chart-container {
            padding: 1rem;
        }
        
        .quick-action-card {
            padding: 1rem;
        }
        
        .quick-action-icon {
            width: 48px;
            height: 48px;
        }
    }
    
    /* Animation for loading */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .metric-card,
    .chart-container,
    .quick-action-card {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .metric-card:nth-child(1) { animation-delay: 0.1s; }
    .metric-card:nth-child(2) { animation-delay: 0.2s; }
    .metric-card:nth-child(3) { animation-delay: 0.3s; }
    .metric-card:nth-child(4) { animation-delay: 0.4s; }
    
    /* Enhanced styling */
    .chart-container h3 {
        color: #1a202c;
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-in-progress { background: #dbeafe; color: #1e40af; }
    .status-evaluation { background: #fde68a; color: #b45309; }
    .status-decision { background: #f3e8ff; color: #7c3aed; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-rejected { background: #fee2e2; color: #dc2626; }
    
    .recent-requests-table {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .table-header {
        background: #f8fafc;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .trend-indicator {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-left: 0.5rem;
    }
    
    .trend-up { background: #dcfce7; color: #16a34a; }
    .trend-down { background: #fef2f2; color: #dc2626; }
    .trend-stable { background: #f1f5f9; color: #64748b; }
    
    .employee-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .employee-card {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e5e7eb;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="dashboard-container">
    {{-- Header --}}
    <div class="dashboard-header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-2">Secretary Dashboard</h1>
                    <p class="text-cyan-100 text-lg">{{ $departmentName }} Department</p>
                    <p class="text-cyan-200 text-sm">{{ now()->format('l, F j, Y') }} • Last updated: {{ now()->format('g:i A') }}</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-cyan-100 text-sm">Welcome back,</p>
                    <p class="text-white text-xl font-semibold">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-cyan-200 text-sm">Secretary • {{ $departmentName }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        {{-- Key Metrics Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8 mb-8">
            {{-- Total Proxy Requests --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-up">+15%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $stats['total_proxy_requests'] ?? 0 }}</h3>
                    <p class="metric-label">Total Requests</p>
                </div>
            </div>

            {{-- Pending Requests --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-down">-8%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $stats['pending_proxy_requests'] ?? 0 }}</h3>
                    <p class="metric-label">Pending Requests</p>
                </div>
            </div>

            {{-- Approved This Month --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-up">+22%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $stats['approved_this_month'] ?? 0 }}</h3>
                    <p class="metric-label">Approved This Month</p>
                </div>
            </div>

            {{-- Employees Helped --}}
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div class="metric-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="trend-indicator trend-stable">0%</span>
                </div>
                <div>
                    <h3 class="metric-value">{{ $stats['employees_helped'] ?? 0 }}</h3>
                    <p class="metric-label">Employees Helped</p>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-white mb-6 text-center">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                {{-- New Request --}}
                <a href="{{ route('secretary.select-employee') }}" class="quick-action-card group">
                    <div class="quick-action-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600">New Request</h4>
                    <p class="text-gray-600 text-sm">Create a PFMO request on behalf of employees</p>
                </a>

                {{-- Track Requests --}}
                <a href="#recent-requests" class="quick-action-card group">
                    <div class="quick-action-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-green-600">Track My Requests</h4>
                    <p class="text-gray-600 text-sm">Monitor status of submitted requests</p>
                </a>

                {{-- Department Overview --}}
                <a href="#department-employees" class="quick-action-card group">
                    <div class="quick-action-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-purple-600">Department Overview</h4>
                    <p class="text-gray-600 text-sm">View {{ count($departmentEmployees) }} department employees</p>
                </a>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-8">
            {{-- Status Distribution Chart --}}
            <div class="chart-container">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Request Status Distribution</h3>
                    <div class="flex items-center space-x-2">
                        <button class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100">Last 7 days</button>
                        <button class="text-xs sm:text-sm text-gray-500 hover:text-gray-700 px-2 py-1 rounded bg-gray-100">Last 30 days</button>
                    </div>
                </div>
                <div class="relative h-64 sm:h-72">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            {{-- Monthly Trend --}}
            <div class="chart-container">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Monthly Request Trends</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs sm:text-sm text-gray-500 px-2 py-1 rounded bg-gray-100">This Year</span>
                    </div>
                </div>
                <div class="relative h-64 sm:h-72">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Requests --}}
        <div id="recent-requests" class="recent-requests-table mb-8">
            <div class="table-header">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Proxy Requests</h3>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All →</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if(isset($proxyRequests) && $proxyRequests->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($proxyRequests as $request)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $request->title ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($request->description ?? 'No description', 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $request->employee_name ?? 'Unknown' }}</div>
                                <div class="text-sm text-gray-500">{{ $request->fromDepartment->dept_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->form_type ?? 'General' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->date_submitted ? $request->date_submitted->format('M j, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900">Track</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No requests yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new request.</p>
                    <div class="mt-6">
                        <a href="{{ route('secretary.select-employee') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create Request
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Department Employees Overview --}}
        <div id="department-employees" class="chart-container">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $departmentName }} Employees</h3>
                <span class="text-sm text-gray-500">{{ count($departmentEmployees) }} employees total</span>
            </div>
            <div class="employee-grid">
                @forelse($departmentEmployees as $employee)
                <div class="employee-card">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-medium text-sm">{{ substr($employee->FirstName, 0, 1) }}{{ substr($employee->LastName, 0, 1) }}</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $employee->FirstName }} {{ $employee->LastName }}</div>
                            <div class="text-xs text-gray-500">{{ $employee->Emp_No }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500">No employees found in this department.</p>
                </div>
                @endforelse
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
            labels: ['Pending', 'In Progress', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $stats['pending_proxy_requests'] ?? 0 }},
                    {{ $stats['in_progress_requests'] ?? 0 }},
                    {{ $stats['approved_this_month'] ?? 0 }},
                    {{ $stats['rejected_requests'] ?? 0 }}
                ],
                backgroundColor: [
                    '#f59e0b',
                    '#3b82f6',
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
                label: 'Requests Submitted',
                data: [8, 12, 15, 10, 18, 22, 16, 25, 20],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
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
