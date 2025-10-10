<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DisasterController;

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
        return response()->json(['status' => 'aman']);
    });

    // Public Auth Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Protected Endpoints (Officer/Volunteer) - General Access
    Route::middleware(['api_access'])->group(function () {
        
        // Auth endpoints
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Dashboard
        Route::get('/dashboard', [DisasterController::class, 'dashboard']);

        // Notifications
        Route::get('/notifications', [DisasterController::class, 'notifications']);
        Route::put('/notifications/{id}/read', [DisasterController::class, 'markNotificationRead']);
        Route::put('/notifications/read-all', [DisasterController::class, 'markAllNotificationsRead']);

        // Disaster Management (General Access - Read Only)
        Route::get('/disasters', [DisasterController::class, 'index']);
        Route::get('/disasters/{id}', [DisasterController::class, 'show']);
        Route::post('/disasters', [DisasterController::class, 'createDisaster']);

        // Disaster Volunteer Management (General Access)
        Route::get('/disasters/{id}/volunteers', [DisasterController::class, 'getDisasterVolunteers']);
        Route::post('/disasters/{id}/volunteers', [DisasterController::class, 'assignVolunteerToDisaster']);
        Route::delete('/disasters/{id}/volunteers/{volunteerId}', [DisasterController::class, 'removeVolunteerFromDisaster']);

    });

    // Disaster-Assigned Endpoints (Officer/Volunteer assigned to specific disaster)
    Route::middleware(['api_access', 'disaster_assigned'])->group(function () {
        
        // Disaster Management (Only for assigned volunteers/officers)
        Route::put('/disasters/{id}', [DisasterController::class, 'updateDisaster']);
        Route::delete('/disasters/{id}', [DisasterController::class, 'deleteDisaster']);

        // Disaster Reports (Only for assigned volunteers/officers)
        Route::get('/disasters/{id}/reports', [DisasterController::class, 'getDisasterReports']);
        Route::post('/disasters/{id}/reports', [DisasterController::class, 'createDisasterReport']);
        Route::get('/disasters/{id}/reports/{reportId}', [DisasterController::class, 'getDisasterReport']);
        Route::put('/disasters/{id}/reports/{reportId}', [DisasterController::class, 'updateDisasterReport']);
        Route::delete('/disasters/{id}/reports/{reportId}', [DisasterController::class, 'deleteDisasterReport']);

        // Disaster Victims (Only for assigned volunteers/officers)
        Route::get('/disasters/{id}/victims', [DisasterController::class, 'getDisasterVictims']);
        Route::post('/disasters/{id}/victims', [DisasterController::class, 'createDisasterVictim']);
        Route::get('/disasters/{id}/victims/{victimId}', [DisasterController::class, 'getDisasterVictim']);
        Route::put('/disasters/{id}/victims/{victimId}', [DisasterController::class, 'updateDisasterVictim']);
        Route::delete('/disasters/{id}/victims/{victimId}', [DisasterController::class, 'deleteDisasterVictim']);

        // Disaster Aids (Only for assigned volunteers/officers)
        Route::get('/disasters/{id}/aids', [DisasterController::class, 'getDisasterAids']);
        Route::post('/disasters/{id}/aids', [DisasterController::class, 'createDisasterAid']);
        Route::get('/disasters/{id}/aids/{aidId}', [DisasterController::class, 'getDisasterAid']);
        Route::put('/disasters/{id}/aids/{aidId}', [DisasterController::class, 'updateDisasterAid']);
        Route::delete('/disasters/{id}/aids/{aidId}', [DisasterController::class, 'deleteDisasterAid']);

    });

});

// Other versions if needed.