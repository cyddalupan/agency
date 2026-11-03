<?php

namespace App\Http\Middleware;

use App\Models\Agency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyAgency
{
    /**
     * These subdomains are the control panel — skip tenant resolution.
     */
    protected array $systemDomains = ['agency', 'www', 'classapparelph', 'localhost'];

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? null;

        // Custom (full) domain -> agency slug, e.g. gulfcsdi.com -> gulf
        $customDomains = config('tenant_domains', []);
        if (isset($customDomains[$host])) {
            $subdomain = $customDomains[$host];
        }

        // Skip tenant resolution for system and unknown domains (test/local)
        if (!$subdomain || in_array($subdomain, $this->systemDomains)) {
            return $next($request);
        }

        $agency = Agency::where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        if ($agency) {
            $request->attributes->set('tenant_agency', $agency);
            app()->instance('tenant_agency', $agency);
        }

        // No agency match is fine — continue without tenant scope
        return $next($request);
    }
}
