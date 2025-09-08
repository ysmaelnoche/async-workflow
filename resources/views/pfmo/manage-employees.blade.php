@extends('layouts.app')

@section('title', 'Manage Employees - PFMO')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    /* Simplified, system-consistent header */
    .page-header {
        background: #f8f9fa; /* light gray */
        color: #212529; /* body text */
        padding: 1.5rem 0;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .employee-card {
        transition: all 0.15s ease;
        border: 1px solid #e9ecef;
        background: #ffffff;
    }
    
    .employee-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.04);
    }
    
    /* Supervisor: primary blue, Employee: neutral gray */
    .badge-supervisor {
        background: #0d6efd; /* bootstrap primary */
        color: #fff;
    }
    
    .badge-employee {
        background: #6c757d; /* bootstrap secondary */
        color: #fff;
    }
    
    .sub-dept-card {
        border-left: 4px solid #0d6efd; /* primary accent */
        background: #fff;
    }
    
    .action-btn {
        border: 1px solid #0d6efd;
        background: transparent;
        color: #0d6efd;
        border-radius: 0.35rem;
        padding: 0.35rem 0.65rem;
        font-size: 0.875rem;
    }
    
    .action-btn:hover {
        background: rgba(13,110,253,0.06);
        color: #0d6efd;
        transform: none;
        box-shadow: none;
    }
    
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
    }
    
    /* Make table more compact and readable */
    #employeesTable th, #employeesTable td {
        vertical-align: middle;
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">
                    <i class="fas fa-users-cog me-3"></i>
                    Manage Employees
                </h1>
                <p class="lead mb-0 mt-2">Assign employees to sub-departments and manage supervisors</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('pfmo.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Employees Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        PFMO Employees
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="employeesTable" style="margin-bottom:0;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Employee</th>
                                    <th>Position</th>
                                    <th>Current Sub-Department</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                {{-- exclude PFMO Head user from list to avoid assigning the head as supervisor/employee --}}
                                @if((isset($employee['access_role']) && $employee['access_role'] === 'Head') || (isset($employee['position']) && strtolower($employee['position']) === 'head'))
                                    @continue
                                @endif
                                <tr data-employee-id="{{ $employee['id'] }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                <span class="text-white fw-bold">
                                                    {{ substr($employee['name'], 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $employee['name'] }}</div>
                                                <small class="text-muted">{{ $employee['emp_no'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $employee['position'] ?? 'Staff' }}</span>
                                    </td>
                                    <td class="sub-department-cell">
                                        @if($employee['sub_department'])
                                            <span class="badge bg-success">{{ $employee['sub_department']['name'] }}</span>
                                        @else
                                            <span class="badge bg-secondary">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td class="role-cell">
                                        @if($employee['is_supervisor'])
                                            <span class="badge badge-supervisor text-white">
                                                <i class="fas fa-crown me-1"></i>Supervisor
                                            </span>
                                        @else
                                            <span class="badge badge-employee text-white">
                                                <i class="fas fa-user me-1"></i>Employee
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary action-btn"
                                                    onclick="showAssignModal({{ $employee['id'] }}, '{{ $employee['name'] }}', {{ $employee['sub_department'] ? $employee['sub_department']['id'] : 'null' }})">
                                                <i class="fas fa-sitemap me-1"></i>Assign
                                            </button>
                                            @if($employee['sub_department'])
                                            <button type="button" class="btn btn-sm btn-outline-warning action-btn"
                                                    onclick="unassignEmployee({{ $employee['id'] }}, '{{ $employee['name'] }}')">
                                                <i class="fas fa-times me-1"></i>Unassign
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Move modals to end of document to avoid being inside scrollable containers -->

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Improve toast z-index so toasts appear above other UI
const style = document.createElement('style');
style.innerHTML = `.toast-container { z-index: 2000 !important; } .modal { z-index: 2050; }`;
document.head.appendChild(style);

// Global variables
let assignEmployeeModal, assignSupervisorModal;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals (disable autofocus to prevent automatic scroll/focus behavior)
    assignEmployeeModal = new bootstrap.Modal(document.getElementById('assignEmployeeModal'), { focus: false });
    assignSupervisorModal = new bootstrap.Modal(document.getElementById('assignSupervisorModal'), { focus: false });
});

// Show assign employee modal
function showAssignModal(employeeId, employeeName, currentSubDeptId) {
    document.getElementById('assignEmployeeId').value = employeeId;
    document.getElementById('assignEmployeeName').textContent = employeeName;
    
    // Pre-select current sub-department if assigned
    const select = document.getElementById('assignSubDepartmentId');
    select.value = currentSubDeptId || '';
    
    assignEmployeeModal.show();
}

// Show assign supervisor modal
function showSupervisorModal(subDeptId, subDeptName, currentSupervisorId, currentSupervisorName) {
    document.getElementById('supervisorSubDepartmentId').value = subDeptId;
    document.getElementById('supervisorSubDepartmentName').textContent = subDeptName;
    
    const isReassignment = currentSupervisorId !== null;
    document.getElementById('isReassignment').value = isReassignment ? 'true' : 'false';
    
    // Show/hide current supervisor info
    const currentInfo = document.getElementById('currentSupervisorInfo');
    if (isReassignment) {
        document.getElementById('currentSupervisorName').textContent = currentSupervisorName;
        currentInfo.style.display = 'block';
        document.getElementById('supervisorActionText').textContent = 'Reassign';
    } else {
        currentInfo.style.display = 'none';
        document.getElementById('supervisorActionText').textContent = 'Assign';
    }
    
    // Reset employee selection
    document.getElementById('supervisorEmployeeId').value = '';
    
    assignSupervisorModal.show();
}

// Assign employee to sub-department
function assignEmployee() {
    const formData = new FormData(document.getElementById('assignEmployeeForm'));
    
    showLoadingButton('Assigning...');
    
    fetch('{{ route("pfmo.assign-employee") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            assignEmployeeModal.hide();
            updateEmployeeRow(data.employee);
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while assigning employee');
    })
    .finally(() => {
        hideLoadingButton();
    });
}

