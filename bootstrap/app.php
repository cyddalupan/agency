<?php

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
        $middleware->append(\App\Http\Middleware\IdentifyAgency::class);

        $middleware->alias([
            'employer' => \App\Http\Middleware\EnsureUserIsEmployer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            $guards = $e->guards();
            if (in_array('applicant', $guards)) {
                return redirect()->guest(route('portal.login'));
            }

            if (in_array('employer', $guards)) {
                return redirect()->guest(route('employer.login'));
            }

            return redirect()->guest(route('login'));
        });
    })->create();
