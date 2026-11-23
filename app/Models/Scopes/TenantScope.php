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

    /**
     * Nesting depth for disableFor(). See that method.
     */
    private static int $disabledDepth = 0;

    /**
     * Run $fn with this scope off for the statements it makes.
     *
     * Card 6b043f7298f1df31d8e5ee0e. App\Auth\WebGuard must look the account
     * up before any tenant exists (a recaller on the main host), and a
     * Tenant::bypass() around that read cannot work: the bypass is only
     * consulted by tenantFilter(), whose first step is `auth()->user()` — the
     * very lookup being made, with the guard's user still unset. Each level
     * opened a fresh bypass and re-entered, until the process died.
     *
     * Disabling the scope at the source removes the question instead of
     * answering it: the lookup never consults the tenant, so it can never call
     * back into the guard. Deliberately the narrowest possible tool — one
     * callable, statements made inside it only, no container or session
     * effect — for reads that are about the ACTOR rather than the tenant
     * (which account does this session id / recaller name?).
     *
     * Not a replacement for Tenant::bypass() in application code: that one is
     * audited and refuses to be silent. A guard lookup is the guard's own
     * plumbing, runs on every request carrying a session, and an audit row per
     * page load would be noise, not oversight.
     *
     * @template T
     * @param  callable():T  $fn
     * @return T
     */
    public static function disableFor(callable $fn)
    {
        self::$disabledDepth++;

        try {
            return $fn();
        } finally {
            self::$disabledDepth--;
        }
    }

    public function apply(Builder $builder, Model $model): void
    {
        $agencyId = self::tenantFilter($model->getTable());

        if ($agencyId !== null) {
            $builder->where($model->getTable() . '.agency_id', $agencyId);
        }
    }

    /**
     * Which agency, if any, is bound for this statement.
     *
     * Returns the agency id to filter by, or null for "no filter" (super admin,
     * an explicit audited bypass, console/queue, or an allowed guest path). A web
     * request with nothing resolvable throws UnboundTenantException.
     *
     * Extracted verbatim from apply() for card 6b008b8e7c2a220f1cd6d2e1. The
     * `expense_request_items` scope has no `agency_id` of its own and filters
     * through the parent request, but it must still answer "who is the tenant?"
     * the same way every tenant model does — same order, same exceptions, same
     * bypass. It therefore calls this, rather than re-implementing the order and
     * drifting. apply() is now the only consumer that adds a WHERE, so there is
     * one place that decides, and all statements follow it.
     */
    public static function tenantFilter(string $table): ?int
    {
        // A deliberate, narrowly scoped actor read (see disableFor()).
        if (self::$disabledDepth > 0) {
            return null;
        }

        // Super admin bypass — skip tenant filtering when logged in as super_admin.
        //
        // Card 6b043f7298f1df31d8e5ee0e: asked of EVERY guard, not the default
        // one. WebGuard wraps two provider lookups in disableFor(), and a
        // statement made inside them used to ask this same method whether the
        // actor is a super_admin: the default-guard question came back "no"
        // (the guard's user is not resolved yet), the scope fell through to
        // "console or not?" — a test IS a console — and the query ran
        // UNSCOPED even after the guard had every reason to refuse it.
        // Answering identically from every guard removes that second path out
        // of the scope and makes the actor test independent of which guard the
        // statement happens to run for.
        if (! self::$resolvingAuth) {
            self::$resolvingAuth = true;
            try {
                foreach (array_keys(config('auth.guards', [])) as $guardName) {
                    $user = auth($guardName)->user();

                    if ($user && ($user->user_type ?? null) === 'super_admin') {
                        return null;
                    }
                }
            } finally {
                self::$resolvingAuth = false;
            }
        }

        // Explicit, audited escape hatch (see App\Support\Tenant).
        if (Tenant::isBypassing()) {
            return null;
        }

        // Check container-bound agency (from middleware or controller)
        $agency = app()->has('tenant_agency') ? app('tenant_agency') : null;

        if ($agency) {
            return (int) $agency->id;
        }

        // Fallback: session-bound agency (when logged in on main domain)
        $agencyId = session('tenant_agency_id');
        if ($agencyId) {
            return (int) $agencyId;
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
            return null;
        }

        // Guest entry point (login, public portal page): IdentifyAgency marked
        // the request so route-model binding can run before a tenant exists.
        // The authenticating middleware right behind it redirects, so nothing is
        // rendered unscoped — see IdentifyAgency::isPublicPath().
        if (request()?->attributes->get('tenant_unbound_allowed')) {
            return null;
        }

        // Web request with no resolvable agency: fail closed.
        throw UnboundTenantException::forHost(request()?->getHost(), $table);
    }
}
