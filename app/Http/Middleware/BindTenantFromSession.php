<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the tenant from the authenticated user, on the control-plane host only.
 *
 * Card 6b043f7298f1df31d8e5ee0e. This is the session-derived half of what was
 * once a single global IdentifyAgency. It lives in the `web` group, pinned by
 * $middleware->priority() to run
 *
 *   StartSession -> EnsureNoOperatorWebSession -> BindTenantFromSession -> SetLocale
 *
 * and that position is the whole fix. Before it ran inside a GLOBAL middleware,
 * i.e. before EncryptCookies and StartSession: a browser carrying only a
 * remember-me cookie was indistinguishable from a guest there, the guard's
 * `user()` later resolved the recaller inside SetLocale's `Auth::user()`, and
 * the `users` lookup threw UnboundTenantException. App\Auth\WebGuard (which
 * refuses an agency-less operator even from a recaller) and
 * EnsureNoOperatorWebSession (which turns that refusal into a logout) are not
 * enough on their own: a legitimate agency-bound recaller still needs the
 * tenant bound before the first scoped query, which is here.
 *
 * Rules:
 *   - A tenant host already bound by ResolveTenantHost keeps its binding. This
 *     middleware does nothing there, so a session can never override the host.
 *   - On the main host, every configured guard is asked (web, applicant,
 *     employer, agent). The applicant portal logs in through its own guard, so
 *     `auth()->check()` is false for a logged-in applicant — that mistake once
 *     shipped 500s on /portal/dashboard. See bindTenantFromGuards().
 *   - Nothing bindable + a path that must work before a user exists (login,
 *     registration, guest portal pages) -> mark `tenant_unbound_allowed`.
 *   - Nothing bindable anywhere else -> the request proceeds unbound and
 *     TenantScope fails closed, which is the intended answer.
 */
class BindTenantFromSession
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never override a host-derived tenant.
        if ($request->attributes->has('tenant_agency')) {
            return $next($request);
        }

        // Tenant hosts never reach here (ResolveTenantHost binds or 404s), but
        // do not let a session decide a tenant on a host that has one.
        if (! $this->isMainHost(strtolower($request->getHost()))) {
            return $next($request);
        }

        if ($this->bindTenantFromGuards($request)) {
            return $next($request);
        }

        if ($this->isPublicPath($request)) {
            // Guest entry point: no tenant exists yet, and a guest request
            // must not throw while the route binds its models. Marked on
            // the request only — the scope reads it. See isPublicPath().
            $request->attributes->set('tenant_unbound_allowed', true);
        }

        return $next($request);
    }

    /**
     * Whether this request may reach a tenant-scoped model without any tenant
     * bound — i.e. a public entry point (login, guest portal pages) whose route
     * model binding must work before a user exists.
     *
     * Card 6b008b8dc9020955b2cc16e1: the scope now fails closed, and the guest
     * prefix runs BEFORE the authenticating middleware, so without this a guest
     * hitting /fra/line-up/{applicant} threw UnboundTenantException (500)
     * instead of redirecting to the login page. The tenant is still unbound: the
     * scope simply does not throw on these paths, and the auth middleware
     * immediately after them redirects, so nothing renders unscoped data.
     *
     * Deliberately a short explicit list of guest prefixes (plus the health
     * check). Not "everything that is not /admin": a route added tomorrow under
     * a new prefix must make someone write it here on purpose.
     *
     * The portal is listed twice on purpose. Its guest pages (register, login,
     * the OTP steps) are reachable before an applicant exists; its authenticated
     * pages (dashboard, profile, jobs) run on the control plane with no host
     * tenant to bind, so the mark keeps SubstituteBindings from throwing before
     * a guard user can be inspected. In practice the mark is only ever reached
     * when NO guard is authenticated (bindTenantFromGuards() returns first),
     * so a logged-in applicant always gets their own tenant and a guest is
     * still redirected to /portal/login by the auth middleware.
     */
    protected function isPublicPath(Request $request): bool
    {
        return $request->is(
            'login',
            'logout',
            'register',
            'password/*',
            'forgot-password',
            'fra/login',
            'fra/password/*',
            'employer/login',
            'employer/password/*',
            'sponsor/login',
            'sponsor/password/*',
            'portal',
            'portal/*',
            'applicant/login',
            'applicant/register',
            'applicant/password/*',
            // The FRA portal is reachable before login: the auth middleware
            // redirects a guest from these pages, but SubstituteBindings has
            // already touched [applicants] by then. Note the shape — these are
            // authenticated pages, not "public data".
            'fra/line-up/*',
            'fra/on-process/*',
            'fra/selected/*',
            'employer/*',
            'sponsor/*',
            'up',
        );
    }

    /**
     * Bind the authenticated user's agency, whichever guard they logged in
     * through. Returns true when an agency was bound.
     *
     * Guards come from config('auth.guards') — web, applicant, employer, agent
     * and godmode today — so a guard added later is covered without touching
     * this method. Each guard model carries agency_id (User, Applicant and
     * Agent all do). A guard whose user has no agency (the super_admin,
     * agency-less accounts) binds nothing: resolving to null must not blank a
     * tenant the host already provided, and the scope decides what an unbound
     * request means (guest mark, or throw).
     */
    protected function bindTenantFromGuards(Request $request): bool
    {
        foreach (array_keys(config('auth.guards', [])) as $guard) {
            $authGuard = auth($guard);

            if (! $authGuard->check()) {
                continue;
            }

            $agency = $authGuard->user()?->agency;

            if (! $agency) {
                continue;
            }

            $request->attributes->set('tenant_agency', $agency);
            app()->instance('tenant_agency', $agency);

            return true;
        }

        return false;
    }

    protected function isMainHost(string $host): bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP) || ! str_contains($host, '.')) {
            return true;
        }

        return in_array($host, config('tenant_domains.main_hosts', []), true);
    }
}
