<?php

namespace Tests\Feature\Auth;

use App\Exceptions\UnboundTenantException;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0420a205f32d19776021d4 — production 500 for the agency-less
 * super_admin on the tenant dashboard.
 *
 * The defect: AuthController::login() authenticated the operator on the `web`
 * guard and redirected them to route('dashboard') — the TENANT dashboard —
 * where the first tenant-scoped query hit `users` with no agency bound and
 * TenantScope threw. Every 500 was table [users].
 *
 * The fix: POST /login never creates a `web` session for an agency-less
 * super_admin; it sends them to route('godmode.login'). A stale pre-fix web
 * session for that account is dropped by EnsureNoOperatorWebSession. God mode
 * keeps its own session key (GodModeGuard); nothing here joins the two.
 *
 * TenantScope::testingAsWebRequest() is ON for every test in this file. PHPUnit
 * boots through the console kernel, where the scope fails open, so without it
 * AC3 and AC5 would pass against the broken code — precisely why QA never saw
 * this. The flag is static and reset in tearDown().
 */
class OperatorWebLoginTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

    protected function setUp(): void
    {
        parent::setUp();

        TenantScope::testingAsWebRequest();
    }

    protected function tearDown(): void
    {
        TenantScope::testingAsWebRequest(false);

        parent::tearDown();
    }

    private function operator(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => null,
            'username'  => 'landlord',
            'email'     => 'admin@agency.com',
            'password'  => self::PASSWORD,
            'user_type' => 'super_admin',
            'status'    => 'active',
        ], $attrs));
    }

    /**
     * The login form's answer as one comparable string: the flashed error and
     * the echoed field. Two logins are "identical" when this is equal.
     */
    private function answer(): string
    {
        $errors = session('errors');

        $message = $errors instanceof \Illuminate\Support\ViewErrorBag
            ? (string) $errors->first('email')
            : (string) ($errors['default']['messages']['email'][0] ?? '');

        return 'error:' . $message . '|old:' . session('_old_input.email');
    }

    // ── AC1 ──────────────────────────────────────────────────────────

    #[Test]
    public function the_operator_correct_password_redirects_to_godmode_login_and_creates_no_web_session(): void
    {
        $this->operator();

        $response = $this->post(route('login'), [
            'email'    => 'admin@agency.com',
            'password' => self::PASSWORD,
        ]);

        $response->assertRedirect(route('godmode.login'));
        $this->assertFalse(Auth::guard('web')->check(), 'a web session was created for the operator');
        $this->assertGuest('web');
        $this->assertNull(session()->get('login_web_' . sha1(\Illuminate\Auth\SessionGuard::class)));
        $this->assertGuest('godmode');
    }

    #[Test]
    public function a_wrong_password_gets_the_identical_response(): void
    {
        $this->operator();

        $correctResponse = $this->post(route('login'), [
            'email'    => 'admin@agency.com',
            'password' => self::PASSWORD,
        ]);
        $correct = $this->answer();
        $correctLocation = $correctResponse->headers->get('Location');

        $wrongResponse = $this->post(route('login'), [
            'email'    => 'admin@agency.com',
            'password' => 'definitely-not-the-password',
        ]);

        // Same status and same Location, not merely "both redirected".
        $this->assertSame(302, $wrongResponse->status());
        $this->assertSame($correctLocation, $wrongResponse->headers->get('Location'));
        $this->assertSame($correct, $this->answer(), 'a wrong password must be indistinguishable from the right one');
        $this->assertGuest('web');
        $this->assertGuest('godmode');
    }

    #[Test]
    public function the_operator_form_never_establishes_a_godmode_session(): void
    {
        $this->operator();

        $this->post(route('login'), [
            'email'    => 'admin@agency.com',
            'password' => self::PASSWORD,
        ]);

        // The whole point of GodModeGuard's separate session key: the low
        // assurance form cannot mint the high assurance session.
        $this->assertGuest('godmode');
        $this->get(route('godmode.dashboard'))->assertRedirect(route('godmode.login'));
    }

    // ── AC2 ──────────────────────────────────────────────────────────

    #[Test]
    public function a_stale_operator_web_session_is_logged_out_and_sent_to_godmode_login(): void
    {
        $operator = $this->operator();

        $response = $this->actingAs($operator)->get(route('dashboard'));

        // 500 is the bug; assert the exact redirect.
        $response->assertRedirect(route('godmode.login'));
        $this->assertGuest('web');
    }

    #[Test]
    public function a_stale_operator_web_session_is_dropped_on_any_tenant_route(): void
    {
        $operator = $this->operator();
        // An active agency exists — the fix must not bind it to an operator.
        Agency::factory()->create(['status' => 'active']);

        $this->actingAs($operator)
            ->withSession([])
            ->get('/applicants')
            ->assertRedirect(route('godmode.login'));

        $this->assertGuest('web');
        $this->assertNull(session('tenant_agency_id'));

        // And it stays dropped: the next visit is a guest redirect, not a 500.
        $this->assertNotSame(500, $this->get('/applicants')->status());
    }

    // ── agency-bound users are untouched ─────────────────────────────

    #[Test]
    public function an_agency_bound_super_admin_still_logs_in_to_the_tenant_app(): void
    {
        $agency = Agency::factory()->create(['status' => 'active']);
        User::factory()->create([
            'agency_id' => $agency->id,
            'email'     => 'bound@example.test',
            'password'  => self::PASSWORD,
            'user_type' => 'super_admin',
            'status'    => 'active',
        ]);

        $this->post(route('login'), [
            'email'    => 'bound@example.test',
            'password' => self::PASSWORD,
        ])->assertRedirect(route('agency.dashboard'));

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertSame($agency->id, (int) session('tenant_agency_id'));

        // The operator branch must not touch them on later requests either.
        $this->get(route('dashboard'))->assertOk();
        $this->assertTrue(Auth::guard('web')->check());
    }

    #[Test]
    public function an_agency_bound_admin_session_survives_a_tenant_page(): void
    {
        $agency = Agency::factory()->create(['status' => 'active']);
        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'email'     => 'admin-a@example.test',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        $this->assertTrue(Auth::guard('web')->check());
    }

    // ── AC3 ──────────────────────────────────────────────────────────

    #[Test]
    public function an_agency_less_operator_on_a_non_public_path_does_not_throw_unbound_tenant(): void
    {
        $operator = $this->operator();

        // `/applicants` is NOT in isPublicPath() and NOT a guest route. Before
        // the middleware this request resolved `users` with no tenant and
        // threw. Go through the REAL web guard (session id in the cookie), not
        // actingAs()'s in-memory user, so the logout path is exercised.
        $response = $this->withSession([
            'login_web_' . sha1(\Illuminate\Auth\SessionGuard::class) => $operator->id,
        ])->get('/applicants');

        $response->assertRedirect(route('godmode.login'));
        $this->assertNotSame(500, $response->status());
        $this->assertGuest('web');
    }

    // ── AC4 ──────────────────────────────────────────────────────────

    #[Test]
    public function an_agency_admin_still_sees_only_its_own_agencys_rows(): void
    {
        $agencyA = Agency::factory()->create(['status' => 'active']);
        $agencyB = Agency::factory()->create(['status' => 'active']);

        $adminA = User::factory()->create([
            'agency_id' => $agencyA->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        Applicant::factory()->count(3)->create(['agency_id' => $agencyA->id]);
        Applicant::factory()->count(4)->create(['agency_id' => $agencyB->id]);

        $this->actingAs($adminA);

        $this->assertSame(3, Applicant::count(), 'a tenant request saw another agency\'s rows');
        $this->assertSame(
            0,
            Applicant::where('agency_id', $agencyB->id)->count(),
            'cross-agency rows were visible'
        );
        $this->assertSame(1, User::where('agency_id', $agencyA->id)->count());
        $this->assertSame(0, User::where('agency_id', $agencyB->id)->count());
    }

    // ── AC5 ──────────────────────────────────────────────────────────

    #[Test]
    public function a_genuine_unbound_request_still_throws(): void
    {
        // No user on any guard, no bound agency, not a public path: the
        // fail-closed behaviour must be exactly what it was.
        $this->expectException(UnboundTenantException::class);

        Applicant::count();
    }

    #[Test]
    public function an_agency_less_non_operator_session_still_throws(): void
    {
        $orphan = User::factory()->create([
            'agency_id' => null,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        // Not an operator (user_type admin): nothing logs this session out and
        // nothing marks the request. The scope refuses, as it always did.
        $this->expectException(UnboundTenantException::class);

        $this->actingAs($orphan);
        Applicant::count();
    }

    // ── AC6 (decision recorded in the PR): no godmode mark added ─────

    #[Test]
    public function a_godmode_session_reads_cross_agency_without_the_unbound_tenant_mark(): void
    {
        $operator = $this->operator();
        $this->actingAs($operator, 'godmode');

        // AC6 decision: the request mark is NOT needed for god mode. The
        // scope's existing super_admin branch (TenantScope::tenantFilter, first
        // rule) already answers "no filter" for this guard, and god screens
        // only ever run on the main host behind auth:godmode. Asserted here so
        // the decision fails loudly if either half is ever removed.
        $this->assertFalse(
            request()->attributes->get('tenant_unbound_allowed'),
            'the request mark is not what lets a god session work'
        );
        $this->assertNull(TenantScope::tenantFilter('users'));
        $this->assertNull(TenantScope::tenantFilter('applicants'));
        $this->assertSame(1, User::withoutGlobalScopes()->count());
    }

    // ── AC1 (reopened): authenticated BY REMEMBER-ME, nothing else ────
    //
    // The 11:40:04 production 500. Sessions table empty, no session cookie,
    // only the recaller. `actingAs()` and a session id both bypass the exact
    // path that broke, so this test builds the cookie the guard builds and
    // sends nothing else. It fails against commit be7a8ffb (the middleware ran
    // before StartSession and never saw the recaller) and against the plain
    // SessionGuard (userFromRecaller() throws UnboundTenantException).

    /** The recaller cookie value SessionGuard::createRecaller() produces. */
    /**
     * The recaller as the browser holds it: an id|token|hash string under the
     * web guard's cookie name. Sent with withCookie()/withUnencryptedCookie(),
     * so it reaches the guard exactly as SessionGuard::recaller() reads it.
     */
    private function recallerCookie(User $user): string
    {
        return $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$user->getAuthPassword();
    }

    private function recallerCookieName(): string
    {
        // Pre-fix name on purpose: an operator's browser is carrying THIS one.
        return 'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d';
    }

    #[Test]
    public function a_remember_me_cookie_alone_does_not_authenticate_the_operator_and_does_not_throw(): void
    {
        $operator = $this->operator(['remember_token' => str_repeat('t', 60)]);

        $response = $this->withUnencryptedCookie(
            $this->recallerCookieName(),
            $this->recallerCookie($operator),
        )->get(route('dashboard'));

        // The bug: 500 from SetLocale -> Auth::user() -> userFromRecaller().
        $this->assertNotSame(500, $response->status());
        $response->assertRedirect(route('godmode.login'));
        $this->assertGuest('web');
    }

    #[Test]
    public function the_remember_me_cookie_is_retired_and_the_stored_token_cleared(): void
    {
        $operator = $this->operator(['remember_token' => str_repeat('r', 60)]);
        $cookie = $this->recallerCookie($operator);

        $response = $this->withUnencryptedCookie($this->recallerCookieName(), $cookie)
            ->get(route('dashboard'));

        $response->assertRedirect(route('godmode.login'));

        // The one token that minted it is gone, so a replay cannot work.
        $this->assertNull($operator->fresh()->remember_token);

        // And the browser is told to drop the cookie.
        $forget = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === $this->recallerCookieName());
        $this->assertNotNull($forget, 'the recaller cookie must be expired on the response');

        // Replay after the fix is a plain guest request, not a 500 and not a login.
        $this->flushSession();
        $replay = $this->withUnencryptedCookie($this->recallerCookieName(), $cookie)->get(route('dashboard'));
        $replay->assertRedirect(route('login'));
        $this->assertGuest('web');
    }

    #[Test]
    public function an_agency_bound_users_remember_me_cookie_is_untouched(): void
    {
        $agency = Agency::factory()->create(['status' => 'active']);
        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
            'remember_token' => str_repeat('k', 60),
        ]);

        $token = $admin->remember_token;

        $response = $this->withUnencryptedCookie(
            $this->recallerCookieName(),
            $this->recallerCookie($admin),
        )->get('/dashboard');

        $this->assertNotSame(500, $response->status());
        $this->assertSame($token, $admin->fresh()->remember_token, 'token must not be cycled');
    }

    #[Test]
    public function the_web_guard_keeps_its_historical_session_and_cookie_keys(): void
    {
        // Changing either key would silently log out all 85 agency-bound users.
        $guard = $this->app['auth']->guard('web');

        $this->assertSame('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $guard->getName());
        $this->assertSame(
            'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d',
            $guard->getRecallerName()
        );
    }
}
