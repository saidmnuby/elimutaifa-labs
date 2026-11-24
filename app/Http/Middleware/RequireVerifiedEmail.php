<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireVerifiedEmail
{
    public function handle(Request $request, Closure $next): Response
    {
        if (PlatformSetting::enabled('email_verification_required', true) && ! $request->user()->email_verified_at) {
            return redirect()->route('email.verify.notice');
        }

        return $next($request);
    }
}
