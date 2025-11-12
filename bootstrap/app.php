<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Load helpers manually
require_once __DIR__ . '/../app/helpers.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'student.auth' => \App\Http\Middleware\StudentAuth::class,
            'callback' => \App\Http\Middleware\CallbackMiddleware::class,
            'disable.csrf' => \App\Http\Middleware\DisableCsrfMiddleware::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
            'spmb.admin' => \App\Http\Middleware\SPMBAdminMiddleware::class,
            'bk.only' => \App\Http\Middleware\BKMiddleware::class,
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
            'school.context' => \App\Http\Middleware\SchoolContext::class,
        ]);
        
        // Completely disable CSRF middleware for all routes
        // $middleware->web([
        //     \App\Http\Middleware\VerifyCsrfToken::class,
        // ]);
        
                        // Minimal web middleware - hanya yang ada
                $middleware->web([
                    \Illuminate\Session\Middleware\StartSession::class,
                    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                    // \App\Http\Middleware\VerifyCsrfToken::class, // Disabled for webhook compatibility
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                    \App\Http\Middleware\SchoolContext::class,
                ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
