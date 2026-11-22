<?php

namespace App\Models\Scopes;

use App\Exceptions\UnboundTenantException;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Applies the `agency_id = <current agency>` filter to every model using the
 * HasTenant trait (56 models).
 *
 * Card 6b008b8dc9020955b2cc16e1 — the scope now FAILS CLOSED.
 *
 * Agency resolution, in order:
 *   1. super_admin logged in          -> no filter (kept deliberately, see :24).
 *   2. Tenant::bypass() in progress   -> no filter, one audit_events row.
 *   3. container `tenant_agency`      -> filter by that agency.
 *   4. session `tenant_agency_id`     -> filter by that agency (main domain).
 *   5. console / queue, unbound       -> no filter. Rationale: an artisan
 *      command, a scheduler run or a queue worker has no request host to bind
 *      (the request Laravel synthesises from APP_URL does not name a tenant);
 *      throwing here would break every seeder and command with no way to opt
 *      in per-call. A console is a trusted operator context. Use
 *      Tenant::bypass() there when you want the access to be recorded in
 *      audit_events. The test is runningInConsole(); only under the test
 *      suite is it narrowed to the console's own synthetic request.
 *   6. web request (anything else)    -> THROW UnboundTenantException.
 *      An unrecognised subdomain or unlisted custom domain reaches here, and
 *      must never be served one unfiltered cross-agency result.
 *
 * These are the ONLY legitimate unbound HTTP paths, and IdentifyAgency lets
 * them through before this scope can throw: /health, login/logout/register
 * (main domain), and the super_admin panel. Everything else 404s at the
 * middleware before reaching a controller.
 */
class TenantScope implements Scope
{
    /**
     * Prevents infinite recursion when auth()->check() triggers a User query
     * that re-applies this scope.
     */
    private static bool $resolvingAuth = false;

    /**
     * Set by a test that installs a web request into the container, to say
     * "judge this as a web request". PHPUnit boots through the console kernel,
     * so runningInConsole() is true for every test and the console branch would
     * otherwise swallow the web paths the tests exist to check.
     *
     * Deliberately NOT inferred from the request object: recreating the
     * console's synthetic request to compare against does not reproduce its
     * server array, so any heuristic silently misclassifies and the exemption
     * stops firing. An explicit flag is checkable and cannot drift.
     */
    public static function testingAsWebRequest(bool $web = true): void
    {
        self::$testingWebRequest = $web;
    }

    private static bool $testingWebRequest = false;

    public function apply(Builder $builder, Model $model): void
    {
        // Super admin bypass — skip tenant filtering when logged in as super_admin
        if (! self::$resolvingAuth) {
            self::$resolvingAuth = true;
            try {
                $user = auth()->user();
                if ($user && $user->user_type === 'super_admin') {
                    return;
                }
            } finally {
                self::$resolvingAuth = false;
            }
        }

        // Explicit, audited escape hatch (see App\Support\Tenant).
        if (Tenant::isBypassing()) {
            return;
        }

        // Check container-bound agency (from middleware or controller)
        $agency = app()->has('tenant_agency') ? app('tenant_agency') : null;

        if ($agency) {
            $builder->where($model->getTable() . '.agency_id', $agency->id);
            return;
        }

        // Fallback: session-bound agency (when logged in on main domain)
        $agencyId = session('tenant_agency_id');
        if ($agencyId) {
            $builder->where($model->getTable() . '.agency_id', $agencyId);
            return;
        }

        // Console / queue workers have no request host to bind a tenant from.
        // Trusted operator context — fail open, documented above. An HTTP
        // request never reaches here unbound: IdentifyAgency binds a tenant or
        // 404s, so the only way in is a code path that forgot to resolve one.
        //
        // Console trust is runningInConsole() — Laravel's SetRequestForConsole
        // installs a synthetic request built from APP_URL in every console
        // boot, so checking bound('request') is useless (it is always true
        // under artisan, and the old `! bound('request')` guard never fired).
        // The one exception is a test that installs a web request and says so
        // via testingAsWebRequest(): PHPUnit boots through the console kernel
        // for every test, and those tests must still reach the guest mark or
        // the throw. Real HTTP never sets that flag.
        if (app()->runningInConsole() && ! self::$testingWebRequest) {
            return;
        }

        // Guest entry point (login, public portal page): IdentifyAgency marked
        // the request so route-model binding can run before a tenant exists.
        // The authenticating middleware right behind it redirects, so nothing is
        // rendered unscoped — see IdentifyAgency::isPublicPath().
        if (request()?->attributes->get('tenant_unbound_allowed')) {
            return;
        }

        // Web request with no resolvable agency: fail closed.
        throw UnboundTenantException::forHost(request()?->getHost(), $model->getTable());
    }
}
