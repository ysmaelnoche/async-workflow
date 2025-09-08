<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware; // Import AdminMiddleware
use App\Http\Middleware\PreventAdminAccessMiddleware; // Import PreventAdminAccessMiddleware
use App\Http\Middleware\PreventEmployeeAccessMiddleware; // Import PreventEmployeeAccessMiddleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([ // Register route middleware aliases here
            'admin' => AdminMiddleware::class,
            'prevent.admin' => PreventAdminAccessMiddleware::class,
            'prevent.employee' => PreventEmployeeAccessMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
