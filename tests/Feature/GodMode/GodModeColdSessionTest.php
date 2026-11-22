<?php

namespace Tests\Feature\GodMode;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0341cf42a2071fc8b38ea5, QA re-run BUG 3 (live 500 on GET /godmode).
 *
 * The live shape, stated once so the tests below can be read against it:
 *
 *   - a god operator has `agency_id NULL`, and the main host binds no tenant;
 *   - every HTTP request is a COLD request: no guard user in memory, so
 *     `auth:godmode` and IdentifyAgency's `check()` must rehydrate from the
 *     session with the tenant scope live;
 *   - TenantScope fails closed on the web, so that rehydration query throws
 *     unless the guard opts out of the scope for its own lookup.
 *
 * Why the sibling tests in GodModeWebHostLoginTest were not enough: several of
 * them post inside `withoutAnyTenant()` and then GET OUTSIDE it, so a tenant is
 * bound for the GET. That is the benign case, and it passed against the broken
 * code. Here every GET happens with the tenant forgotten AND the scope judging
 * the request as a web request (`testingAsWebRequest()`), which is what turns
 * the console fail-open branch off.
 *
 * Verified red -> green: with GodModeGuard::user() restoring via
 * `parent::user()` inside a bypass, or with a plain
 * `$this->provider->retrieveById($id)`, the cold tests below reproduce the live
 * 'Tenant scope could not be applied to [users]'.
 */
class GodModeColdSessionTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    protected function setUp(): void
    {
        parent::setUp();

        // Judge every query here as an HTTP request, not artisan.
        TenantScope::testingAsWebRequest();
    }

    protected function tearDown(): void
    {
        // Static: leaving it on disables the console path for every test after.
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

    /**
     * A session that already holds a logged-in god operator, with nothing
     * resolved in memory — the state a second HTTP request starts in.
     */
    private function godSession(User $god): array
    {
        return [
            auth('godmode')->getName() => $god->id,
            'godmode.started_at'       => now(),
            'godmode.last_activity_at' => now(),
        ];
    }

    /** No container instance and no session tenant: the main host, unbound. */
    private function withoutTenant(): void
    {
        $this->app->forgetInstance('tenant_agency');
    }

    // ── the live failure ─────────────────────────────────────────────

    #[Test]
    public function a_cold_dashboard_request_restores_the_operator_with_no_tenant_bound(): void
    {
        $god = $this->god();

        $this->withoutTenant();

        $response = $this->withSession($this->godSession($god))
            ->get(route('godmode.dashboard'));

        // 500 is the bug. Assert 200 explicitly so a crash can never be read as
        // "some refusal was expected".
        $response->assertOk();
        $response->assertSee('God Mode');

        // The operator was actually rehydrated from the session row, not merely
        // 200'd.
        $this->assertSame($god->id, auth('godmode')->id());
    }

    #[Test]
    public function the_redirect_target_after_a_real_login_answers_200_with_no_tenant_bound(): void
    {
        $god = $this->god();

        $this->withoutTenant();

        $this->withSession([])->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ])->assertRedirect(route('godmode.dashboard'));

        // A real browser makes a new request with a new guard. Dropping the
        // guards and forgetting the tenant reproduces that, and `get()` is
        // what the redirect actually turns into.
        $this->app['auth']->forgetGuards();
        $this->withoutTenant();

        $this->get(route('godmode.dashboard'))
            ->assertOk()
            ->assertSee('God Mode');
    }

    #[Test]
    public function the_operator_restore_does_not_write_a_bypass_row_on_every_page_view(): void
    {
        $god = $this->god();

        $this->withoutTenant();
        $this->withSession($this->godSession($god))->get(route('godmode.dashboard'))->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withSession($this->godSession($god))->get(route('godmode.dashboard'))->assertOk();

        // Rehydration runs per request; auditing it would write one row per
        // page view and bury the login-path bypass row it should sit beside.
        $this->assertSame(
            0,
            (int) AuditEvent::withoutGlobalScopes()
                ->where('action', AuditEvent::ACTION_TENANT_BYPASS)
                ->count(),
            'restoring the operator from the session is not a bypass decision'
        );
    }

    // ── the scope must not have been weakened ─────────────────────────

    #[Test]
    public function the_guard_fix_does_not_reopen_unbound_queries_for_other_models(): void
    {
        $god = $this->god();
        $this->withoutTenant();

        $this->withSession($this->godSession($god))->get(route('godmode.dashboard'))->assertOk();

        // A FRESH request, tenant still unbound: the guard's opt-out must not
        // have leaked past its own lookup. If it had used Tenant::bypass() the
        // flag would already be back to zero by now, so this also pins that it
        // is an opt-out, not a global mode.
        //
        // refreshApplication() matters here: within one test the harness
        // rebinds app['request'] on every $this->get(), but TenantScope reads
        // request() — the UNBOUND instance — so a second $this->get() would
        // still see the first request's `tenant_unbound_allowed` mark and fail
        // open. Verified: without this line the assertion below does not throw.
        $this->refreshApplication();
        TenantScope::testingAsWebRequest();
        $this->withoutTenant();

        $this->expectException(\App\Exceptions\UnboundTenantException::class);

        User::query()->where('username', 'landlord')->first();
    }

    #[Test]
    public function a_suspended_operator_loses_a_cold_session(): void
    {
        $god = $this->god();

        $this->withoutTenant();
        $this->withSession($this->godSession($god))->get(route('godmode.dashboard'))->assertOk();

        $god->update(['status' => 'suspended']);
        $this->app['auth']->forgetGuards();
        $this->withoutTenant();

        $this->get(route('godmode.dashboard'))->assertForbidden();
        $this->assertGuest('godmode');
    }
}
