<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GitHubRepositoryVerifier
{
    /** @return 'found'|'not_publicly_visible'|'unavailable' */
    public static function check(string $repository): string
    {
        [$owner, $name] = explode('/', trim($repository), 2);
        $cacheKey = 'github-repository-visibility:'.sha1(mb_strtolower($owner.'/'.$name));

        if (Cache::has($cacheKey)) {
            return (string) Cache::get($cacheKey);
        }

        try {
            $response = Http::acceptJson()
                ->withUserAgent('ElimuTaifa-Labs')
                ->timeout(8)
                ->get('https://api.github.com/repos/'.rawurlencode($owner).'/'.rawurlencode($name));
        } catch (ConnectionException) {
            return 'unavailable';
        }

        if ($response->successful()) {
            Cache::put($cacheKey, 'found', now()->addMinutes(10));

            return 'found';
        }

        if ($response->status() === 404) {
            // GitHub returns the same 404 for private and nonexistent repos.
            Cache::put($cacheKey, 'not_publicly_visible', now()->addMinutes(2));

            return 'not_publicly_visible';
        }

        return 'unavailable';
    }
}
