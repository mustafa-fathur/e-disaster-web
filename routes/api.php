<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Mobile (Android) REST API routes. These are versioned and return JSON.
| Swap closures with real controllers as features are implemented.
*/

// Version 1
Route::prefix('v1')->group(function () {

    // Healthcheck
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    })->name('api.v1.health');

    // Public Auth Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/login', function () {
            // TODO: Implement login via Sanctum or JWT
            return response()->json(['message' => 'Login endpoint placeholder'], 200);
        })->name('api.v1.auth.login');

        Route::post('/register', function () {
            // Volunteer registration (requires admin approval)
            return response()->json(['message' => 'Register endpoint placeholder'], 201);
        })->name('api.v1.auth.register');
    });

});

// Other versions if needed.