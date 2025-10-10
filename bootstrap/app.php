<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register role-based middleware aliases
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'officer_or_volunteer' => \App\Http\Middleware\EnsureUserIsOfficerOrVolunteer::class,
            'web_access' => \App\Http\Middleware\EnsureUserCanAccessWeb::class,
            'api_access' => \App\Http\Middleware\EnsureUserCanAccessAPI::class,
            'api_auth' => \App\Http\Middleware\EnsureApiAuthentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
