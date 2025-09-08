@extends('layouts.app')

@section('title', 'Encoder Credentials - Testing')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">🔐 Encoder Authentication Credentials (For Testing)</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Encoder Credentials</li>
    </ol>

    <div class="row">
        <!-- Dean Credentials -->
        <div class="col-md-6">
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">👨‍🎓 Dean Credentials</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Valid Dean accounts for encoder authentication:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>PIN</th>
                                    <th>Authority</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Dean Smith</strong></td>
                                    <td><code>1234</code></td>
                                    <td>All Requests</td>
                                </tr>
                                <tr>
                                    <td><strong>Dr. John Doe</strong></td>
                                    <td><code>5678</code></td>
                                    <td>All Requests</td>
                                </tr>
                                <tr>
                                    <td><strong>Dean Maria Santos</strong></td>
                                    <td><code>9012</code></td>
                                    <td>All Requests</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <small><strong>Authority:</strong> Deans can authorize ALL priority levels including Emergency and High Priority requests.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secretary Credentials -->
        <div class="col-md-6">
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">👩‍💼 Secretary Credentials</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Valid Secretary accounts for encoder authentication:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>PIN</th>
                                    <th>Authority</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Secretary Johnson</strong></td>
                                    <td><code>2468</code></td>
                                    <td>Routine, Medium</td>
                                </tr>
                                <tr>
                                    <td><strong>Ms. Jane Doe</strong></td>
                                    <td><code>1357</code></td>
                                    <td>Routine, Medium</td>
                                </tr>
                                <tr>
                                    <td><strong>Secretary Ana Cruz</strong></td>
                                    <td><code>3691</code></td>
                                    <td>Routine, Medium</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <small><strong>Authority:</strong> Secretaries can handle routine and medium priority requests. Emergency and High Priority requests require Dean authorization.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Matrix -->
    <div class="row">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">📋 Authority Matrix</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="bg-danger text-white p-3 rounded text-center">
                                <strong>🚨 EMERGENCY</strong>
                                <br><small>Dean Only</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-warning text-dark p-3 rounded text-center">
                                <strong>⚡ HIGH PRIORITY</strong>
                                <br><small>Dean Preferred</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-primary text-white p-3 rounded text-center">
                                <strong>📝 MEDIUM</strong>
                                <br><small>Dean/Secretary</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-success text-white p-3 rounded text-center">
                                <strong>📋 ROUTINE</strong>
                                <br><small>Dean/Secretary</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature Integrity Solution -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">🔒 Signature Integrity Solution</h5>
                </div>
                <div class="card-body">
                    <p>This system solves the shared account signature integrity issue by:</p>
                    
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item">
                            <strong>Dual Identity Tracking:</strong> Records both "Prepared By" (staff member) and "Encoded By" (dean/secretary)
                        </li>
                        <li class="list-group-item">
                            <strong>PIN Authentication:</strong> Requires personal PIN to verify the encoder's identity
                        </li>
                        <li class="list-group-item">
                            <strong>Authority Validation:</strong> Checks if the encoder has authority for the request priority level
                        </li>
                        <li class="list-group-item">
                            <strong>Timestamp Logging:</strong> Records exact time of encoding for audit trail
                        </li>
                        <li class="list-group-item">
                            <strong>Role-Based Authorization:</strong> Different authority levels for different roles
                        </li>
                    </ol>
                    
                    <div class="alert alert-success mt-3">
                        <strong>Result:</strong> Even with a shared account, the system knows exactly who encoded each request and whether they had the authority to do so.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
