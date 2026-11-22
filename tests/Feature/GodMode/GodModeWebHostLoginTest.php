<?php

namespace Tests\Feature\GodMode;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0341cf42a2071fc8b38ea5 — regression: QA found a 500 on a REAL login.
 *
 * Everything in GodModeLoginTest passed while POST /godmode/login answered
 * 500 'Tenant scope could not be applied to [users]' for an existing operator.
 * Two reasons, both closed here:
 *
 *   1. The unbound-tenant throw is a WEB behaviour; PHPUnit boots through the
 *      console kernel, where TenantScope deliberately fails open. Without
 *      TenantScope::testingAsWebRequest() these tests would be running in the
 *      console branch and would pass against the broken code — precisely the
 *      green-but-wrong failure this file exists to prevent. Every test here
 *      turns that flag OFF afterwards, because it is static and would otherwise
 *      leak into every later test in the process.
 *
 *   2. The happy-path tests only ever posted a username that EXISTS. The old
 *      code never reached SessionGuard::attempt() for an unknown username (it
 *      returns before, via the dummy-hash branch), so the throw was invisible.
 *
 * The scenarios go past "it returns 200" into the criteria the crash made
 * unreachable: dashboard, identical generic error for a real operator with a
 * wrong password, lockout, idle expiry and logout — all with the tenant scope
 * live.
 */
class GodModeWebHostLoginTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    protected function setUp(): void
    {
        parent::setUp();

        // Judge every query in this file as an HTTP request, not artisan.
        TenantScope::testingAsWebRequest();
    }

    protected function tearDown(): void
    {
        // Static state: leaving it on turns the console exemption off for every
        // test that runs after this file.
        TenantScope::testingAsWebRequest(false);

        parent::tearDown();
    }

    private function god(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => null,
            'username'  => 'landlord',
            'email'     => 'landlord@godmode.local',
            'password'  => self::PASSWORD,
            'user_type' => 'super_admin',
            'status'    => 'active',
        ], $attrs));
    }

    private function agencyAdmin(): User
    {
        return User::factory()->create([
            'agency_id' => Agency::factory()->create(['status' => 'active'])->id,
            'username'  => 'agencyadmin',
            'email'     => 'admin@agency.com',
            'password'  => self::PASSWORD,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
    }

    private function firstError(): ?string
    {
        $errors = session('errors');

        // Same two shapes the existing suite handles: a ViewErrorBag once the
        // middleware has shared it, otherwise the raw flashed array.
        if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
            return $errors->first('username') ?: null;
        }

        return $errors['default']['messages']['username'][0] ?? null;
    }

    /**
     * Evaluate $fn with NO tenant bound at all — no container instance, no
     * session id — which is the state a visitor to the main host is in.
     */
    private function withoutAnyTenant(callable $fn)
    {
        $this->app->forgetInstance('tenant_agency');

        return $fn();
    }

    // ── the bug itself ───────────────────────────────────────────────

    #[Test]
    public function a_real_operator_logs_in_on_the_main_host_with_no_tenant_bound(): void
    {
        $god = $this->god();

        $response = $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]));

        // 500 is the bug; assert the redirect explicitly so a crash can never
        // read as "some failure was expected".
        $response->assertRedirect(route('godmode.dashboard'));
        $response->assertSessionHasNoErrors();
        $this->assertTrue(auth('godmode')->check());
        $this->assertSame($god->id, auth('godmode')->id());
        $this->assertGuest('web');

        $this->assertDatabaseHas('audit_events', [
            'action'        => 'login',
            'actor_user_id' => $god->id,
        ]);
    }

    #[Test]
    public function a_real_operator_logs_in_on_the_main_host_with_a_tenant_bound(): void
    {
        // The QA environment had a tenant sitting in the container/session
        // (the main host can carry one from a restored agency session). That
        // is the harsher case: the attempt is bound to agency A while the
        // operator belongs to none, so a scoped provider query would simply
        // not find them and the login would fail generically — silently.
        $agency = Agency::factory()->create(['status' => 'active']);
        $god = $this->god();

        $this->app->instance('tenant_agency', $agency);

        $response = $this->withSession(['tenant_agency_id' => $agency->id])
            ->post(route('godmode.login.post'), [
                'username' => 'landlord',
                'password' => self::PASSWORD,
            ]);

        $response->assertRedirect(route('godmode.dashboard'));
        $this->assertSame($god->id, auth('godmode')->id());
    }

    #[Test]
    public function the_login_by_email_also_works_with_no_tenant_bound(): void
    {
        $god = $this->god();

        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord@godmode.local',
            'password' => self::PASSWORD,
        ]))->assertRedirect(route('godmode.dashboard'));

        $this->assertSame($god->id, auth('godmode')->id());
    }

    #[Test]
    public function a_real_operator_with_a_wrong_password_fails_generically_not_500(): void
    {
        $this->god();

        // Reaching the guard's attempt() WITH a real username is the path that
        // threw. A wrong password must be answered by the form, not by a crash.
        $response = $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => 'definitely-not-the-password',
        ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $response->assertStatus(302);
        $this->assertSame(
            'The provided credentials do not match our records.',
            $this->firstError(),
            'a real operator with a wrong password must get the same generic text as an unknown user'
        );
        $this->assertGuest('godmode');
    }

    #[Test]
    public function an_agency_admin_with_a_correct_password_gets_the_identical_generic_error(): void
    {
        $admin = $this->agencyAdmin();

        // A non-operator never reaches attempt(): the controller refuses on
        // user_type BEFORE the guard. That early refusal is exactly why an
        // 'admin with a valid password' shape never reproduced the live 500 —
        // so it must be pinned as a refusal in a real web context, not only
        // under the console boot where nothing can throw.
        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'admin@agency.com',
            'password' => self::PASSWORD, // the correct one
        ]));

        $this->assertStringContainsString(
            'do not match our records',
            (string) $this->firstError(),
            'a non-operator with a valid password must not be told apart from a wrong password'
        );
        $this->assertGuest('godmode');

        // Same refusal with a tenant bound: binding an agency for a god-mode
        // login must not promote that agency's admin into god mode.
        $this->app->instance('tenant_agency', $admin->agency);
        $this->withSession(['tenant_agency_id' => $admin->agency_id])
            ->post(route('godmode.login.post'), [
                'username' => 'admin@agency.com',
                'password' => self::PASSWORD,
            ]);

        $this->assertStringContainsString('do not match our records', (string) $this->firstError());
        $this->assertGuest('godmode');

        $this->app->forgetInstance('tenant_agency');
    }

    #[Test]
    public function a_suspended_operator_is_refused_here_even_on_a_live_web_request(): void
    {
        // status !== 'active' is the other pre-attempt refusal. Same question
        // in a web context: refuse, never crash.
        $this->god(['status' => 'suspended']);

        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]));

        $this->assertStringContainsString('do not match our records', (string) $this->firstError());
        $this->assertGuest('godmode');
    }

    /**
     * The session backstop, over a real session rather than actingAs().
     *
     * `actingAs()` sets the guard's user in memory, so it never exercises the
     * `retrieveById()` path nor the middleware's re-read of the row. Live does:
     * an operator is suspended while a session is open on another device, and
     * the next cold request has to see the new status.
     */
    #[Test]
    public function a_suspended_operator_loses_an_open_session_on_a_cold_request(): void
    {
        $god = $this->god();

        $this->withoutAnyTenant(fn () => $this->withSession([
            auth('godmode')->getName() => $god->id,
            'godmode.started_at'       => now(),
            'godmode.last_activity_at' => now(),
        ])->get(route('godmode.dashboard')))->assertOk();

        // Suspended while the session is open, then a fresh request.
        $god->update(['status' => 'suspended']);
        $this->app['auth']->forgetGuards();

        $this->withoutAnyTenant(fn () => $this->get(route('godmode.dashboard')))
            ->assertForbidden();

        $this->assertGuest('godmode');
    }

    // ── criteria the 500 made unreachable ────────────────────────────

    /**
     * The GET here is deliberately NOT wrapped in withoutAnyTenant().
     *
     * That wrapper only drops the container instance; the request still goes
     * through the kernel, and IdentifyAgency marks an unbound main-host path
     * with `tenant_unbound_allowed`, so the assertion says nothing about the
     * scope re-reading the operator. The cold variants below (and
     * GodModeColdSessionTest) are the ones that reach the throw; this test is
     * kept as the plain "login then dashboard" smoke check.
     */
    #[Test]
    public function after_a_real_login_the_dashboard_answers_200_on_the_main_host(): void
    {
        $this->god();

        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]))->assertRedirect(route('godmode.dashboard'));

        $this->get(route('godmode.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control');
    }

    /**
     * BUG 3, the case the suite could not see (QA re-run of card
     * 6b0341cf42a2071fc8b38ea5).
     *
     * The existing dashboard test posted inside withoutAnyTenant() and then
     * GET the dashboard OUTSIDE it, i.e. with a tenant bound — the benign
     * case. Live has NO tenant bound on the GET: the operator's agency_id is
     * NULL, so IdentifyAgency's bindTenantFromGuards() binds nothing and
     * auth:godmode's check() has to retrieve the operator with the tenant
     * scope live.
     *
     * Two reasons a plain `$this->get()` still passed against the broken
     * code, both worth knowing before trusting green here:
     *
     *   1. Within one test the guard's user is already resolved in memory, so
     *      retrieveById() is never called. Live gets a cold request per
     *      request. The session is written directly (no actingAs(), which
     *      would pin the user in memory) and the guard is dropped with
     *      forgetGuards(), so the GET must rebuild it from the session.
     *   2. Not simply "drop withoutAnyTenant from the GET" — that change also
     *      passed against the broken code, because the scope's own
     *      auth()->user() probe runs the guard stack through the console
     *      branch, which fails open under PHPUnit. Only the FORGET + GET
     *      combination reaches the throw.
     *
     * Verified red -> green: with GodModeGuard::user() not wrapped in
     * Tenant::bypass(), this test fails with the live
     * 'Tenant scope could not be applied to [users]'.
     */
    #[Test]
    public function a_cold_dashboard_request_restores_the_session_operator_without_a_tenant(): void
    {
        $god = $this->god();

        $this->withoutAnyTenant(fn () => $this->withSession([
            auth('godmode')->getName() => $god->id,
            'godmode.started_at'       => now(),
            'godmode.last_activity_at' => now(),
        ])->get(route('godmode.dashboard')))
            ->assertOk()
            ->assertSee('God Mode');

        // The user was actually retrieved, not merely 200'd by a redirect.
        $this->assertSame($god->id, auth('godmode')->id());
    }

    /**
     * The full login -> dashboard sequence with no tenant ever bound, kept
     * alongside the cold-session test above so a future change cannot fix one
     * shape and break the other.
     */
    #[Test]
    public function the_dashboard_answers_200_with_no_tenant_bound_on_get(): void
    {
        $this->god();

        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]));

        $this->app->forgetInstance('tenant_agency');

        $response = $this->get('/godmode');

        $this->assertNotSame(500, $response->status(), 'the dashboard must not 500 on an agency-less operator');
        $response->assertOk();
        $response->assertSee('God Mode');
    }

    /**
     * Login through the controller and then make the redirect target its own
     * cold request: forget the resolved guard so the GET has to rebuild it
     * from the session, which is what a second HTTP request does.
     */
    #[Test]
    public function the_dashboard_after_a_login_is_ok_when_the_request_is_cold(): void
    {
        $this->god();

        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]))->assertRedirect(route('godmode.dashboard'));

        // Dump every resolved guard: a real second request starts with none.
        $this->app['auth']->forgetGuards();

        $this->withoutAnyTenant(fn () => $this->get(route('godmode.dashboard')))
            ->assertOk();
    }

    #[Test]
    public function five_wrong_passwords_for_a_real_operator_lock_the_username(): void
    {
        $this->god();

        for ($i = 0; $i < 5; $i++) {
            $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
                'username' => 'landlord',
                'password' => 'wrong-'.$i,
            ]));
        }

        // Correct password, still locked: the lock is checked before the guard.
        $response = $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]));

        $response->assertRedirect();
        $this->assertStringContainsString('Too many login attempts', (string) $this->firstError());
        $this->assertGuest('godmode');
        $this->assertSame(0, (int) (AuditEvent::withoutGlobalScopes()
            ->where('action', 'login')->count()), 'a locked-out attempt must not audit a login');
    }

    #[Test]
    public function ten_failures_from_one_ip_trip_the_ip_limit_even_across_real_usernames(): void
    {
        $this->god();
        $this->agencyAdmin();

        // Five on the operator, five on a real non-operator: both resolve and
        // both take the password-verify path, so this exercises the real code.
        for ($i = 0; $i < 5; $i++) {
            $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
                'username' => 'landlord', 'password' => 'nope-'.$i,
            ]));
        }
        for ($i = 0; $i < 5; $i++) {
            $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
                'username' => 'agencyadmin', 'password' => 'nope-'.$i,
            ]));
        }

        // A fresh username from the same IP is refused on the IP budget.
        $response = $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'brand-new-name', 'password' => 'whatever',
        ]));

        $this->assertStringContainsString('Too many login attempts', (string) $this->firstError());

        RateLimiter::clear('godmode:ip:127.0.0.1');
    }

    #[Test]
    public function a_real_login_session_idles_out_and_logout_works(): void
    {
        $this->god();

        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]));

        // 31 idle minutes: back to the login page, not a dashboard.
        $this->travel(31)->minutes();
        $this->get(route('godmode.dashboard'))->assertRedirect(route('godmode.login'));
        $this->assertGuest('godmode');

        $this->travelBack();

        // And a fresh login can be logged out cleanly.
        $this->withoutAnyTenant(fn () => $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]));
        $this->assertTrue(auth('godmode')->check());

        $this->post(route('godmode.logout'))->assertRedirect(route('godmode.login'));
        $this->assertGuest('godmode');

        // Back after logout must not reopen the dashboard.
        $this->get(route('godmode.dashboard'))->assertRedirect(route('godmode.login'));
    }

    #[Test]
    public function a_login_with_a_tenant_bound_does_not_lease_that_tenant_to_the_god_session(): void
    {
        // Binding a tenant for a god operator would be a cross-agency hole: the
        // god account has no agency, so no tenant may end up bound to it.
        $agency = Agency::factory()->create(['status' => 'active']);
        $this->god();
        $this->app->instance('tenant_agency', $agency);

        $this->withSession(['tenant_agency_id' => $agency->id])
            ->post(route('godmode.login.post'), [
                'username' => 'landlord',
                'password' => self::PASSWORD,
            ])->assertRedirect(route('godmode.dashboard'));

        $this->assertNull(auth('godmode')->user()->agency_id);

        // Clean the bound tenant so the session created above cannot leak into
        // a later test through the container instance.
        $this->app->forgetInstance('tenant_agency');
    }
}
