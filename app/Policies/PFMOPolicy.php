<?php

namespace App\Policies;

use App\Models\User;

class PFMOPolicy
{
    /**
     * Check if user can access PFMO functionality
     */
    public function accessPFMO(User $user): bool
    {
        // Check if user is in PFMO department
        if ($user->department && $user->department->dept_code === 'PFMO') {
            return true;
        }

        // Check if user has specific PFMO permissions
        if ($user->accessRole === 'Approver' && $user->position === 'VPAA') {
            return true;
        }

        return false;
    }

    /**
     * Check if user can approve PFMO requests
     */
    public function approvePFMORequests(User $user): bool
    {
        // PFMO staff can approve requests
        if ($user->department && $user->department->dept_code === 'PFMO') {
            return $user->accessRole === 'Approver';
        }

        // VPAA can approve any requests
        if ($user->accessRole === 'Approver' && $user->position === 'VPAA') {
            return true;
        }

        return false;
    }

    /**
     * Check if user can manage PFMO workflows
     */
    public function managePFMOWorkflows(User $user): bool
    {
        // PFMO head can manage workflows
        if ($user->department && $user->department->dept_code === 'PFMO' && $user->position === 'Head') {
            return true;
        }

        // VPAA can manage any workflows
        if ($user->accessRole === 'Approver' && $user->position === 'VPAA') {
            return true;
        }

        return false;
    }

    /**
     * Check if user can view PFMO metrics
     */
    public function viewPFMOMetrics(User $user): bool
    {
        // PFMO staff can view metrics
        if ($user->department && $user->department->dept_code === 'PFMO') {
            return true;
        }

        // VPAA can view any metrics
        if ($user->accessRole === 'Approver' && $user->position === 'VPAA') {
            return true;
        }

        return false;
    }

    /**
     * Check if user can assign PFMO requests
     */
    public function assignPFMORequests(User $user): bool
    {
        // PFMO head can assign requests
        if ($user->department && $user->department->dept_code === 'PFMO' && $user->position === 'Head') {
            return true;
        }

        // VPAA can assign any requests
        if ($user->accessRole === 'Approver' && $user->position === 'VPAA') {
            return true;
        }

        return false;
    }
}

