<?php

namespace App\Http\Controllers;

use App\Support\GitHubAppClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class GitHubAppInstallationController extends Controller
{
    public function start(Request $request, GitHubAppClient $github): RedirectResponse
    {
        $developer = $request->user();

        if (! $developer->github_verified_at || blank($developer->github_username)) {
            return redirect()->route('github.oauth.project')->with(
                'info',
                'Connect your GitHub account first, then connect a repository.'
            );
        }

        if (blank(config('services.github.app_slug')) || ! $github->isConfigured()) {
            return back()->withErrors(['repository' => 'The GitHub App is not set up. Please contact an administrator.']);
        }

        if (! $request->boolean('manage')) {
            try {
                $installationId = $github->installationForUser($developer->github_username);

                if ($installationId) {
                    $installation = $github->installation($installationId);
                    if (strcasecmp($installation['account_login'], (string) $developer->github_username) !== 0) {
                        throw new \RuntimeException('GitHub installation account does not match the verified developer.');
                    }

                    $repositories = $github->repositories($installationId);
                    if ($repositories !== []) {
                        $request->session()->put([
                            'github_app_installation_id' => $installationId,
                            'github_app_account_login' => $installation['account_login'],
                            'github_app_repositories' => array_column($repositories, 'full_name'),
                        ]);

                        return redirect()->route('projects.create')->with(
                            'success',
                            'The GitHub App is already connected. Your allowed repositories are now listed.'
                        );
                    }
                }
            } catch (Throwable $exception) {
                report($exception);

                return redirect()->route('projects.create')->withErrors([
                    'repository' => 'We could not verify the GitHub installation. Try again or contact an administrator.',
                ]);
            }
        }

        $state = Str::random(64);
        $request->session()->put('github_app_install_state', $state);

        return redirect()->away('https://github.com/apps/'.rawurlencode(config('services.github.app_slug')).'/installations/new?'.http_build_query([
            'state' => $state,
        ]));
    }

    public function installed(Request $request, GitHubAppClient $github): RedirectResponse
    {
        $expectedState = (string) $request->session()->pull('github_app_install_state', '');
        $state = (string) $request->query('state', '');
        $installationId = $request->integer('installation_id');

        if ($expectedState === '' || $state === '' || ! hash_equals($expectedState, $state)) {
            return redirect()->route('projects.create')->withErrors([
                'repository' => 'The GitHub App did not pass the security check. Try again from the project form.',
            ]);
        }

        if ($installationId < 1 || ! $github->isConfigured()) {
            return redirect()->route('projects.create')->withErrors([
                'repository' => 'The GitHub App is not fully set up yet. Please contact an administrator.',
            ]);
        }

        try {
            $installation = $github->installation($installationId);
            $developer = $request->user();

            if (strcasecmp($installation['account_type'], 'User') === 0
                && strcasecmp($installation['account_login'], (string) $developer->github_username) !== 0) {
                return redirect()->route('projects.create')->withErrors([
                    'repository' => 'You connected a different GitHub account from the one verified on your profile.',
                ]);
            }

            $repositories = $github->repositories($installationId);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('projects.create')->withErrors([
                'repository' => 'The GitHub App could not verify the installation. Check that the App is installed on the right account and try again.',
            ]);
        }

        if ($repositories === []) {
            return redirect()->route('projects.create')->withErrors([
                'repository' => 'No repository was selected when you connected the GitHub App.',
            ]);
        }

        $request->session()->put([
            'github_app_installation_id' => $installationId,
            'github_app_account_login' => $installation['account_login'],
            'github_app_repositories' => array_column($repositories, 'full_name'),
        ]);

        return redirect()->route('projects.create')->with(
            'success',
            'The GitHub App is connected. The selected repositories can now be checked.'
        );
    }
}
