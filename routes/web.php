<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperModuleController;
use App\Http\Controllers\DatabaseWorkspaceController;
use App\Http\Controllers\DatabaseTableController;
use App\Http\Controllers\GitHubVerificationController;
use App\Http\Controllers\GitHubOAuthController;
use App\Http\Controllers\GitHubAppInstallationController;
use App\Http\Controllers\PlatformAdminController;
use App\Http\Controllers\ProjectSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'));

Route::get('/github/oauth/callback', [GitHubOAuthController::class, 'callback'])
    ->middleware('throttle:github-verify')->name('github.oauth.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::get('/github/connect/register', [GitHubOAuthController::class, 'connectForRegistration'])
        ->name('github.oauth.register');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/github/connect/project', [GitHubOAuthController::class, 'connectForProject'])
        ->middleware('can:submit-project')->name('github.oauth.project');
    Route::get('/github/app/install', [GitHubAppInstallationController::class, 'start'])
        ->middleware('can:submit-project')->name('github.app.install');
    Route::get('/github/app/installed', [GitHubAppInstallationController::class, 'installed'])
        ->middleware('can:submit-project')->name('github.app.installed');
    Route::get('/github/verify/repository', [GitHubVerificationController::class, 'repository'])
        ->middleware(['can:submit-project', 'throttle:github-verify'])->name('github.verify.repository');
    Route::get('/modules/database/request', [DatabaseWorkspaceController::class, 'requestForm'])->name('database.request.create');
    Route::post('/modules/database/request', [DatabaseWorkspaceController::class, 'storeRequest'])->name('database.request.store');
    Route::get('/database-accounts/{database}', [DatabaseWorkspaceController::class, 'show'])->name('database.show');
    Route::get('/database-accounts/{database}/export', [DatabaseWorkspaceController::class, 'export'])->name('database.export');
    Route::get('/database-accounts/{database}/tables/create', [DatabaseTableController::class, 'createTable'])->name('database.tables.create');
    Route::post('/database-accounts/{database}/tables', [DatabaseTableController::class, 'storeTable'])->name('database.tables.store');
    Route::get('/database-accounts/{database}/tables/{table}', [DatabaseTableController::class, 'show'])->name('database.tables.show');
    Route::delete('/database-accounts/{database}/tables/{table}', [DatabaseTableController::class, 'dropTable'])->name('database.tables.destroy');
    Route::get('/database-accounts/{database}/tables/{table}/rows/create', [DatabaseTableController::class, 'createRow'])->name('database.rows.create');
    Route::post('/database-accounts/{database}/tables/{table}/rows', [DatabaseTableController::class, 'storeRow'])->name('database.rows.store');
    Route::get('/database-accounts/{database}/tables/{table}/rows/{rowKey}/edit', [DatabaseTableController::class, 'editRow'])->name('database.rows.edit');
    Route::put('/database-accounts/{database}/tables/{table}/rows/{rowKey}', [DatabaseTableController::class, 'updateRow'])->name('database.rows.update');
    Route::delete('/database-accounts/{database}/tables/{table}/rows/{rowKey}', [DatabaseTableController::class, 'deleteRow'])->name('database.rows.destroy');
    Route::post('/database-accounts/{database}/query', [DatabaseWorkspaceController::class, 'runQuery'])->name('database.query');
    Route::get('/modules/{moduleKey}', [DeveloperModuleController::class, 'show'])
        ->whereIn('moduleKey', ['github-deploy', 'database'])
        ->name('modules.show');

    Route::middleware('can:submit-project')->group(function () {
        Route::get('/projects/create', [ProjectSubmissionController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectSubmissionController::class, 'store'])->name('projects.store');
    });

    Route::prefix('admin')->name('admin.')->middleware('can:manage-platform')->group(function () {
        Route::get('/developers', [PlatformAdminController::class, 'developers'])->name('developers');
        Route::patch('/developers/{user}/status', [PlatformAdminController::class, 'updateDeveloperStatus'])->name('developers.status');
        Route::get('/projects', [PlatformAdminController::class, 'projects'])->name('projects');
        Route::patch('/projects/{project}/reject', [PlatformAdminController::class, 'rejectProject'])->name('projects.reject');
        Route::get('/database-requests', [PlatformAdminController::class, 'databaseRequests'])->name('database-requests');
        Route::post('/database-requests/{databaseRequest}/approve', [PlatformAdminController::class, 'approveDatabaseRequest'])->name('database-requests.approve');
        Route::patch('/database-requests/{databaseRequest}/reject', [PlatformAdminController::class, 'rejectDatabaseRequest'])->name('database-requests.reject');
        Route::get('/projects/{project}/edit', [PlatformAdminController::class, 'editProject'])->name('projects.edit');
        Route::patch('/projects/{project}', [PlatformAdminController::class, 'updateProject'])->name('projects.update');
        Route::patch('/projects/{project}/approve', [PlatformAdminController::class, 'approveProject'])->name('projects.approve');
    });
});
