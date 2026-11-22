<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlatformAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->middleware('can:manage-platform')->group(function () {
        Route::get('/developers', [PlatformAdminController::class, 'developers'])->name('developers');
        Route::patch('/developers/{user}/status', [PlatformAdminController::class, 'updateDeveloperStatus'])->name('developers.status');
        Route::get('/projects', [PlatformAdminController::class, 'projects'])->name('projects');
        Route::get('/projects/create', [PlatformAdminController::class, 'createProject'])->name('projects.create');
        Route::post('/projects', [PlatformAdminController::class, 'storeProject'])->name('projects.store');
    });
});
