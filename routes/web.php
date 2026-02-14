<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserManagementController;

// controller versi Stored Procedure
use App\Http\Controllers\ProcessSpController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Admin-only (User Management)
Route::middleware(['auth', 'can:manage-users'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');

        Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');

        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

// Everyone who is logged in can see Home
Route::middleware('auth')->group(function () {
    Route::get('/home', [ProcessController::class, 'home'])->name('home');
    Route::get('/dashboard', [ProcessController::class, 'home'])->name('dashboard');
});

// Only Internal + Admin can see the rest of the app
Route::middleware(['auth', 'can:view-process'])->group(function () {
    Route::get('/process/{slug}', [ProcessController::class, 'show'])
        ->whereIn('slug', ['stitches', 'strobel', 'injection', 'lasting', 'finishing', 'recap'])
        ->name('process.show');

    // ✅ Autocomplete suggestions (per-column)
    Route::get('/process/{slug}/suggest', [ProcessController::class, 'suggest'])
        ->whereIn('slug', ['stitches', 'strobel', 'injection', 'lasting', 'finishing'])
        ->name('process.suggest');

    foreach (['stitches', 'strobel', 'injection', 'lasting', 'finishing', 'recap'] as $slug) {
        Route::get('/' . $slug, fn () => redirect()->route('process.show', ['slug' => $slug]));
    }

    Route::get('/assembly/{tab?}', [ProcessController::class, 'assembly'])
        ->whereIn('tab', ['injection', 'lasting', null])
        ->name('assembly');
});
