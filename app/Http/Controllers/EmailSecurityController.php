<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use App\Support\ActivityLogger;

class EmailSecurityController extends Controller
{
    private const CODE_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    public function sendCode(string $email, string $purpose): bool
    {
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        DB::table('email_security_codes')->where('email', $email)->where('purpose', $purpose)->delete();
        DB::table('email_security_codes')->insert([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::CODE_MINUTES),
            'attempts' => 0,
            'consumed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $verification = $purpose === 'verify_email';
        $emailChange = $purpose === 'change_email';
        $sent = app(\App\Support\SystemMailer::class)->to(
            $email,
            $verification ? 'Verify your email · Elimutaifa Labs' : ($emailChange ? 'Confirm your new email · Elimutaifa Labs' : 'Password reset code · Elimutaifa Labs'),
            [
                'eyebrow' => $verification ? 'Email verification' : ($emailChange ? 'Email change' : 'Account security'),
                'title' => $verification ? 'Verify your email' : ($emailChange ? 'Confirm your new email' : 'Reset your password'),
                'intro' => $verification
                    ? 'Use this code to verify your email and finish setting up your account.'
                    : ($emailChange
                        ? 'Use this code to confirm this new email address for your Elimutaifa Labs account.'
                        : 'Use this code to confirm your email and choose a new password.'),
                'code' => $code,
                'note' => 'This code expires in 10 minutes. If you did not request it, you can ignore this email.',
            ]
        );
        if (! $sent) {
            DB::table('email_security_codes')->where('email', $email)->where('purpose', $purpose)->delete();
            return false;
        }

        return true;
    }

    public function verificationNotice(): View|RedirectResponse
    {
        if (auth()->user()->email_verified_at) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    public function registrationEmailForm(Request $request): View|RedirectResponse
    {
        $draft = $this->pendingRegistration($request);
        if (! is_array($draft)) {
            return redirect()->route('register');
        }
        if (! empty($draft['email_verified'])) {
            return redirect()->route('register.github');
        }

        return view('auth.register-verify-email', ['email' => $draft['email']]);
    }

    public function verifyRegistrationEmail(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $draft = $this->pendingRegistration($request);
        if (! is_array($draft) || empty($draft['email'])) {
            return redirect()->route('register');
        }

        if (! $this->consumeRegistrationCode($draft['email'], $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'That code is invalid or expired. Request a new one.']);
        }

        $draft['email_verified'] = true;
        $this->storePendingRegistration($request, $draft);

        return redirect()->route('register.github')->with('success', 'Email verified.');
    }

    public function resendRegistrationEmail(Request $request): RedirectResponse
    {
        $draft = $this->pendingRegistration($request);
        if (! is_array($draft) || empty($draft['email'])) {
            return redirect()->route('register');
        }

        $sent = $this->sendRegistrationCode($draft['email']);

        return $sent
            ? back()->with('success', 'A new code has been sent.')
            : back()->with('mail_error', 'Email delivery is not configured yet. Contact the platform admin.');
    }

    public function restartRegistration(Request $request): RedirectResponse
    {
        $draft = $this->pendingRegistration($request);
        if ($draft && ! empty($draft['email'])) {
            Cache::store('file')->forget($this->registrationCodeCacheKey($draft['email']));
        }
        $this->forgetPendingRegistration($request);

        return redirect()->route('register');
    }

    public function registrationGithubForm(Request $request): View|RedirectResponse
    {
        $draft = $this->pendingRegistration($request);
        if (! is_array($draft)) {
            return redirect()->route('register');
        }
        if (empty($draft['email_verified'])) {
            return redirect()->route('register.verify-email');
        }

        return view('auth.register-github', ['githubProfile' => $this->pendingGithubProfile($request)]);
    }

    public function storePendingRegistration(Request $request, array $draft): void
    {
        $key = $request->session()->get('pending_registration_key') ?: Str::random(64);
        $request->session()->put('pending_registration_key', $key);
        $expiresAt = max(1, (int) ($draft['expires_at'] ?? now()->addMinutes(30)->timestamp) - now()->timestamp);
        Cache::store('file')->put('pending-registration:'.$key, $draft, $expiresAt);
    }

    public function pendingRegistration(Request $request): ?array
    {
        $key = $request->session()->get('pending_registration_key');
        if (! is_string($key) || $key === '') {
            return null;
        }

        $draft = Cache::store('file')->get('pending-registration:'.$key);
        if (! is_array($draft) || empty($draft['expires_at']) || $draft['expires_at'] <= now()->timestamp) {
            $this->forgetPendingRegistration($request);
            return null;
        }

        return $draft;
    }

    public function pendingGithubProfile(Request $request): ?array
    {
        return $this->pendingRegistration($request)['github_profile'] ?? null;
    }

    public function setPendingGithubProfile(Request $request, array $profile): bool
    {
        $draft = $this->pendingRegistration($request);
        if (! $draft) {
            return false;
        }
        $draft['github_profile'] = $profile;
        $this->storePendingRegistration($request, $draft);
        return true;
    }

    public function forgetPendingRegistration(Request $request): void
    {
        $key = $request->session()->pull('pending_registration_key');
        if (is_string($key) && $key !== '') {
            Cache::store('file')->forget('pending-registration:'.$key);
        }
    }

    public function sendRegistrationCode(string $email): bool
    {
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);
        $key = $this->registrationCodeCacheKey($email);
        $expiresAt = now()->addMinutes(self::CODE_MINUTES);
        Cache::store('file')->put($key, ['hash' => Hash::make($code), 'attempts' => 0, 'expires_at' => $expiresAt->timestamp], $expiresAt);

        $sent = app(\App\Support\SystemMailer::class)->to($email, 'Verify your email · ElimuTaifa Labs', [
            'eyebrow' => 'Email verification',
            'title' => 'Verify your email',
            'intro' => 'Use this code to verify your email and continue creating your account.',
            'code' => $code,
            'note' => 'This code expires in 10 minutes. If you did not request it, you can ignore this email.',
        ]);
        if (! $sent) {
            Cache::store('file')->forget($key);
            return false;
        }

        return true;
    }

