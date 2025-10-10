<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DisasterController;

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
    Route::get('/admin-dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard.alt');

    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');

    // Volunteer Management
    Route::get('/volunteers', [AdminController::class, 'volunteers'])->name('admin.volunteers');
    Route::patch('/volunteers/{user}/approve', [AdminController::class, 'approveVolunteer'])->name('admin.volunteers.approve');
    Route::patch('/volunteers/{user}/reject', [AdminController::class, 'rejectVolunteer'])->name('admin.volunteers.reject');

    // Officer Management (forms handled via modals on index)
    Route::get('/officers', [AdminController::class, 'officers'])->name('admin.officers');
    Route::post('/officers', [AdminController::class, 'storeOfficer'])->name('admin.officers.store');
    Route::patch('/officers/{user}', [AdminController::class, 'updateOfficer'])->name('admin.officers.update');
    Route::delete('/officers/{user}', [AdminController::class, 'destroyOfficer'])->name('admin.officers.destroy');
    
    // Disasters
    Route::get('/disasters', [DisasterController::class, 'index'])->name('admin.disasters');
    Route::get('/disasters/create', [DisasterController::class, 'create'])->name('admin.disasters.create');
    Route::post('/disasters', [DisasterController::class, 'store'])->name('admin.disasters.store');
    Route::get('/disasters/{disaster}', [DisasterController::class, 'show'])->name('admin.disasters.show');
    Route::get('/disasters/{disaster}/edit', [DisasterController::class, 'edit'])->name('admin.disasters.edit');
    Route::patch('/disasters/{disaster}', [DisasterController::class, 'update'])->name('admin.disasters.update');
    Route::delete('/disasters/{disaster}', [DisasterController::class, 'destroy'])->name('admin.disasters.destroy');
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
