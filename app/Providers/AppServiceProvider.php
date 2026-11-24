<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-platform', fn (User $user) => $user->isAdmin());
        Gate::define('submit-project', fn (User $user) =>
            $user->role === User::ROLE_DEVELOPER && $user->status === User::STATUS_ACTIVE
        );

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('register', fn (Request $request) =>
            Limit::perHour(3)->by($request->ip())
        );

        RateLimiter::for('email-code', fn (Request $request) =>
            Limit::perMinute(6)->by(Str::lower((string) ($request->user()?->email ?? $request->input('email', 'guest'))).'|'.$request->ip())
        );

        RateLimiter::for('email-resend', fn (Request $request) =>
            Limit::perMinute(1)->by(Str::lower((string) ($request->user()?->email ?? $request->session()->get('password_reset_email', 'guest'))).'|'.$request->ip())
        );

        RateLimiter::for('password-change', fn (Request $request) =>
            Limit::perMinute(5)->by($request->user()->id.'|'.$request->ip())
        );

        RateLimiter::for('github-verify', fn (Request $request) =>
            Limit::perMinute(30)->by($request->ip())
        );

        RateLimiter::for('database-credentials', fn (Request $request) =>
            Limit::perMinute(10)->by($request->user()->id.'|'.$request->ip())
        );
    }
}
