<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RequireActiveLgaAssignment;
use App\Http\Middleware\RequirePasswordChange;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'user.active' => EnsureUserIsActive::class,
            'lga.assignment' => RequireActiveLgaAssignment::class,
            'password.change' => RequirePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $status = $e->getStatusCode();

            if ($status === 403 && $request->expectsJson()) {
                return response()->json(['message' => 'You are not authorised to perform this action.'], 403);
            }

            if ($status === 404) {
                return response()->view('errors.404', [], 404);
            }

            if ($status === 403) {
                return response()->view('errors.403', [], 403);
            }
        });
    })->create();
