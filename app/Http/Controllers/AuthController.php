<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        $draft = app(EmailSecurityController::class)->pendingRegistration($request);
        if ($draft) {
            return redirect()->route(! empty($draft['email_verified']) ? 'register.github' : 'register.verify-email');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'github_username' => ['nullable', 'string', 'max:39', 'regex:/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        app(EmailSecurityController::class)->storePendingRegistration($request, [
            'name' => $validated['name'],
            'email' => mb_strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'email_verified' => false,
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        $sent = app(EmailSecurityController::class)->sendRegistrationCode(mb_strtolower($validated['email']));

        return redirect()->route('register.verify-email')->with($sent ? 'success' : 'mail_error', $sent
            ? 'We sent a verification code to your email.'
            : 'Email delivery is not configured yet. Contact the platform admin.');
    }

    public function completeRegistration(Request $request): RedirectResponse
    {
        $security = app(EmailSecurityController::class);
        $draft = $security->pendingRegistration($request);
        if (! is_array($draft) || empty($draft['email_verified']) || empty($draft['expires_at']) || $draft['expires_at'] <= now()->timestamp) {
            $security->forgetPendingRegistration($request);
            return redirect()->route('register')->withErrors(['email' => 'Please complete email verification first.']);
        }
        if (User::where('email', $draft['email'])->exists()) {
            $security->forgetPendingRegistration($request);
            return redirect()->route('register')->withErrors(['email' => 'That email already has an account. Please sign in.']);
        }

        $githubProfile = $security->pendingGithubProfile($request);
        $githubProfile = is_array($githubProfile) ? $githubProfile : null;
        if ($githubProfile && (! isset($githubProfile['id'], $githubProfile['login']) || User::where('github_id', $githubProfile['id'])->orWhereRaw('LOWER(github_username) = ?', [mb_strtolower($githubProfile['login'])])->exists())) {
            return redirect()->route('register.github')->withErrors(['github_username' => 'This GitHub account is already linked to an ElimuTaifa Labs account.']);
        }

        $user = User::create([
            'name' => $draft['name'],
            'email' => $draft['email'],
            'email_verified_at' => now(),
            'github_username' => $githubProfile['login'] ?? null,
            'github_id' => $githubProfile['id'] ?? null,
            'github_verified_at' => $githubProfile ? now() : null,
            'password' => $draft['password'],
        ]);

        $security->forgetPendingRegistration($request);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Your account has been created. It is now waiting for admin approval.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = mb_strtolower($credentials['email']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email or password is incorrect.'])
                ->onlyInput('email');
        }

        if (in_array(auth()->user()->status, [User::STATUS_SUSPENDED, User::STATUS_REJECTED], true)) {
            $message = auth()->user()->status === User::STATUS_REJECTED
                ? 'This account application was not approved.'
                : 'This account is suspended. Contact platform support.';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => $message]);
        }

        $request->session()->regenerate();

        if (! auth()->user()->email_verified_at) {
            $sent = app(EmailSecurityController::class)->sendCode(auth()->user()->email, 'verify_email');
            return redirect()->route('email.verify.notice')->with($sent ? 'success' : 'mail_error', $sent
                ? 'We sent a verification code to your email address.'
                : 'Email delivery is not configured yet. Contact the platform admin to finish email setup.');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
