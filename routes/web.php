<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

// Test routes for middleware
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
