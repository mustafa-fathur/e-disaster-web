<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Public
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Unified dashboard (role-aware)
Route::get('dashboard', App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

// Authenticated settings (shared for all roles)
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Admin-only area (no URL prefix; guarded by middleware)
Route::middleware(['auth', 'active', 'admin'])->group(function () {
    // Admin Dashboard (also accessible via unified /dashboard)
    Route::get('/admin-dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])
        ->name('admin.dashboard.alt');

    // User Management
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'showUser'])->name('admin.users.show');
    Route::patch('/users/{user}/status', [App\Http\Controllers\AdminController::class, 'updateUserStatus'])->name('admin.users.status');

    // Volunteer Management
    Route::get('/volunteers', [App\Http\Controllers\AdminController::class, 'volunteers'])->name('admin.volunteers');
    Route::patch('/volunteers/{user}/approve', [App\Http\Controllers\AdminController::class, 'approveVolunteer'])->name('admin.volunteers.approve');
    Route::patch('/volunteers/{user}/reject', [App\Http\Controllers\AdminController::class, 'rejectVolunteer'])->name('admin.volunteers.reject');

    // Officer Management
    Route::get('/officers/create', [App\Http\Controllers\AdminController::class, 'createOfficer'])->name('admin.officers.create');
    Route::post('/officers', [App\Http\Controllers\AdminController::class, 'storeOfficer'])->name('admin.officers.store');
});

// Diagnostic test routes (temporary)
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/test/active', function () {
        return response()->json([
            'message' => 'You are an active user!',
            'user' => auth()->user()->only(['name', 'email', 'type', 'status'])
        ]);
    })->name('test.active');
});

Route::middleware(['auth', 'active', 'admin'])->group(function () {
    Route::get('/test/admin', function () {
        return response()->json([
            'message' => 'You are an admin!',
            'user' => auth()->user()->only(['name', 'email', 'type', 'status'])
        ]);
    })->name('test.admin');
});

Route::middleware(['auth', 'active', 'officer_or_volunteer'])->group(function () {
    Route::get('/test/officer-volunteer', function () {
        return response()->json([
            'message' => 'You are an officer or volunteer!',
            'user' => auth()->user()->only(['name', 'email', 'type', 'status'])
        ]);
    })->name('test.officer-volunteer');
});

require __DIR__.'/auth.php';
