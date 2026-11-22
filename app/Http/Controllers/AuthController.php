<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
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

        $githubProfile = $request->session()->get('github_verified_profile');
        $githubProfile = is_array($githubProfile) ? $githubProfile : null;

        if (! empty($validated['github_username'])
            && (! $githubProfile
                || ! isset($githubProfile['id'], $githubProfile['login'])
                || mb_strtolower($validated['github_username']) !== mb_strtolower($githubProfile['login']))) {
            throw ValidationException::withMessages([
                'github_username' => 'Connect the GitHub account you entered to verify that you own it, or leave this field blank.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => mb_strtolower($validated['email']),
            'github_username' => $githubProfile && ! empty($validated['github_username']) ? $githubProfile['login'] : null,
            'github_id' => $githubProfile && ! empty($validated['github_username']) ? $githubProfile['id'] : null,
            'github_verified_at' => $githubProfile && ! empty($validated['github_username']) ? now() : null,
            'password' => Hash::make($validated['password']),
        ]);

        $request->session()->forget('github_verified_profile');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
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
                ->withErrors(['email' => 'Email au password si sahihi.'])
                ->onlyInput('email');
        }

        if (in_array(auth()->user()->status, [User::STATUS_SUSPENDED, User::STATUS_REJECTED], true)) {
            $message = auth()->user()->status === User::STATUS_REJECTED
                ? 'Usajili wa akaunti hii haujaidhinishwa.'
                : 'Akaunti hii imesimamishwa. Wasiliana na admin.';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => $message]);
        }

        $request->session()->regenerate();

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
