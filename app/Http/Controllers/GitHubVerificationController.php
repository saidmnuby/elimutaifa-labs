<?php

namespace App\Http\Controllers;

use App\Support\GitHubRepositoryVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GitHubVerificationController extends Controller
{
    public function repository(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'repository' => ['required', 'string', 'max:190', 'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/'],
        ]);

        return response()->json([
            'status' => GitHubRepositoryVerifier::check($validated['repository']),
        ]);
    }
}
