<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubAppClient
{
    public function isConfigured(): bool
    {
        $path = config('services.github.app_private_key_path');
        $realKeyPath = is_string($path) && $path !== '' ? realpath($path) : false;
        $realPublicPath = realpath(public_path());

        return filled(config('services.github.app_id'))
            && $realKeyPath !== false
            && is_readable($realKeyPath)
            && $realPublicPath !== false
            && ! str_starts_with(
                $realKeyPath,
                rtrim($realPublicPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            );
    }

    /** @return array{account_login: string, account_type: string} */
    public function installation(int $installationId): array
    {
        $response = Http::acceptJson()
            ->withToken($this->jwt())
            ->withUserAgent('ElimuTaifa-Labs')
            ->timeout(12)
            ->get("https://api.github.com/app/installations/{$installationId}");

        if (! $response->successful()) {
            throw new RuntimeException('GitHub installation could not be verified.');
        }

        $login = $response->json('account.login');
        $type = $response->json('account.type');

        if (! is_string($login) || ! is_string($type)) {
            throw new RuntimeException('GitHub returned an invalid installation account.');
        }

        return ['account_login' => $login, 'account_type' => $type];
    }

    public function installationForUser(string $username): ?int
    {
        $response = Http::acceptJson()
            ->withToken($this->jwt())
            ->withUserAgent('ElimuTaifa-Labs')
            ->timeout(12)
            ->get('https://api.github.com/users/'.rawurlencode($username).'/installation');

        if ($response->status() === 404) {
            return null;
        }

        $installationId = $response->json('id');
        if (! $response->successful() || ! is_int($installationId) || $installationId < 1) {
            throw new RuntimeException('GitHub could not find this account installation.');
        }

        return $installationId;
    }

    /** @return list<array{id: int, full_name: string, private: bool}> */
    public function repositories(int $installationId): array
    {
        $tokenResponse = Http::acceptJson()
            ->withToken($this->jwt())
            ->withUserAgent('ElimuTaifa-Labs')
            ->timeout(12)
            ->post("https://api.github.com/app/installations/{$installationId}/access_tokens", [
                'permissions' => ['contents' => 'read'],
            ]);

        $token = $tokenResponse->json('token');
        if (! $tokenResponse->successful() || ! is_string($token) || $token === '') {
            throw new RuntimeException('GitHub could not issue a read-only installation token.');
        }

        $repositories = [];
        for ($page = 1; $page <= 20; $page++) {
            $response = Http::acceptJson()
                ->withToken($token)
                ->withUserAgent('ElimuTaifa-Labs')
                ->timeout(12)
                ->get('https://api.github.com/installation/repositories', [
                    'per_page' => 100,
                    'page' => $page,
                ]);

            if (! $response->successful() || ! is_array($response->json('repositories'))) {
                throw new RuntimeException('GitHub repositories could not be checked.');
            }

            $items = $response->json('repositories');
            foreach ($items as $item) {
                if (is_array($item)
                    && is_int($item['id'] ?? null)
                    && is_string($item['full_name'] ?? null)) {
                    $repositories[] = [
                        'id' => $item['id'],
                        'full_name' => $item['full_name'],
                        'private' => (bool) ($item['private'] ?? false),
                    ];
                }
            }

            if (count($items) < 100) {
                return $repositories;
            }
        }

        throw new RuntimeException('GitHub returned too many repositories to verify safely.');
    }

    private function jwt(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('GitHub App credentials are not configured.');
        }

        $keyPath = (string) config('services.github.app_private_key_path');
        $privateKey = @file_get_contents($keyPath);
        if (! is_string($privateKey) || $privateKey === '') {
            throw new RuntimeException('GitHub App private key could not be read.');
        }

        $now = time();
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64Url(json_encode([
            'iat' => $now - 60,
            'exp' => $now + 540,
            'iss' => (string) config('services.github.app_id'),
        ], JSON_THROW_ON_ERROR));
        $message = $header.'.'.$payload;

        $key = openssl_pkey_get_private($privateKey);
        if ($key === false || ! openssl_sign($message, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('GitHub App private key is invalid.');
        }

        return $message.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
