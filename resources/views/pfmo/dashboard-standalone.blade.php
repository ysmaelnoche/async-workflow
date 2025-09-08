<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PFMO Dashboard - Professional Interface</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --gradient-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: var(--card-shadow);
        }

        .dashboard-container {
            padding: 2rem 0;
        }

        .card-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            box-shadow: var(--card-shadow);
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bg-primary-gradient { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .bg-success-gradient { background: linear-gradient(135deg, var(--success-color), #059669); }
        .bg-warning-gradient { background: linear-gradient(135deg, var(--warning-color), #d97706); }
        .bg-danger-gradient { background: linear-gradient(135deg, var(--danger-color), #dc2626); }
        .bg-info-gradient { background: linear-gradient(135deg, var(--info-color), #0891b2); }

        .table-custom {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            overflow: hidden;
        }

        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem 0;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .chart-container {
                height: 250px;
            }
        }

        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
        }

        .action-btn {
            background: var(--gradient-bg);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 1rem;
            width: 100%;
            transition: transform 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('images/lyceum-logo.png') }}" alt="Logo" width="40" height="40" class="me-2">
                <span class="fw-bold">PFMO Dashboard</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pfmo.facility-requests') }}"><i class="fas fa-clipboard-list me-1"></i> Requests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pfmo.approvals') }}"><i class="fas fa-check-circle me-1"></i> Approvals</a>
                    </li>
                </ul>
                
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard Content -->
    <div class="container-fluid dashboard-container">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-body text-center py-4">
                        <h1 class="display-5 fw-bold mb-2">Welcome to PFMO Dashboard</h1>
                        <p class="lead text-muted">Physical Facilities Management Office - Workflow System</p>
                        <small class="text-muted">Last updated: {{ now()->format('M d, Y H:i A') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card card-custom stat-card">
                    <div class="stat-icon bg-primary-gradient">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="stat-number text-primary">{{ $dashboard['stats']['total_requests'] ?? 0 }}</div>
                    <div class="stat-label">Total Requests</div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card card-custom stat-card">
                    <div class="stat-icon bg-warning-gradient">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number text-warning">{{ $dashboard['stats']['pending_requests'] ?? 0 }}</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card card-custom stat-card">
                    <div class="stat-icon bg-success-gradient">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number text-success">{{ $dashboard['stats']['approved_requests'] ?? 0 }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card card-custom stat-card">
                    <div class="stat-icon bg-danger-gradient">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-number text-danger">{{ $dashboard['stats']['rejected_requests'] ?? 0 }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card card-custom stat-card">
                    <div class="stat-icon bg-info-gradient">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-number text-info">{{ $dashboard['stats']['approved_today'] ?? 0 }}</div>
                    <div class="stat-label">Today Approved</div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="card card-custom stat-card">
                    <div class="stat-icon bg-secondary">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-number text-secondary">{{ count($dashboard['sub_departments'] ?? []) }}</div>
                    <div class="stat-label">Departments</div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Row -->
        <div class="row g-4 mb-4">
            <!-- Request Status Chart -->
            <div class="col-lg-6">
                <div class="card card-custom">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Request Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trends Chart -->
            <div class="col-lg-6">
                <div class="card card-custom">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="trendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests and Quick Actions -->
        <div class="row g-4">
            <!-- Recent Requests Table -->
            <div class="col-lg-8">
                <div class="card card-custom">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Recent Requests</h5>
                        <a href="{{ route('pfmo.facility-requests') }}" class="btn btn-primary btn-sm btn-custom">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-custom">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Requester</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dashboard['recent_requests'] ?? [] as $request)
                                    <tr>
                                        <td><strong>#{{ $request->form_request_id }}</strong></td>
                                        <td>
                                            <div>
                                                <strong>{{ $request->requester->name ?? 'Unknown' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $request->requester->employeeInfo->department ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info badge-custom">
                                                {{ $request->iomDetails->first()->type_of_service ?? 'General' }}
                                            </span>
                                        </td>
                                        <td>{{ $request->date_submitted ? \Carbon\Carbon::parse($request->date_submitted)->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'Pending' => 'warning',
                                                    'Approved' => 'success',
                                                    'Rejected' => 'danger',
                                                    'In Progress' => 'info'
                                                ];
                                                $statusColor = $statusColors[$request->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }} badge-custom">{{ $request->status }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>No recent requests found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card card-custom">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="{{ route('pfmo.facility-requests') }}" class="action-btn">
                                <i class="fas fa-clipboard-list fa-lg mb-2"></i>
                                <div>View All Requests</div>
                            </a>
                            
                            <a href="{{ route('pfmo.approvals') }}" class="action-btn">
                                <i class="fas fa-check-circle fa-lg mb-2"></i>
                                <div>Manage Approvals</div>
                            </a>
                            
                            <a href="#" class="action-btn">
                                <i class="fas fa-chart-bar fa-lg mb-2"></i>
                                <div>Generate Reports</div>
                            </a>
                            
                            <a href="#" class="action-btn">
                                <i class="fas fa-cog fa-lg mb-2"></i>
                                <div>System Settings</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Department Overview -->
                <div class="card card-custom mt-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="fas fa-building me-2"></i>Departments</h5>
                    </div>
                    <div class="card-body">
                        @forelse($dashboard['sub_departments'] ?? [] as $dept)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>{{ $dept->sub_department_name }}</strong>
                                <br>
                                <small class="text-muted">{{ $dept->supervisor->name ?? 'No Supervisor' }}</small>
                            </div>
                            <span class="badge bg-primary">Active</span>
                        </div>
                        @if(!$loop->last)<hr class="my-2">@endif
                        @empty
                        <p class="text-muted text-center">No departments configured</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js Configuration -->
    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Rejected', 'In Progress'],
                datasets: [{
                    data: [
                        {{ $dashboard['stats']['pending_requests'] ?? 0 }},
                        {{ $dashboard['stats']['approved_requests'] ?? 0 }},
                        {{ $dashboard['stats']['rejected_requests'] ?? 0 }},
                        {{ ($dashboard['stats']['total_requests'] ?? 0) - ($dashboard['stats']['pending_requests'] ?? 0) - ($dashboard['stats']['approved_requests'] ?? 0) - ($dashboard['stats']['rejected_requests'] ?? 0) }}
                    ],
                    backgroundColor: [
                        '#f59e0b',
                        '#10b981',
                        '#ef4444',
                        '#06b6d4'
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

        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Requests',
                    data: [12, 19, 15, 25, 22, 30],
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
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
