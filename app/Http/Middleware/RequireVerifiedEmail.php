<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireVerifiedEmail
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->email_verified_at) {
            return redirect()->route('email.verify.notice');
        }

        return $next($request);
    }
}
