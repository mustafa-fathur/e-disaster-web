<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsOfficerOrVolunteer;
use App\Http\Middleware\EnsureUserIsAssignedToDisaster;
use App\Http\Middleware\EnsureUserCanAccessAPI;

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
            'active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
            'officer_or_volunteer' => EnsureUserIsOfficerOrVolunteer::class,
            'disaster_assigned' => EnsureUserIsAssignedToDisaster::class,
            'api_access' => EnsureUserCanAccessAPI::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
