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
        \App\Providers\ReportingServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Card 6b043f7298f1df31d8e5ee0e: IdentifyAgency is host-only now. Its
        // session-derived half is BindTenantFromSession in the `web` group
        // below — a global middleware runs before EncryptCookies/StartSession
        // and therefore before any remember-me recaller exists, which is how
        // every agency user returning with "keep me signed in" got a 500 on
        // the `users` lookup.
        $middleware->append(\App\Http\Middleware\IdentifyAgency::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Card 6b0420a205f32d19776021d4: an agency-less super_admin must never
        // hold a `web` session — there is no tenant to serve them and the first
        // scoped query throws [users].
        //
        // Two gates, and the order is the whole fix:
        //   1. WebGuard (config/auth.php `web.guard`) refuses to restore a
        //      session or a remember-me recaller for an agency-less user. That
        //      is the earlier bug's blind spot: a recaller is resolved by
        //      SessionGuard::user() inside SetLocale, AFTER this pipeline, and
        //      the users lookup there threw before any middleware could look.
        //   2. EnsureNoOperatorWebSession runs inside the `web` group, RIGHT
        //      AFTER StartSession (see $middleware->priority() below), so the
        //      guard is fully resolved — session or recaller — when it looks.
        //      It then drops the session, the recaller cookie and (once) the
        //      stored remember_token, and redirects to the god mode door.
        // It used to be a GLOBAL append, which ran before StartSession: with an
        // empty sessions table it always saw "guest" and never fired.
        // Card 6b043f7298f1df31d8e5ee0e: the order inside `web` is the fix, and
        // it is priority-sorted — appending alone would drop BindTenantFromSession
        // after SetLocale, whose Auth::user() is the very recaller resolution
        // that throws. So StartSession -> EnsureNoOperatorWebSession ->
        // BindTenantFromSession -> SetLocale, pinned below and asserted in
        // tests/Feature/Tenant/RecallersBindTenantBeforeLocaleTest.php.
        //
        // EnsureNoOperatorWebSession first, on purpose: an operator's stale
        // session/recaller is dropped BEFORE we try to bind a tenant from it.
        $middleware->web(append: [
            \App\Http\Middleware\EnsureNoOperatorWebSession::class,
            \App\Http\Middleware\BindTenantFromSession::class,
        ]);
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \App\Http\Middleware\EnsureNoOperatorWebSession::class,
            \App\Http\Middleware\BindTenantFromSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
        \Illuminate\Auth\Middleware\Authenticate::redirectUsing(
            [\App\Http\Middleware\EnsureNoOperatorWebSession::class, 'redirectTo']
        );
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'employer'    => \App\Http\Middleware\EnsureUserIsEmployer::class,
            'fra'         => \App\Http\Middleware\EnsureUserIsFra::class,
            'sponsor'     => \App\Http\Middleware\EnsureUserIsSponsor::class,
            'sponsor.guest' => \App\Http\Middleware\RedirectIfSponsorAuthenticated::class,
            'role'        => \App\Http\Middleware\CheckRole::class,
            'module.access' => \App\Http\Middleware\EnsureModuleAccess::class,
            'agent'       => \App\Http\Middleware\EnsureUserIsAgent::class,
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
                // Card 6b0341cf42a2071fc8b38ea5: an unauthenticated god request
                // goes to its own login, never to the agency one.
                in_array('godmode', $guards) => route('godmode.login'),
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