// Assign supervisor to sub-department
function assignSupervisor() {
    const formData = new FormData(document.getElementById('assignSupervisorForm'));
    
    showLoadingButton('Assigning...');
    
    fetch('{{ route("pfmo.assign-supervisor") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            assignSupervisorModal.hide();
            // Reload page to update supervisor info and employee roles
            window.location.reload();
        } else {
            if (data.error_code === 'supervisor_exists') {
                showToast('warning', data.message);
            } else {
                showToast('error', data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while assigning supervisor');
    })
    .finally(() => {
        hideLoadingButton();
    });
}

// Unassign employee from sub-department
function unassignEmployee(employeeId, employeeName) {
    if (!confirm(`Are you sure you want to unassign ${employeeName} from their current sub-department?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('employee_id', employeeId);
    
    fetch('{{ route("pfmo.unassign-employee") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            updateEmployeeRow(data.employee);
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while unassigning employee');
    });
}

// Unassign supervisor from sub-department
function unassignSupervisor(subDeptId, subDeptName, supervisorName) {
    if (!confirm(`Are you sure you want to remove ${supervisorName} as supervisor of ${subDeptName}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('sub_department_id', subDeptId);
    
    fetch('{{ route("pfmo.unassign-supervisor") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            // Reload page to update supervisor info and employee roles
            window.location.reload();
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while unassigning supervisor');
    });
}

// Update employee row in table
function updateEmployeeRow(employee) {
    const row = document.querySelector(`tr[data-employee-id="${employee.id}"]`);
    if (!row) return;
    
    // Update sub-department cell
    const subDeptCell = row.querySelector('.sub-department-cell');
    if (employee.sub_department) {
        subDeptCell.innerHTML = `<span class="badge bg-success">${employee.sub_department.name}</span>`;
    } else {
        subDeptCell.innerHTML = `<span class="badge bg-secondary">Not Assigned</span>`;
    }
    
    // Update actions - this is simplified, in practice you might want to reload
    // the page or have more sophisticated row updating
}

// Show toast notification
function showToast(type, message) {
    const toastContainer = document.querySelector('.toast-container');
    const toastId = 'toast-' + Date.now();
    
    const toastClass = type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : 'bg-danger';
    const icon = type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${toastClass} text-white" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Show loading state on buttons
function showLoadingButton(text = 'Loading...') {
    const buttons = document.querySelectorAll('.modal-footer .btn-primary');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
    });
}

// Hide loading state on buttons
function hideLoadingButton() {
    const buttons = document.querySelectorAll('.modal-footer .btn-primary');
    buttons.forEach(btn => {
        btn.disabled = false;
        if (btn.id === 'supervisorActionText' || btn.querySelector('#supervisorActionText')) {
            const actionText = document.getElementById('supervisorActionText').textContent;
            btn.innerHTML = `<i class="fas fa-user-tie me-1"></i>${actionText}`;
        } else {
            btn.innerHTML = `<i class="fas fa-check me-1"></i>Assign`;
        }
    });
}
</script>
@endpush

<!-- Modals placed at the end of document body to prevent scroll issues -->
<!-- Assign Employee Modal -->
<div class="modal fade" id="assignEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Employee to Sub-Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignEmployeeForm">
                    <input type="hidden" id="assignEmployeeId" name="employee_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Employee:</label>
                        <div id="assignEmployeeName" class="form-control-plaintext border rounded p-2 bg-light"></div>
                    </div>

                    <div class="mb-3">
                        <label for="assignSubDepartmentId" class="form-label fw-bold">Sub-Department:</label>
                        <select class="form-select" id="assignSubDepartmentId" name="sub_department_id" required>
                            <option value="">Select Sub-Department</option>
                            @foreach($subDepartments as $subDept)
                            <option value="{{ $subDept['id'] }}">{{ $subDept['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignEmployee()">
                    <i class="fas fa-check me-1"></i>Assign
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Supervisor Modal -->
<div class="modal fade" id="assignSupervisorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Supervisor to Sub-Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignSupervisorForm">
                    <input type="hidden" id="supervisorSubDepartmentId" name="sub_department_id">
                    <input type="hidden" id="isReassignment" name="reassign" value="false">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sub-Department:</label>
                        <div id="supervisorSubDepartmentName" class="form-control-plaintext border rounded p-2 bg-light"></div>
                    </div>

                    <div id="currentSupervisorInfo" class="mb-3" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Current Supervisor:</strong> <span id="currentSupervisorName"></span><br>
                            <small>This will replace the current supervisor.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="supervisorEmployeeId" class="form-label fw-bold">New Supervisor:</label>
                        <select class="form-select" id="supervisorEmployeeId" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                {{-- Exclude PFMO Head by checking position/access_role if present in the transformed data --}}
                                @if((isset($employee['position']) && $employee['position'] === 'Head') || (isset($employee['access_role']) && $employee['access_role'] === 'Head'))
                                    @continue
                                @endif
                                <option value="{{ $employee['id'] }}">{{ $employee['name'] }} ({{ $employee['position'] ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="assignSupervisor()">
                    <i class="fas fa-user-tie me-1"></i><span id="supervisorActionText">Assign</span>
                </button>
            </div>
        </div>
    </div>
</div>
