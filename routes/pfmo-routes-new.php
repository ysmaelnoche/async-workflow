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

    // PFMO Approvals (redirect to facility requests for PFMO users)
    Route::get('/approvals', [\App\Http\Controllers\PFMOController::class, 'facilityRequests'])
        ->name('approvals')
        ->middleware('can:access-pfmo');

    // Facility Requests Management
    Route::get('/facility-requests', [\App\Http\Controllers\PFMOController::class, 'facilityRequests'])
        ->name('facility-requests')
        ->middleware('can:access-pfmo');

    // Individual Request Handling
    Route::get('/request/{id}', [\App\Http\Controllers\PFMOController::class, 'showRequest'])
        ->name('request.show')
        ->middleware('can:access-pfmo');

    Route::post('/request/{id}/process', [\App\Http\Controllers\PFMOController::class, 'processApproval'])
        ->name('request.process')
        ->middleware('can:access-pfmo');

    // Bulk Actions
    Route::post('/bulk-action', [\App\Http\Controllers\PFMOController::class, 'bulkAction'])
        ->name('bulk-action')
        ->middleware('can:access-pfmo');

    // Metrics and Analytics
    Route::get('/metrics', [\App\Http\Controllers\PFMOController::class, 'metrics'])
        ->name('metrics')
        ->middleware('can:access-pfmo');

    // Additional PFMO Management Routes
    
    // Supervisor Assignment Routes
    Route::post('/supervisor/assign', [\App\Http\Controllers\PFMOController::class, 'assignSupervisor'])
        ->name('supervisor.assign')
        ->middleware('can:access-pfmo');

    Route::post('/supervisor/remove', [\App\Http\Controllers\PFMOController::class, 'removeSupervisor'])
        ->name('supervisor.remove')
        ->middleware('can:access-pfmo');

    Route::get('/supervisors/available', [\App\Http\Controllers\PFMOController::class, 'getAvailableSupervisors'])
        ->name('supervisors.available')
        ->middleware('can:access-pfmo');

    // AI-powered categorization route
    Route::post('/categorize', function(\Illuminate\Http\Request $request) {
        $description = $request->input('description', '');
        
        // Simple categorization logic based on keywords
        $categories = [
            'Construction' => ['build', 'construct', 'repair', 'renovation', 'ceiling', 'wall', 'floor', 'paint'],
            'Warehouse' => ['storage', 'warehouse', 'inventory', 'stock', 'supply'],
            'General Services' => ['clean', 'maintenance', 'general', 'service', 'utility'],
            'Housekeeping' => ['housekeeping', 'janitorial', 'cleaning', 'sanitation'],
            'Transportation' => ['transport', 'vehicle', 'delivery', 'logistics', 'car', 'truck']
        ];
        
        $suggestions = [];
        $description_lower = strtolower($description);
        
        foreach ($categories as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($description_lower, $keyword) !== false) {
                    $score++;
                }
            }
            if ($score > 0) {
                $suggestions[] = [
                    'category' => $category,
                    'confidence' => min($score / count($keywords), 1.0) * 100
                ];
            }
        }
        
        // Sort by confidence
        usort($suggestions, function($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });
        
        // Limit to top 3 suggestions
        $suggestions = array_slice($suggestions, 0, 3);
        
        return response()->json(['suggestions' => $suggestions]);
    })->name('categorize')->middleware('can:access-pfmo');

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

});
