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
    ->withProviders([
        \App\Providers\EventServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\IdentifyAgency::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'employer' => \App\Http\Middleware\EnsureUserIsEmployer::class,
            'role'     => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            $guards = $e->guards();

            // For POST/PUT/DELETE requests to protected routes (like /logout),
            // don't store the intended URL since POST URLs can't be meaningfully
            // redirected to after login
            if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                if (in_array('applicant', $guards)) {
                    return redirect()->route('portal.login');
                }
                if (in_array('employer', $guards)) {
                    return redirect()->route('employer.login');
                }
                return redirect()->route('login');
            }

            if (in_array('applicant', $guards)) {
                return redirect()->guest(route('portal.login'));
            }

            if (in_array('employer', $guards)) {
                return redirect()->guest(route('employer.login'));
            }

            return redirect()->guest(route('login'));
        });
    })->create();
