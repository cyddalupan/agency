<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
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
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'employer'    => \App\Http\Middleware\EnsureUserIsEmployer::class,
            'fra'         => \App\Http\Middleware\EnsureUserIsFra::class,
            'sponsor'     => \App\Http\Middleware\EnsureUserIsSponsor::class,
            'sponsor.guest' => \App\Http\Middleware\RedirectIfSponsorAuthenticated::class,
            'role'        => \App\Http\Middleware\CheckRole::class,
            'ai.rate'     => \App\Http\Middleware\AiQueryRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            $guards = $e->guards();

            $redirectTo = match (true) {
                in_array('applicant', $guards) => route('portal.login'),
                in_array('employer', $guards) => route('employer.login'),
                Str::startsWith($request->path(), 'fra/') || $request->path() === 'fra' => route('fra.login'),
                Str::startsWith($request->path(), 'sponsor/') || $request->path() === 'sponsor' => route('sponsor.login'),
                default => route('login'),
            };

            // For POST/PUT/DELETE requests to protected routes (like /logout),
            // don't store the intended URL since POST URLs can't be meaningfully
            // redirected to after login
            if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                return redirect()->to($redirectTo);
            }

            return redirect()->guest($redirectTo);
        });
    })->create();