    private function consumeRegistrationCode(string $email, string $code): bool
    {
        $key = $this->registrationCodeCacheKey($email);
        $cache = Cache::store('file');
        $record = $cache->get($key);
        if (! is_array($record) || ($record['expires_at'] ?? 0) <= now()->timestamp || ($record['attempts'] ?? self::MAX_ATTEMPTS) >= self::MAX_ATTEMPTS) {
            return false;
        }
        if (! Hash::check($code, $record['hash'])) {
            $record['attempts']++;
            $cache->put($key, $record, max(1, $record['expires_at'] - now()->timestamp));
            return false;
        }

        $cache->forget($key);
        return true;
    }

    private function registrationCodeCacheKey(string $email): string
    {
        return 'registration-email-code:'.hash('sha256', mb_strtolower($email));
    }


    public function verifyEmail(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $result = $this->consumeCode(auth()->user()->email, 'verify_email', $request->string('code')->toString());

        if (! $result) {
            return back()->withErrors(['code' => 'The code is invalid, expired, or has reached its attempt limit. Request a new one.']);
        }

        auth()->user()->forceFill(['email_verified_at' => now()])->save();

        return redirect()->intended(route('dashboard'))->with('success', 'Email verified. Your account is ready.');
    }

    public function resendVerification(): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->email_verified_at) {
            $sent = $this->sendCode($user->email, 'verify_email');
        } else {
            $sent = true;
        }

        return $sent
            ? back()->with('success', 'A new verification code has been sent.')
            : back()->withErrors(['email' => 'Email delivery is not configured yet. Please contact the platform admin.']);
    }

    public function forgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function requestResetCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = mb_strtolower($data['email']);
        $user = User::where('email', $email)->first();

        $request->session()->put('password_reset_email', $email);
        $sent = $user ? $this->sendCode($email, 'password_reset') : true;

        return redirect()->route('password.code')
            ->with('success', 'If an account exists for that email, a reset code has been sent.')
            ->with('email_delivery_configured', $sent);
    }

    public function codeForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.password-code');
    }

    public function verifyResetCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $email = $request->session()->get('password_reset_email');

        if (! $email || ! $this->consumeCode($email, 'password_reset', $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'The code is invalid, expired, or has reached its attempt limit. Request a new one.']);
        }

        $request->session()->put('password_reset_authorized_at', now()->timestamp);

        return redirect()->route('password.reset.form');
    }

    public function resendResetCode(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        $sent = true;
        if ($email && User::where('email', $email)->exists()) {
            $sent = $this->sendCode($email, 'password_reset');
        }

        return $sent
            ? back()->with('success', 'If an account exists for that email, a new code has been sent.')
            : back()->withErrors(['code' => 'Email delivery is not configured yet. Please contact the platform admin.']);
    }

    public function resetForm(Request $request): View|RedirectResponse
    {
        if (! $this->hasAuthorizedReset($request)) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if (! $this->hasAuthorizedReset($request)) {
            return redirect()->route('password.request')->withErrors(['email' => 'Your reset session expired. Request a new code.']);
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);
        $email = $request->session()->get('password_reset_email');
        $user = User::where('email', $email)->firstOrFail();
        $user->forceFill(['password' => $data['password']])->save();
        DB::table('email_security_codes')->where('email', $email)->where('purpose', 'password_reset')->delete();
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_authorized_at']);

        return redirect()->route('login')->with('success', 'Password changed. Sign in with your new password.');
    }

    public function changePasswordForm(): View
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        $request->user()->forceFill(['password' => $data['password']])->save();

        return back()->with('success', 'Your password has been changed.');
    }

    public function changeEmailForm(): View
    {
        return view('auth.change-email');
    }

    public function requestEmailChange(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255', 'unique:users,email']]);
        $newEmail = mb_strtolower($data['email']);
        if ($newEmail === mb_strtolower($request->user()->email)) {
            return back()->withErrors(['email' => 'Enter a different email address.'])->withInput();
        }

        if (! $this->sendCode($newEmail, 'change_email')) {
            return back()->withErrors(['email' => 'We could not send a verification code. Check email settings and try again.'])->withInput();
        }

        $request->session()->put('pending_email_change', $newEmail);

        return redirect()->route('email.change.verify.form')->with('success', 'A verification code was sent to your new email address.');
    }

    public function emailChangeCodeForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('pending_email_change')) {
            return redirect()->route('email.change.form');
        }

        return view('auth.verify-email-change', ['newEmail' => $request->session()->get('pending_email_change')]);
    }

    public function verifyEmailChange(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $newEmail = $request->session()->get('pending_email_change');
        if (! is_string($newEmail) || $newEmail === '') {
            return redirect()->route('email.change.form');
        }
        if (User::where('email', $newEmail)->where('id', '!=', $request->user()->id)->exists()) {
            return redirect()->route('email.change.form')->withErrors(['email' => 'That email is already in use. Enter another one.']);
        }
        if (! $this->consumeCode($newEmail, 'change_email', $request->string('code')->toString())) {
            return back()->withErrors(['code' => 'The code is invalid, expired, or has reached its attempt limit. Request a new one.']);
        }

        $oldEmail = $request->user()->email;
        $request->user()->forceFill(['email' => $newEmail, 'email_verified_at' => now()])->save();
        $request->session()->forget('pending_email_change');
        ActivityLogger::record($request, 'account.email_changed');
        app(\App\Support\SystemMailer::class)->to($oldEmail, 'Email address changed · Elimutaifa Labs', [
            'eyebrow' => 'Account security',
            'title' => 'Your account email was changed',
            'intro' => 'The email address on your Elimutaifa Labs account has been changed.',
            'details' => ['New email' => $newEmail],
            'note' => 'If you did not make this change, contact platform support immediately.',
        ]);

        return redirect()->route('email.change.form')->with('success', 'New email verified and saved.');
    }

    public function resendEmailChangeCode(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pending_email_change');
        if (! is_string($email) || $email === '') {
            return redirect()->route('email.change.form');
        }

        return $this->sendCode($email, 'change_email')
            ? back()->with('success', 'A new verification code was sent.')
            : back()->withErrors(['code' => 'We could not send the code. Try again later.']);
    }

    public function cancelEmailChange(Request $request): RedirectResponse
    {
        $email = $request->session()->pull('pending_email_change');
        if (is_string($email)) {
            DB::table('email_security_codes')->where('email', $email)->where('purpose', 'change_email')->delete();
        }

        return redirect()->route('email.change.form')->with('success', 'Email change cancelled.');
    }

    private function consumeCode(string $email, string $purpose, string $code): bool
    {
        return DB::transaction(function () use ($email, $purpose, $code) {
            $record = DB::table('email_security_codes')->where('email', $email)
                ->where('purpose', $purpose)->lockForUpdate()->first();

            if (! $record || $record->consumed_at || now()->greaterThan($record->expires_at) || $record->attempts >= self::MAX_ATTEMPTS) {
                return false;
            }

            if (! Hash::check($code, $record->code_hash)) {
                DB::table('email_security_codes')->where('id', $record->id)->increment('attempts', 1, ['updated_at' => now()]);
                return false;
            }

            DB::table('email_security_codes')->where('id', $record->id)->update(['consumed_at' => now(), 'updated_at' => now()]);
            return true;
        });
    }

    private function hasAuthorizedReset(Request $request): bool
    {
        $authorizedAt = (int) $request->session()->get('password_reset_authorized_at', 0);
        return $authorizedAt > 0 && now()->timestamp - $authorizedAt <= 900;
    }
}
