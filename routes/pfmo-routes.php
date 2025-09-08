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
    Route::get('/dashboard', [\App\Http\Controllers\PFMOController::class, 'dashboard'])
        ->name('dashboard')
        ->middleware('can:access-pfmo');

    // Employee Management Routes (PFMO Head only)
    // Show manage employees page
    Route::get('/manage-employees', [\App\Http\Controllers\PFMOController::class, 'manageEmployees'])
        ->name('manage-employees')
        ->middleware('can:access-pfmo');

    // Employee assignment endpoints
    Route::post('/assign-employee', [\App\Http\Controllers\PFMOController::class, 'assignEmployee'])
        ->name('assign-employee')
        ->middleware('can:access-pfmo');

    Route::post('/assign-supervisor', [\App\Http\Controllers\PFMOController::class, 'assignSupervisor'])
        ->name('assign-supervisor')
        ->middleware('can:access-pfmo');

    Route::post('/unassign-employee', [\App\Http\Controllers\PFMOController::class, 'unassignEmployee'])
        ->name('unassign-employee')
        ->middleware('can:access-pfmo');

    Route::post('/unassign-supervisor', [\App\Http\Controllers\PFMOController::class, 'unassignSupervisor'])
        ->name('unassign-supervisor')
        ->middleware('can:access-pfmo');

    // PFMO Approvals (redirect to facility requests for PFMO users)
    Route::get('/approvals', [\App\Http\Controllers\PFMOController::class, 'facilityRequests'])
        ->name('approvals')
        ->middleware('can:access-pfmo');

    // Facility Requests Management
    Route::get('/facility-requests', [\App\Http\Controllers\PFMOController::class, 'facilityRequests'])
        ->name('facility-requests')
        ->middleware('can:access-pfmo');

    // Individual Request Details
    Route::get('/request/{id}', [PFMOController::class, 'showRequest'])
        ->name('request.show')
        ->middleware('can:access-pfmo');

    // Process Approval/Denial
    Route::post('/request/{id}/process', [PFMOController::class, 'processApproval'])
        ->name('request.process')
        ->middleware('can:approve-pfmo-requests');

    // Bulk Actions
    Route::post('/bulk-action', [PFMOController::class, 'bulkAction'])
        ->name('bulk-action')
        ->middleware('can:approve-pfmo-requests');

    // Performance Metrics
    Route::get('/metrics', [PFMOController::class, 'metrics'])
        ->name('metrics')
        ->middleware('can:access-pfmo');

    // Supervisor Management (PFMO Head only)
    Route::get('/supervisors', [SupervisorAssignmentController::class, 'index'])
        ->name('supervisors')
        ->middleware('can:access-pfmo');

    // AJAX Supervisor Management Routes
    Route::post('/supervisor/assign', [PFMOController::class, 'assignSupervisor'])
        ->name('supervisor.assign')
        ->middleware('can:access-pfmo');
    
    Route::post('/supervisor/remove', [PFMOController::class, 'removeSupervisor'])
        ->name('supervisor.remove')
        ->middleware('can:access-pfmo');
    
    Route::get('/supervisors/available', [PFMOController::class, 'getAvailableSupervisors'])
        ->name('supervisors.available')
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
