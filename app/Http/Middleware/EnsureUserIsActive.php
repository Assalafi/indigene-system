<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            auth()->guard('web')->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'This account is not active. Contact your System Administrator.',
            ]);
        }

        return $next($request);
    }
}
