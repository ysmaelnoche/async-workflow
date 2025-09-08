@extends('layouts.app')

@section('title', 'PFMO Dashboard - Enhanced Workflow')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --card-bg: rgba(255, 255, 255, 0.95);
        --text-primary: #1a202c;
        --text-secondary: #64748b;
        --border-color: rgba(255, 255, 255, 0.2);
        --shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
        --header-bg: rgba(255, 255, 255, 0.95);
    }

    [data-bs-theme="dark"] {
        --card-bg: rgba(30, 41, 59, 0.95);
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --border-color: rgba(51, 65, 85, 0.3);
        --shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.4);
        --header-bg: rgba(30, 41, 59, 0.95);
    }

    body {
        background: var(--primary-gradient);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    .dashboard-header {
        background: var(--header-bg);
        backdrop-filter: blur(20px);
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
    }
    
    .kpi-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        box-shadow: var(--shadow);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }
    
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }
    
    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }
    
    .kpi-number {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0;
        color: var(--text-primary);
    }
    
    .kpi-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
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
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        box-shadow: var(--shadow);
        backdrop-filter: blur(10px);
    }
    
    .chart-card .card-header {
        background: var(--card-bg);
        border-bottom: 2px solid var(--border-color);
        border-radius: 15px 15px 0 0 !important;
        font-weight: 700;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }
    
    .action-btn {
        background: var(--primary-gradient);
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
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }
    
    .action-btn.btn-info {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }
    
    .action-btn.btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
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
        background: linear-gradient(135deg, #dbeafe, #93c5fd);
        color: #1e40af;
        border-color: #93c5fd;
    }
    
    .status-evaluation {
        background: linear-gradient(135deg, #fde68a, #fcd34d);
        color: #b45309;
        border-color: #fcd34d;
    }
    
    .status-decision {
        background: linear-gradient(135deg, #e9d5ff, #c4b5fd);
        color: #7c3aed;
        border-color: #c4b5fd;
    }
    
    .status-approved {
        background: linear-gradient(135deg, #d1fae5, #86efac);
        color: #065f46;
        border-color: #86efac;
    }
    
    .status-rejected {
        background: linear-gradient(135deg, #fee2e2, #fca5a5);
        color: #dc2626;
        border-color: #fca5a5;
    }

    /* Dark mode status badges */
    [data-bs-theme="dark"] .status-pending {
        background: rgba(254, 243, 199, 0.2);
        color: #fcd34d;
        border-color: #fcd34d;
    }
    
    [data-bs-theme="dark"] .status-in-progress {
        background: rgba(219, 234, 254, 0.2);
        color: #93c5fd;
        border-color: #93c5fd;
    }
    
    [data-bs-theme="dark"] .status-evaluation {
        background: rgba(253, 230, 138, 0.2);
        color: #fcd34d;
        border-color: #fcd34d;
    }
    
    [data-bs-theme="dark"] .status-decision {
        background: rgba(233, 213, 255, 0.2);
        color: #c4b5fd;
        border-color: #c4b5fd;
    }
    
    [data-bs-theme="dark"] .status-approved {
        background: rgba(209, 250, 229, 0.2);
        color: #86efac;
        border-color: #86efac;
    }
    
    [data-bs-theme="dark"] .status-rejected {
        background: rgba(254, 226, 226, 0.2);
        color: #fca5a5;
        border-color: #fca5a5;
    }
    
    .table {
        background: var(--card-bg);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: var(--shadow);
        margin: 0;
    }
    
    .table th {
        background: var(--card-bg);
        color: var(--text-primary);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
        padding: 1rem;
    }
    
    .table td {
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
        padding: 1rem;
        vertical-align: middle;
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: rgba(255, 255, 255, 0.02);
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    [data-bs-theme="dark"] .table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .supervisor-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .supervisor-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }
    
    .supervisor-name {
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    
    .supervisor-dept {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin: 0;
    }
    
    .btn-assign, .btn-remove {
        border: none;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn-assign {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }
    
    .btn-assign:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        color: white;
    }
    
    .btn-remove {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }
    
    .btn-remove:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        color: white;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-header {
            margin-bottom: 1rem;
        }
        
        .kpi-number {
            font-size: 2rem;
        }
        
        .kpi-icon {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .status-badge {
            font-size: 0.625rem;
            padding: 0.25rem 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .kpi-number {
            font-size: 1.75rem;
        }
        
        .kpi-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
    }
    
    /* Animations */
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
    
    .kpi-card, .chart-card, .table {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .kpi-card:nth-child(1) { animation-delay: 0.1s; }
    .kpi-card:nth-child(2) { animation-delay: 0.2s; }
    .kpi-card:nth-child(3) { animation-delay: 0.3s; }
    .kpi-card:nth-child(4) { animation-delay: 0.4s; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<div class="container-fluid py-4">
    <!-- Dashboard Header -->
    <div class="dashboard-header p-4 mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold text-dark mb-2">
                    <i class="fas fa-tachometer-alt text-primary me-3"></i>
                    PFMO Dashboard
                </h1>
                <p class="text-muted mb-1">Physical Facilities Management Office</p>
                <small class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>
                    {{ now()->format('l, F j, Y') }} • 
                    <i class="fas fa-clock me-1"></i>
                    Last updated: {{ now()->format('g:i A') }}
                </small>
            </div>
            <div class="col-md-4 text-md-end">
                <p class="text-muted mb-1">Welcome back,</p>
                <h4 class="fw-bold text-dark">{{ Auth::user()->name ?? Auth::user()->username }}</h4>
                <small class="text-muted">
                    {{ Auth::user()->position ?? 'PFMO Head' }} • 
                    {{ Auth::user()->department->dept_name ?? 'PFMO' }}
                </small>
            </div>
        </div>
        
        <!-- Quick Navigation -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="btn-group" role="group" aria-label="Quick Navigation">
                    <a href="{{ route('pfmo.manage-employees') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users-cog me-2"></i>Manage Employees
                    </a>
                    <a href="{{ route('pfmo.facility-requests') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-clipboard-list me-2"></i>View Requests
                    </a>
                    <a href="{{ route('pfmo.metrics') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>Metrics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card h-100" style="--card-accent: linear-gradient(90deg, #3b82f6, #1d4ed8);">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="kpi-number">{{ $dashboard['stats']['total_requests'] ?? 15 }}</h2>
                        <p class="kpi-label mb-0">Total Requests</p>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> +12% from last month
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card h-100" style="--card-accent: linear-gradient(90deg, #f59e0b, #d97706);">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="kpi-number">{{ $dashboard['stats']['pending_requests'] ?? 10 }}</h2>
                        <p class="kpi-label mb-0">Pending Approval</p>
                        <small class="text-warning">
                            <i class="fas fa-minus"></i> -5% from last week
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card h-100" style="--card-accent: linear-gradient(90deg, #8b5cf6, #7c3aed);">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="kpi-number">{{ $dashboard['stats']['under_evaluation'] ?? 1 }}</h2>
                        <p class="kpi-label mb-0">Under Evaluation</p>
                        <small class="text-muted">
                            <i class="fas fa-equals"></i> No change
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card h-100" style="--card-accent: linear-gradient(90deg, #10b981, #059669);">
                <div class="card-body d-flex align-items-center">
                    <div class="kpi-icon me-3" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="kpi-number">{{ $dashboard['stats']['approved_today'] ?? 0 }}</h2>
                        <p class="kpi-label mb-0">Approved Today</p>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> +8% from yesterday
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-grid mb-4">
        <h3 class="text-white mb-4 text-center">
            <i class="fas fa-bolt me-2"></i>
            Quick Actions
        </h3>
        <div class="row g-3">
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('pfmo.manage-employees') }}" class="action-btn btn-primary w-100">
                    <i class="fas fa-users-cog"></i>
                    Manage Employees
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('pfmo.approvals') }}" class="action-btn w-100">
                    <i class="fas fa-tasks"></i>
                    Review Queue
                </a>
            </div>
            @if(Auth::user()->position === 'Head')
            <div class="col-lg-3 col-md-6">
                <button class="action-btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#manageSupervisorsModal">
                    <i class="fas fa-users-cog"></i>
                    Manage Supervisors
                </button>
            </div>
            @endif
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('pfmo.facility-requests') }}" class="action-btn btn-success w-100">
                    <i class="fas fa-list"></i>
                    View All Requests
                </a>
            </div>
            <div class="col-lg-3 col-md-6">
                <a href="#reports" class="action-btn btn-warning w-100">
                    <i class="fas fa-chart-bar"></i>
                    Generate Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card chart-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Request Status Distribution
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-period="7">7 Days</button>
                        <button class="btn btn-outline-primary" data-period="30">30 Days</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card chart-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Request Trends
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" data-trend="daily">Daily</button>
                        <button class="btn btn-outline-secondary" data-trend="weekly">Weekly</button>
                        <button class="btn btn-outline-secondary" data-trend="monthly">Monthly</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Supervisor Management (PFMO Head Only) -->
    @if(Auth::user()->position === 'Head')
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users-cog me-2"></i>
                        Supervisor Assignments
                    </h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignSupervisorModal">
                        <i class="fas fa-plus me-1"></i>
                        Assign Supervisor
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($subDepartments ?? [] as $subDept)
                        <div class="col-md-6 col-lg-4">
                            <div class="supervisor-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-primary mb-0">{{ $subDept->name }}</h6>
                                    @if($subDept->supervisor)
                                        <span class="badge bg-success">Assigned</span>
                                    @else
                                        <span class="badge bg-warning">Vacant</span>
                                    @endif
                                </div>
                                @if($subDept->supervisor)
                                    <p class="mb-1">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $subDept->supervisor->employeeInfo->FirstName ?? 'N/A' }} {{ $subDept->supervisor->employeeInfo->LastName ?? '' }}
                                    </p>
                                    <small class="text-muted">{{ $subDept->supervisor->position ?? 'Supervisor' }}</small>
                                @else
                                    <p class="text-muted mb-0">No supervisor assigned</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card table-modern">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Recent Requests
                    </h5>
                    <a href="{{ route('pfmo.facility-requests') }}" class="btn btn-outline-primary btn-sm">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Requester</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboard['recent_requests'] ?? [] as $request)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $request->title ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ Str::limit($request->description ?? 'No description', 50) }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $request->requester->employeeInfo->FirstName ?? 'Unknown' }} {{ $request->requester->employeeInfo->LastName ?? '' }}</div>
                                        <small class="text-muted">{{ $request->requester->department->dept_name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $request->form_type ?? 'General' }}</span>
                                    </td>
                                    <td>
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
                                    <td>
                                        <small>{{ $request->date_submitted ? $request->date_submitted->format('M j, Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('approvals.show', $request->form_id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No recent requests found</p>
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
    </div>
</div>

<!-- Manage Supervisors Modal -->
@if(Auth::user()->position === 'Head')
<div class="modal fade" id="manageSupervisorsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog me-2"></i>
                    Manage Supervisors
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Current supervisor assignments for all sub-departments:</p>
                <div class="row g-3">
                    @foreach($subDepartments ?? [] as $subDept)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">{{ $subDept->name }}</h6>
                                @if($subDept->supervisor)
                                    <p class="card-text">
                                        <strong>Supervisor:</strong> {{ $subDept->supervisor->employeeInfo->FirstName ?? 'N/A' }} {{ $subDept->supervisor->employeeInfo->LastName ?? '' }}
                                    </p>
                                    <button class="btn btn-outline-danger btn-sm" onclick="removeSupervisor({{ $subDept->id }})">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                @else
                                    <p class="card-text text-muted">No supervisor assigned</p>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignSupervisorModal" data-subdept="{{ $subDept->id }}">
                                        <i class="fas fa-plus"></i> Assign
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Under Evaluation', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $dashboard['stats']['pending_requests'] ?? 5 }},
                    {{ $dashboard['stats']['in_progress_requests'] ?? 3 }},
                    {{ $dashboard['stats']['under_evaluation'] ?? 2 }},
                    {{ $dashboard['stats']['approved_requests'] ?? 8 }},
                    {{ $dashboard['stats']['rejected_requests'] ?? 1 }}
                ],
                backgroundColor: [
                    '#f59e0b',
                    '#3b82f6',
                    '#8b5cf6',
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

    // Initialize Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Requests',
                data: [12, 19, 3, 5, 2, 3, 9],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});

function removeSupervisor(subDeptId) {
    if (confirm('Are you sure you want to remove this supervisor?')) {
        // AJAX call to remove supervisor
        fetch(`/pfmo/supervisors/remove/${subDeptId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}
</script>
@endsection
