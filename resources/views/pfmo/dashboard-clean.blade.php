<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PFMO Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --secondary-blue: #3b82f6;
            --light-blue: #dbeafe;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --white: #ffffff;
            --purple: #8b5cf6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.5;
        }

        .navbar {
            background-color: var(--white) !important;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--gray-800) !important;
        }

        .nav-link {
            color: var(--gray-600) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-blue) !important;
            background-color: var(--light-blue);
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-header {
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .last-updated {
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--white);
        }

        .stat-icon.total { background-color: var(--primary-blue); }
        .stat-icon.pending { background-color: var(--warning); }
        .stat-icon.approved { background-color: var(--success); }
        .stat-icon.rejected { background-color: var(--danger); }
        .stat-icon.today { background-color: var(--secondary-blue); }
        .stat-icon.departments { background-color: var(--gray-600); }
        .stat-icon.rating { background-color: var(--purple); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .chart-container, .departments-section {
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary-blue);
        }

        .department-item {
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
            background-color: var(--gray-50);
        }

        .department-item:hover {
            background-color: var(--light-blue);
            border-color: var(--primary-blue);
        }

        .department-name {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .supervisor-info {
            color: var(--gray-600);
            font-size: 0.75rem;
        }

        /* Chart Legend */
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            justify-content: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        /* Rating Display */
        .rating-display {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            color: #fbbf24;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.25rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .chart-container, .departments-section {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('images/lyceum-logo.png') }}" alt="Logo" style="height: 32px; margin-right: 0.5rem;">
                PFMO Dashboard
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a class="nav-link" href="#"><i class="fas fa-file-alt me-2"></i>Requests</a>
                <a class="nav-link" href="#"><i class="fas fa-check-circle me-2"></i>Approvals</a>
                <a class="nav-link" href="#"><i class="fas fa-briefcase me-2"></i>Job Orders</a>
            </div>
            
            <div class="dropdown ms-3">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-2"></i>User
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">PFMO Dashboard</h1>
            <p class="page-subtitle">Physical Facilities Management Office - Workflow System</p>
            <p class="last-updated">Last updated: {{ now()->format('M d, Y H:i A') }}</p>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon total">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-number">{{ $totalRequests ?? 15 }}</div>
                <div class="stat-label">Total Requests</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-number">{{ $pendingRequests ?? 8 }}</div>
                <div class="stat-label">Pending</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-number">{{ $approvedRequests ?? 5 }}</div>
                <div class="stat-label">Approved</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon rejected">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="stat-number">{{ $rejectedRequests ?? 2 }}</div>
                <div class="stat-label">Rejected</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon today">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-number">{{ $todayApproved ?? 3 }}</div>
                <div class="stat-label">Today Approved</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon departments">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="stat-number">{{ $departmentCount ?? 5 }}</div>
                <div class="stat-label">Departments</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon rating">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-number">4.2</div>
                <div class="stat-label">Avg. Rating</div>
                <div class="rating-display mt-2">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <small class="text-muted">({{ $feedbackCount ?? 48 }} reviews)</small>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Chart Section -->
            <div class="chart-container">
                <h3 class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    Request Status Overview
                </h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #f59e0b;"></div>
                        <span>Pending</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #10b981;"></div>
                        <span>Approved</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ef4444;"></div>
                        <span>Rejected</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #3b82f6;"></div>
                        <span>In Progress</span>
                    </div>
                </div>
            </div>

            <!-- Departments Section -->
            <div class="departments-section">
                <h3 class="section-title">
                    <i class="fas fa-building"></i>
                    Department Management
                </h3>
                @if(isset($subDepartments) && $subDepartments->count() > 0)
                    @foreach($subDepartments as $dept)
                        <div class="department-item">
                            <div class="department-name">{{ $dept->name }}</div>
                            <div class="supervisor-info">
                                @if($dept->supervisor)
                                    <i class="fas fa-user me-1"></i>
                                    Supervisor: {{ $dept->supervisor->first_name }} {{ $dept->supervisor->last_name }}
                                @else
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    No supervisor assigned
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="department-item">
                        <div class="department-name">Information Technology</div>
                        <div class="supervisor-info">
                            <i class="fas fa-user me-1"></i>
                            Supervisor: John Doe
                        </div>
                    </div>
                    <div class="department-item">
                        <div class="department-name">Physical Facilities</div>
                        <div class="supervisor-info">
                            <i class="fas fa-user me-1"></i>
                            Supervisor: Jane Smith
                        </div>
                    </div>
                    <div class="department-item">
                        <div class="department-name">Maintenance</div>
                        <div class="supervisor-info">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            No supervisor assigned
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Chart Configuration
        const ctx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Rejected', 'In Progress'],
                datasets: [{
                    data: [
                        {{ $pendingRequests ?? 8 }},
                        {{ $approvedRequests ?? 5 }},
                        {{ $rejectedRequests ?? 2 }},
                        {{ $inProgressRequests ?? 3 }}
                    ],
                    backgroundColor: [
                        '#f59e0b',
                        '#10b981',
                        '#ef4444',
                        '#3b82f6'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
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
                cutout: '60%'
            }
        });

        // Animate number counters
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.innerText);
                if (isNaN(target)) return;
                
                let current = 0;
                const increment = target / 30;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.innerText = target;
                        clearInterval(timer);
                    } else {
                        counter.innerText = Math.ceil(current);
                    }
                }, 50);
            });
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(animateCounters, 500);
        });
    </script>
</body>
</html>
