<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Http\Middleware\BindTenantFromSession;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b043f7298f1df31d8e5ee0e, AC6 — the ORDERING is the fix.
 *
 * A comment saying "this runs right after StartSession" does not survive the
 * next refactor: the `web` group is priority-sorted, so a middleware with no
 * entry in $middleware->priority() lands at the END, after SubstituteBindings
 * and after SetLocale. SetLocale calls Auth::user(), and for a request that
 * carries only a remember-me cookie that is where the recaller is resolved and
 * where the `users` lookup threw. Putting BindTenantFromSession there instead
 * of pinning it is the half-fix that looks like a dead end.
 *
 * So the resolved order is asserted against the BOOTED kernel, not inferred
 * from bootstrap/app.php. If someone reorders priority() or drops the entry,
 * this fails even though the file still "looks" right.
 */
class RecallersBindTenantBeforeLocaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The resolved `web` group as the router applies it, i.e. after
     * priority sorting. Reading the group definition alone would only tell us
     * what we appended, which is exactly the assumption that was wrong.
     */
    private function resolvedWebGroup(): array
    {
        // A real request bootstraps the app through the HTTP kernel, and the
        // kernel is what pushes its groups + priority list onto this router
        // (syncMiddlewareToRouter()). A test bootstraps through the console
        // kernel and never gets that sync, so call it here: without it the
        // `web` group is not registered at all and this test would be reading
        // an empty router rather than the one production uses.
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->bootstrap();
        $router = app('router');

        // Sort the group the way Router::runRouteWithinStack() does for a
        // route that carries the `web` group: gatherGroupStack + sortMiddleware.
        $sorted = (new \Illuminate\Routing\SortedMiddleware(
            $router->middlewarePriority,
            $router->getMiddlewareGroups()['web']
        ))->all();

        $this->assertNotEmpty($sorted, 'the web group resolved empty');

        return array_values(array_map(
            static fn ($m) => is_string($m) ? $m : (is_object($m) ? $m::class : (string) $m),
            $sorted
        ));
    }

    #[Test]
    public function bind_tenant_from_session_is_in_the_web_group(): void
    {
        $this->assertContains(BindTenantFromSession::class, $this->resolvedWebGroup());
    }

    /**
     * AC6 verbatim: the binding middleware is pinned immediately after
     * StartSession (EnsureNoOperatorWebSession shares the slot, on purpose:
     * a stale operator session is dropped before we bind a tenant from it).
     */
    #[Test]
    public function bind_tenant_from_session_sits_immediately_after_start_session(): void
    {
        $group = $this->resolvedWebGroup();

        $start = array_search(StartSession::class, $group, true);
        $bind = array_search(BindTenantFromSession::class, $group, true);

        $this->assertNotFalse($start, 'StartSession is not in the web group');
        $this->assertNotFalse($bind, 'BindTenantFromSession is not in the web group');

        // Everything between StartSession and BindTenantFromSession must be the
        // operator-session gate, and nothing else.
        $between = array_slice($group, $start + 1, $bind - $start - 1);
        $this->assertSame(
            [\App\Http\Middleware\EnsureNoOperatorWebSession::class],
            $between,
            'BindTenantFromSession must directly follow StartSession '
            . '(only EnsureNoOperatorWebSession may sit between them)'
        );
        $this->assertSame($start + 2, $bind, 'exact position drifted');
    }

    /**
     * Sanity: the group under test is the real `web` group — the one the
     * stock EncryptCookies/StartSession pair comes from — not an empty or
     * unrelated list.
     */
    #[Test]
    public function the_group_under_test_is_the_real_web_group(): void
    {
        $group = $this->resolvedWebGroup();

        $this->assertContains(\Illuminate\Cookie\Middleware\EncryptCookies::class, $group);
        $this->assertContains(StartSession::class, $group);
        $this->assertContains(\Illuminate\Routing\Middleware\SubstituteBindings::class, $group);
    }

    /**
     * The point of the position: SetLocale — the first place the guard is
     * resolved for a recaller — must run AFTER the tenant is bound. Asserted
     * in both directions so a reader sees which one is the bug.
     */
    #[Test]
    public function set_locale_runs_after_the_tenant_is_bound(): void
    {
        $group = $this->resolvedWebGroup();

        $bind = array_search(BindTenantFromSession::class, $group, true);
        $locale = array_search(SetLocale::class, $group, true);

        $this->assertNotFalse($bind);
        $this->assertNotFalse($locale, 'SetLocale is not in the web group');

        $this->assertLessThan(
            $locale,
            $bind,
            'BindTenantFromSession runs after SetLocale. SetLocale calls Auth::user(), '
            . 'which resolves the remember-me recaller and throws on the users lookup — '
            . 'the original 500. This is the ordering the card is about.'
        );

        // SubstituteBindings too: it touches tenant models for route bindings.
        $substitute = array_search(\Illuminate\Routing\Middleware\SubstituteBindings::class, $group, true);
        if ($substitute !== false) {
            $this->assertLessThan($substitute, $bind, 'route-model binding ran before the tenant was bound');
        }
    }

    /**
     * The middleware also has to be reachable through the group that real
     * browser routes use — i.e. `GET /dashboard` carries it. A class in the
     * priority list but not in the group is inert.
     */
    #[Test]
    public function a_real_dashboard_route_carries_the_binding_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('dashboard') ?? null;

        $this->assertNotNull($route, 'the dashboard route is missing');
        $this->assertContains('web', $route->middleware(), 'the dashboard route is not in the web group');

        // Bootstrap first for the same reason as resolvedWebGroup(): a test
        // boots through the console kernel, and only the HTTP kernel pushes
        // the groups onto the router.
        app(\Illuminate\Contracts\Http\Kernel::class)->bootstrap();

        $resolved = app('router')->gatherRouteMiddleware($route);
        $classes = array_map(
            static fn ($m) => is_string($m) ? $m : (is_object($m) ? $m::class : (string) $m),
            $resolved
        );

        $this->assertContains(BindTenantFromSession::class, $classes);
        $this->assertLessThan(
            array_search(SetLocale::class, $classes, true),
            array_search(BindTenantFromSession::class, $classes, true),
            'on the real route, SetLocale still runs first'
        );
    }
}
