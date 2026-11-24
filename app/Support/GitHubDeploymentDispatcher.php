<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubDeploymentDispatcher
{
    public function dispatch(array $inputs): void
    {
        $token = config('services.github.deploy_dispatch_token');
        $repository = (string) config('services.github.deploy_repository');
        $workflow = (string) config('services.github.deploy_workflow');
        $ref = (string) config('services.github.deploy_ref');

        if (! is_string($token) || $token === ''
            || ! preg_match('/\A[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+\z/', $repository)
            || ! preg_match('/\A[A-Za-z0-9_.-]+\z/', $workflow)
            || ! preg_match('/\A[A-Za-z0-9._\/-]+\z/', $ref)) {
            throw new RuntimeException('GitHub deployment dispatch is not configured correctly.');
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(15)
            ->post("https://api.github.com/repos/{$repository}/actions/workflows/{$workflow}/dispatches", [
                'ref' => $ref,
                'inputs' => $inputs,
            ]);

        if ($response->status() !== 204) {
            report(new RuntimeException('GitHub rejected the approved deployment dispatch (HTTP '.$response->status().').'));
            throw new RuntimeException('GitHub did not accept the deployment request.');
        }
    }
}
