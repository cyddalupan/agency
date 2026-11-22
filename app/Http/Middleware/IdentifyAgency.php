<?php

namespace App\Http\Middleware;

use App\Models\Agency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the tenant from the request host and binds it as `tenant_agency`.
 *
 * Card 6b008b8dc9020955b2cc16e1: an unknown agency host no longer falls
 * through to an unscoped request. It 404s here.
 *
 * Resolution rules, in order:
 *   1. Host is listed in tenant_domains.main_hosts (the app itself, www, an
 *      IP, localhost) — the control plane. Login, logout, register, the
 *      super_admin panel and /health legitimately run unbound here. When a
 *      user is already authenticated (session restored without a fresh
 *      login) their agency is bound from the user record before passing
 *      through; otherwise the request passes through unbound.
 *
 *      "Authenticated" means EVERY configured guard, not the default `web`
 *      one. The applicant portal logs in through the separate `applicant`
 *      guard, so auth()->check() is false for a logged-in applicant — that
 *      mistake shipped 500s on /portal/dashboard, /portal/profile and
 *      /portal/jobs. See bindTenantFromGuards().
 *   2. Host is in tenant_domains.custom_domains — bind that slug.
 *   3. Otherwise the first label is the agency slug (<slug>.fixitautoservices.com)
 *      — bind it when it is an active agency.
 *   4. Tenant host that resolves to NO active agency — 404. Serving it
 *      unbound would throw in TenantScope (fail closed), so the honest answer
 *      is that this address does not exist.
 */
class IdentifyAgency
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        // Card 6b0341cf42a2071fc8b38ea5: god mode exists on the main host only.
        // Refused BEFORE the tenant lookup, so a tenant-shaped host never binds
        // an agency for a god-mode request and no agency session cookie is ever
        // issued on a host an agency controls. 404 rather than 403: on an
        // agency's own subdomain this path simply does not exist.
        if ($request->is('godmode', 'godmode/*') && ! $this->isMainHost($host)) {
            abort(404);
        }

        // Control plane: no tenant to resolve from the host.
        if ($this->isMainHost($host)) {
            // A logged-in user on the control plane still needs their tenant
            // bound: the session may be restored (remember-me, long-lived
            // session) without going through AuthController::login().
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

        $customDomains = config('tenant_domains.custom_domains', []);
        $subdomain = $customDomains[$host] ?? explode('.', $host)[0];

        $agency = Agency::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        if ($agency) {
            $request->attributes->set('tenant_agency', $agency);
            app()->instance('tenant_agency', $agency);

            return $next($request);
        }

        // A tenant-shaped host that maps to no active agency. Do not serve it
        // unscoped: TenantScope would throw mid-request anyway. 404 instead.
        abort(404, 'No agency is configured for this address.');
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
     * Guards come from config('auth.guards') — web, applicant, employer and
     * agent today — so a guard added later is covered without touching this
     * method. Each guard model carries agency_id (User, Applicant and Agent
     * all do). A guard whose user has no agency (the super_admin, agency-less
     * accounts) binds nothing: resolving to null must not blank a tenant the
     * host already provided, and the scope decides what an unbound request
     * means (guest mark, or throw).
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
