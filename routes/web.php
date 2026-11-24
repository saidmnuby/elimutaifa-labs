<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\EmailSecurityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeveloperModuleController;
use App\Http\Controllers\DatabaseWorkspaceController;
use App\Http\Controllers\DatabaseTableController;
use App\Http\Controllers\GitHubVerificationController;
use App\Http\Controllers\GitHubOAuthController;
use App\Http\Controllers\GitHubAppInstallationController;
use App\Http\Controllers\GitHubDeploymentWebhookController;
use App\Http\Controllers\PlatformAdminController;
use App\Http\Controllers\ProjectSubmissionController;
use Illuminate\Support\Facades\Route;

Route::post('/github/webhook', [GitHubDeploymentWebhookController::class, 'webhook'])->name('github.webhook');
Route::post('/github/deployment-callback', [GitHubDeploymentWebhookController::class, 'callback'])->name('github.deployment-callback');

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : response()->view('welcome'));

Route::get('/sitemap.xml', fn () => response()->view('sitemap')
    ->header('Content-Type', 'application/xml; charset=UTF-8'))
    ->name('sitemap');

Route::get('/github/oauth/callback', [GitHubOAuthController::class, 'callback'])
    ->middleware('throttle:github-verify')->name('github.oauth.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::get('/register/verify-email', [EmailSecurityController::class, 'registrationEmailForm'])->name('register.verify-email');
    Route::post('/register/verify-email', [EmailSecurityController::class, 'verifyRegistrationEmail'])->middleware('throttle:email-code')->name('register.verify-email.submit');
    Route::post('/register/verify-email/resend', [EmailSecurityController::class, 'resendRegistrationEmail'])->middleware('throttle:email-resend')->name('register.verify-email.resend');
    Route::post('/register/restart', [EmailSecurityController::class, 'restartRegistration'])->name('register.restart');
    Route::get('/register/github', [EmailSecurityController::class, 'registrationGithubForm'])->name('register.github');
    Route::post('/register/complete', [AuthController::class, 'completeRegistration'])->middleware('throttle:register')->name('register.complete');
    Route::get('/forgot-password', [EmailSecurityController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [EmailSecurityController::class, 'requestResetCode'])->middleware('throttle:email-code')->name('password.email');
    Route::get('/forgot-password/code', [EmailSecurityController::class, 'codeForm'])->name('password.code');
    Route::post('/forgot-password/code', [EmailSecurityController::class, 'verifyResetCode'])->middleware('throttle:email-code')->name('password.code.verify');
    Route::post('/forgot-password/resend', [EmailSecurityController::class, 'resendResetCode'])->middleware('throttle:email-resend')->name('password.code.resend');
    Route::get('/reset-password', [EmailSecurityController::class, 'resetForm'])->name('password.reset.form');
    Route::post('/reset-password', [EmailSecurityController::class, 'resetPassword'])->middleware('throttle:email-code')->name('password.reset');
    Route::get('/github/connect/register', [GitHubOAuthController::class, 'connectForRegistration'])
        ->name('github.oauth.register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/email/verify', [EmailSecurityController::class, 'verificationNotice'])->name('email.verify.notice');
    Route::post('/email/verify', [EmailSecurityController::class, 'verifyEmail'])->middleware('throttle:email-code')->name('email.verify');
    Route::post('/email/verify/resend', [EmailSecurityController::class, 'resendVerification'])->middleware('throttle:email-resend')->name('email.verify.resend');
    Route::middleware('email.verified')->group(function () {
    Route::get('/account/password', [EmailSecurityController::class, 'changePasswordForm'])->name('password.change.form');
    Route::put('/account/password', [EmailSecurityController::class, 'changePassword'])->middleware('throttle:password-change')->name('password.change');
    Route::get('/account/email', [EmailSecurityController::class, 'changeEmailForm'])->name('email.change.form');
    Route::post('/account/email', [EmailSecurityController::class, 'requestEmailChange'])->middleware('throttle:email-code')->name('email.change.request');
    Route::get('/account/email/verify', [EmailSecurityController::class, 'emailChangeCodeForm'])->name('email.change.verify.form');
    Route::post('/account/email/verify', [EmailSecurityController::class, 'verifyEmailChange'])->middleware('throttle:email-code')->name('email.change.verify');
    Route::post('/account/email/verify/resend', [EmailSecurityController::class, 'resendEmailChangeCode'])->middleware('throttle:email-resend')->name('email.change.resend');
    Route::post('/account/email/cancel', [EmailSecurityController::class, 'cancelEmailChange'])->name('email.change.cancel');
    Route::get('/activities', [ActivityLogController::class, 'index'])->name('activities.index');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
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
    Route::post('/database-accounts/{database}/credentials', [DatabaseWorkspaceController::class, 'credentials'])
        ->middleware('throttle:database-credentials')->name('database.credentials');
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
});
