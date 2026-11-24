<?php

namespace App\Http\Controllers;

use App\Support\GitHubAppClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class GitHubAppInstallationController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $developer = $request->user();

        if (! $developer->github_verified_at || blank($developer->github_username)) {
            return redirect()->route('github.oauth.project')->with(
                'info',
                'Kwanza unganisha akaunti yako ya GitHub, kisha uunganishe repository.'
            );
        }

        if (blank(config('services.github.app_slug'))) {
            return back()->withErrors(['repository' => 'GitHub App haijasanidiwa. Wasiliana na admin.']);
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
                'repository' => 'GitHub App installation haikupita security check. Jaribu tena kutoka kwenye project form.',
            ]);
        }

        if ($installationId < 1 || ! $github->isConfigured()) {
            return redirect()->route('projects.create')->withErrors([
                'repository' => 'GitHub App bado haijasanidiwa kikamilifu na admin.',
            ]);
        }

        try {
            $installation = $github->installation($installationId);
            $developer = $request->user();

            if (strcasecmp($installation['account_type'], 'User') === 0
                && strcasecmp($installation['account_login'], (string) $developer->github_username) !== 0) {
                return redirect()->route('projects.create')->withErrors([
                    'repository' => 'Umeunganisha GitHub account tofauti na ile iliyothibitishwa kwenye profile yako.',
                ]);
            }

            $repositories = $github->repositories($installationId);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('projects.create')->withErrors([
                'repository' => 'GitHub App haikuweza kuthibitisha installation. Hakikisha App imewekwa kwenye account sahihi na ujaribu tena.',
            ]);
        }

        if ($repositories === []) {
            return redirect()->route('projects.create')->withErrors([
                'repository' => 'Hakuna repository iliyochaguliwa wakati wa kuunganisha GitHub App.',
            ]);
        }

        $request->session()->put([
            'github_app_installation_id' => $installationId,
            'github_app_account_login' => $installation['account_login'],
            'github_app_repositories' => array_column($repositories, 'full_name'),
        ]);

        return redirect()->route('projects.create')->with(
            'success',
            'GitHub App imeunganishwa. Repo zilizochaguliwa sasa zinaweza kuthibitishwa.'
        );
    }
}
