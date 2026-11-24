<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GitHubOAuthController extends Controller
{
    public function connectForRegistration(Request $request): RedirectResponse
    {
        return $this->start($request, 'register');
    }

    public function connectForProject(Request $request): RedirectResponse
    {
        return $this->start($request, 'project');
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('github_oauth_state', '');
        $state = (string) $request->query('state', '');
        $intent = (string) $request->session()->pull('github_oauth_intent', 'register');
        $codeVerifier = (string) $request->session()->pull('github_oauth_code_verifier', '');
        $destination = $this->destination($intent);

        if ($expectedState === '' || $state === '' || ! hash_equals($expectedState, $state)) {
            return redirect($destination)->withErrors([
                'github_username' => 'GitHub verification did not pass its security check. Please try again.',
            ]);
        }

        if ($request->filled('error')) {
            return redirect($destination)->withErrors([
                'github_username' => 'GitHub verification was cancelled. You can try again or leave the username blank.',
            ]);
        }

        if (! $request->filled('code') || $codeVerifier === '') {
            return redirect($destination)->withErrors([
                'github_username' => 'GitHub did not return a verification code. Please try again.',
            ]);
        }

        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->timeout(12)
                ->post('https://github.com/login/oauth/access_token', [
                    'client_id' => config('services.github.client_id'),
                    'client_secret' => config('services.github.client_secret'),
                    'code' => $request->string('code')->toString(),
                    'redirect_uri' => config('services.github.redirect_uri'),
                    'code_verifier' => $codeVerifier,
                ]);

            $accessToken = $tokenResponse->json('access_token');
            if (! $tokenResponse->successful() || ! is_string($accessToken) || $accessToken === '') {
                return redirect($destination)->withErrors([
                    'github_username' => 'GitHub could not complete verification. Please try again.',
                ]);
            }

            $profileResponse = Http::acceptJson()
                ->withToken($accessToken)
                ->withUserAgent('ElimuTaifa-Labs')
                ->timeout(12)
                ->get('https://api.github.com/user');
        } catch (ConnectionException) {
            return redirect($destination)->withErrors([
                'github_username' => 'GitHub could not be reached. Please try again shortly.',
            ]);
        }

        $githubId = $profileResponse->json('id');
        $githubUsername = $profileResponse->json('login');

        if (! $profileResponse->successful()
            || (! is_int($githubId) && ! (is_string($githubId) && ctype_digit($githubId)))
            || ! is_string($githubUsername)
            || ! preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/', $githubUsername)) {
            return redirect($destination)->withErrors([
                'github_username' => 'GitHub returned an account profile we could not verify. Please try again.',
            ]);
        }

        $githubId = (string) $githubId;
        $currentUser = $request->user();

        if ($currentUser && $currentUser->github_id && (string) $currentUser->github_id !== $githubId) {
            return redirect($destination)->withErrors([
                'github_username' => 'This platform account is already linked to a different GitHub account.',
            ]);
        }

        $claimedByAnotherUser = User::query()
            ->where(function ($query) use ($githubId, $githubUsername) {
                $query->where('github_id', $githubId)
                    ->orWhereRaw('LOWER(github_username) = ?', [mb_strtolower($githubUsername)]);
            })
            ->when($currentUser, fn ($query) => $query->where('id', '!=', $currentUser->id))
            ->exists();

        if ($claimedByAnotherUser) {
            return redirect($destination)->withErrors([
                'github_username' => 'This GitHub account is already linked to another TechwareTz Control Center (TCC) account. Sign in to that account to use it.',
            ]);
        }

        if ($currentUser) {
            $currentUser->forceFill([
                'github_id' => $githubId,
                'github_username' => $githubUsername,
                'github_verified_at' => now(),
            ])->save();

            return redirect($destination)->with('success', 'GitHub account ownership verified: @'.$githubUsername.'.');
        }

        $githubProfile = [
            'id' => $githubId,
            'login' => $githubUsername,
        ];
        if (! app(EmailSecurityController::class)->setPendingGithubProfile($request, $githubProfile)) {
            return redirect()->route('register')->withErrors([
                'email' => 'Your registration session expired. Please start again.',
            ]);
        }

        return redirect($destination)->with('success', 'GitHub username verified.');
    }

    private function start(Request $request, string $intent): RedirectResponse
    {
        $clientId = config('services.github.client_id');
        $clientSecret = config('services.github.client_secret');
        $redirectUri = config('services.github.redirect_uri');

        if (! is_string($clientId) || $clientId === ''
            || ! is_string($clientSecret) || $clientSecret === ''
            || ! is_string($redirectUri) || $redirectUri === ''
            || filter_var($redirectUri, FILTER_VALIDATE_URL) === false
            || (! str_starts_with($redirectUri, 'https://') && ! app()->environment('local'))) {
            return back()->withErrors([
                'github_username' => 'GitHub ownership verification is not configured yet. Ask the platform admin to set it up.',
            ]);
        }

        $state = Str::random(64);
        $codeVerifier = Str::random(96);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $request->session()->put([
            'github_oauth_state' => $state,
            'github_oauth_code_verifier' => $codeVerifier,
            'github_oauth_intent' => $intent,
        ]);

        return redirect()->away('https://github.com/login/oauth/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'read:user',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]));
    }

    private function destination(string $intent): string
    {
        return $intent === 'project' ? route('projects.create') : route('register.github');
    }
}
