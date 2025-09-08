<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PFMODashboardController;
use App\Http\Controllers\PFMOEmployeeController;
use App\Http\Controllers\PFMOController;

/**
 * PFMO (Physical Facilities Management Office) Routes
 * All routes prefixed with /pfmo and named with pfmo.
 */
Route::middleware(['auth'])->prefix('pfmo')->name('pfmo.')->group(function () {
    
    // Dashboard route
    Route::get('/dashboard', [PFMODashboardController::class, 'index'])->name('dashboard');
    
    // Employee Management routes
    Route::get('/manage-employees', [PFMOEmployeeController::class, 'index'])->name('manage-employees');
    
    // Supervisor management routes
    Route::post('/assign-supervisor', [PFMOEmployeeController::class, 'assignSupervisor'])->name('assign-supervisor');
    Route::post('/remove-supervisor', [PFMOEmployeeController::class, 'removeSupervisor'])->name('remove-supervisor');
    
    // Employee assignment routes
    Route::post('/assign-employee', [PFMOEmployeeController::class, 'assignEmployee'])->name('assign-employee');
    Route::post('/unassign-employee', [PFMOEmployeeController::class, 'unassignEmployee'])->name('unassign-employee');
    Route::post('/unassign-supervisor', [PFMOEmployeeController::class, 'unassignSupervisor'])->name('unassign-supervisor');
    
    // Request management routes (using existing PFMOController for backward compatibility)
    Route::get('/approvals', [PFMOController::class, 'facilityRequests'])->name('approvals');
    Route::get('/facility-requests', [PFMOController::class, 'facilityRequests'])->name('facility-requests');
    Route::get('/request/{id}', [PFMOController::class, 'showRequest'])->name('request.show');
    Route::post('/request/{id}/process', [PFMOController::class, 'processApproval'])->name('request.process');
    Route::post('/bulk-action', [PFMOController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/metrics', [PFMOController::class, 'metrics'])->name('metrics');
    
});
