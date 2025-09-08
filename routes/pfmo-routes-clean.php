<?php

/**
 * PFMO (Physical Facilities Management Office) Routes
 * Specialized routes for facility request management
 */

use App\Http\Controllers\PFMOController;
use App\Http\Controllers\SupervisorAssignmentController;
use Illuminate\Support\Facades\Route;

// PFMO Dashboard and Management Routes
Route::middleware(['auth'])->prefix('pfmo')->name('pfmo.')->group(function () {

    // PFMO Dashboard
    Route::get('/dashboard', [PFMOController::class, 'dashboard'])
        ->name('pfmo.dashboard')
        ->middleware('can:access-pfmo');

    // Employee Management Routes (PFMO Head only)
    // Show manage employees page
    Route::get('/manage-employees', [PFMOController::class, 'manageEmployees'])
        ->name('pfmo.manage-employees')
        ->middleware('can:access-pfmo');

    // Employee assignment endpoints
    Route::post('/assign-employee', [PFMOController::class, 'assignEmployee'])
        ->name('pfmo.assign-employee')
        ->middleware('can:access-pfmo');

    Route::post('/assign-supervisor', [PFMOController::class, 'assignSupervisor'])
        ->name('pfmo.assign-supervisor')
        ->middleware('can:access-pfmo');

    Route::post('/unassign-employee', [PFMOController::class, 'unassignEmployee'])
        ->name('pfmo.unassign-employee')
        ->middleware('can:access-pfmo');

    Route::post('/unassign-supervisor', [PFMOController::class, 'unassignSupervisor'])
        ->name('pfmo.unassign-supervisor')
        ->middleware('can:access-pfmo');

    // PFMO Approvals (redirect to facility requests for PFMO users)
    Route::get('/approvals', [PFMOController::class, 'facilityRequests'])
        ->name('pfmo.approvals')
        ->middleware('can:access-pfmo');

    // Facility Requests Management
    Route::get('/facility-requests', [PFMOController::class, 'facilityRequests'])
        ->name('pfmo.facility-requests')
        ->middleware('can:access-pfmo');

    // Individual Request Details
    Route::get('/request/{id}', [PFMOController::class, 'showRequest'])
        ->name('pfmo.request.show')
        ->middleware('can:access-pfmo');

    // Process Approval/Denial
    Route::post('/request/{id}/process', [PFMOController::class, 'processApproval'])
        ->name('pfmo.request.process')
        ->middleware('can:approve-pfmo-requests');

    // Bulk Actions
    Route::post('/bulk-action', [PFMOController::class, 'bulkAction'])
        ->name('pfmo.bulk-action')
        ->middleware('can:approve-pfmo-requests');

    // Performance Metrics
    Route::get('/metrics', [PFMOController::class, 'metrics'])
        ->name('pfmo.metrics')
        ->middleware('can:access-pfmo');

    // Supervisor Management (PFMO Head only)
    Route::get('/supervisors', [SupervisorAssignmentController::class, 'index'])
        ->name('pfmo.supervisors')
        ->middleware('can:access-pfmo');

    // AJAX Supervisor Management Routes
    Route::post('/supervisor/assign', [PFMOController::class, 'assignSupervisor'])
        ->name('pfmo.supervisor.assign')
        ->middleware('can:access-pfmo');

    Route::post('/supervisor/remove', [PFMOController::class, 'removeSupervisor'])
        ->name('pfmo.supervisor.remove')
        ->middleware('can:access-pfmo');

    Route::get('/supervisors/available', [PFMOController::class, 'getAvailableSupervisors'])
        ->name('pfmo.supervisors.available')
        ->middleware('can:access-pfmo');

});

// PFMO API Routes for AJAX calls
Route::middleware(['auth', 'api'])->prefix('api/pfmo')->name('api.pfmo.')->group(function () {

    // Get dashboard data
    Route::get('/dashboard-data', function () {
        return response()->json(\App\Services\PFMOWorkflowService::getPFMODashboard());
    })->name('dashboard.data')->middleware('can:access-pfmo');

    // Get recommendations
    Route::get('/recommendations', function () {
        return response()->json(\App\Services\PFMOWorkflowService::getPFMORecommendations());
    })->name('recommendations')->middleware('can:access-pfmo');

    // Auto-categorize request
    Route::post('/categorize-request', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'description' => 'required|string',
            'title' => 'nullable|string'
        ]);

        $suggestions = \App\Services\PFMOWorkflowService::categorizePFMORequest(
            $request->description,
            $request->title ?? ''
        );

        return response()->json(['suggestions' => $suggestions]);
    })->name('categorize')->middleware('can:access-pfmo');

});
