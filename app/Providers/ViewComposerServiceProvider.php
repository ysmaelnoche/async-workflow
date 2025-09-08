<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Services\ApprovalCacheService;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                // Use cached approval count service for performance
                // Same logic as before, but now cached for 5 minutes
                $pendingCount = ApprovalCacheService::getPendingApprovalCount();
                $view->with('pendingApprovalCount', $pendingCount);
            }
        });
    }
}