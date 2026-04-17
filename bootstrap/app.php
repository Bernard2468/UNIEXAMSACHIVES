<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases for Super Admin system
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'subscription.active' => \App\Http\Middleware\SubscriptionActiveMiddleware::class,
            'maintenance.check' => \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        // Apply subscription check to web middleware group (except for exempt routes)
        $middleware->web(append: [
            \App\Http\Middleware\SubscriptionActiveMiddleware::class,
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
