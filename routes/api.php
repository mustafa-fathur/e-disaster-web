<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

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
    });

    // Public Auth Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Protected Endpoints (Officer/Volunteer)
    Route::middleware(['api_auth', 'api_access'])->group(function () {
        // Auth endpoints
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

});

// Other versions if needed.