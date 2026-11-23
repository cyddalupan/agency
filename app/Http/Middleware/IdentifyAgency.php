<?php

namespace App\Http\Middleware;

use App\Models\Agency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the tenant from the request host and binds it as `tenant_agency`.
 *
 * Card 6b043f7298f1df31d8e5ee0e: split in two. This class is now the GLOBAL,
 * host-only half (filename and class name kept unchanged so the split touches
 * no registration); the session-derived half — binding a control-plane user's
 * agency from their own login — moved to BindTenantFromSession inside the `web`
 * group, because it cannot work before StartSession exists.
 *
 * Why the split: `bindTenantFromGuards()` loops every guard and calls
 * `user()?->agency`. A remember-me cookie is resolved there, and the `users`
 * lookup throws TenantScope's UnboundTenantException — before EncryptCookies
 * and StartSession have even run, while it was a global middleware. A global
 * middleware is structurally incapable of session work.
 *
 * Card 6b008b8dc9020955b2cc16e1: an unknown agency host no longer falls
 * through to an unscoped request. It 404s here.
 *
 * Resolution rules, in order:
 *   1. Host is listed in tenant_domains.main_hosts (the app itself, www, an
 *      IP, localhost) — the control plane. Nothing is bound from the host:
 *      the tenant, if any, comes from the user (BindTenantFromSession). See
 *      that class for the guest mark.
 *   2. Host is in tenant_domains.custom_domains — bind that slug.
 *   3. Otherwise the first label is the agency slug (<slug>.fixitautoservices.com)
 *      — bind it when it is an active agency.
 *   4. Tenant host that resolves to NO active agency — 404. Serving it
 *      unbound would throw in TenantScope (fail closed), so the honest answer
 *      is that this address does not exist.
 *
 * A host-derived binding is final for the request: BindTenantFromSession only
 * fills in on the main host, so it can never override this one.
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

        // Control plane: no tenant to resolve from the host. The global stack
        // deliberately binds nothing here; see BindTenantFromSession.
        if ($this->isMainHost($host)) {
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

    protected function isMainHost(string $host): bool
    {
        if (filter_var($host, FILTER_VALIDATE_IP) || ! str_contains($host, '.')) {
            return true;
        }

        return in_array($host, config('tenant_domains.main_hosts', []), true);
    }
}
