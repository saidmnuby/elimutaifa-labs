<?php

namespace App\Http\Controllers;

use App\Support\GitHubAppClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GitHubVerificationController extends Controller
{
    public function repository(Request $request, GitHubAppClient $github): JsonResponse
    {
        $validated = $request->validate([
            'repository' => ['required', 'string', 'max:190', 'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/'],
        ]);

        $installationId = (int) $request->session()->get('github_app_installation_id', 0);
        if ($installationId < 1 || ! $github->isConfigured()) {
            return response()->json(['status' => 'installation_required']);
        }

        try {
            $repositories = $github->repositories($installationId);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'unavailable'], 503);
        }

        $found = collect($repositories)->first(fn (array $repo) =>
            strcasecmp($repo['full_name'], $validated['repository']) === 0
        );

        return response()->json([
            'status' => $found ? 'found' : 'not_accessible',
            'repository' => $found['full_name'] ?? null,
            'private' => $found['private'] ?? null,
        ]);
    }
}
