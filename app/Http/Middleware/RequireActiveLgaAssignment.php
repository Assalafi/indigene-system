<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SRD 13.3 - LGA roles must have an active assignment to proceed.
 */
class RequireActiveLgaAssignment
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isSystemAdmin() && ! $user->activeLga()) {
            auth()->guard('web')->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your LGA assignment is not active. Contact your System Administrator.',
            ]);
        }

        return $next($request);
    }
}
