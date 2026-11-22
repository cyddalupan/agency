<?php

namespace Tests\Feature\GodMode;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0341cf42a2071fc8b38ea5 — god mode login.
 *
 * The tests that matter most are the guard-isolation ones: two session guards
 * on the same provider share a session key unless getName() is overridden, and
 * that single defect would let any agency login open every tenant's data.
 */
class GodModeLoginTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-battery-staple';

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

    private function tenantHost(string $slug = 'gulf'): string
    {
        return $slug.'.fixitautoservices.com';
    }

    /**
     * Put an operator on the god guard with the session marks a real login
     * writes. The middleware treats a missing `godmode.started_at` as expired
     * (a session older than the cap), so any test that wants to reach the
     * session-cap checks must seed them or it is only testing the expiry path.
     */
    private function actAsGod(User $user): self
    {
        $this->actingAs($user, 'godmode');

        return $this->withSession([
            'godmode.started_at'       => now(),
            'godmode.last_activity_at' => now(),
        ]);
    }

    /**
     * The first error message flashed for $key.
     *
     * The session can hand this back in either shape depending on when it was
     * last written — a plain array (`default.messages.username[0]`) or a
     * ViewErrorBag once the view middleware has shared it. Asking the bag
     * first and falling back to the raw array covers both; reading only one
     * shape makes the test pass for the wrong reason. Returns null when nothing
     * was flashed, which is a failure in every caller.
     */
    /**
     * Post a REAL login on the `web` guard (not actingAs). The agency login
     * writes the session key itself, which is what the guard split has to
     * survive; `actingAs` mutates the guard in place and never exercises the
     * stored key.
     */
    private function realWebLogin(User $user): void
    {
        // Literal path: POST /login is unnamed (GET /login holds name 'login').
        // UserFactory's default password ('password') is the one that works for
        // a factory user — its hash is a shared static, not per-user.
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    }

    private function errorFor(string $key = 'username'): ?string
    {
        $errors = session('errors');

        if ($errors instanceof \Illuminate\Support\ViewErrorBag) {
            return $errors->first($key) ?: null;
        }

        return $errors['default']['messages'][$key][0] ?? null;
    }

    /**
     * A god-mode operator who is also an agency admin with QMS on — the only
     * shape that may read the Audit Trail (`auth:web` + `module.access`, and
     * QMS is off by default for a plain agency).
     */
    private function godWithQms(): User
    {
        $agency = Agency::factory()->withQms()->create(['status' => 'active']);

        return $this->god(['agency_id' => $agency->id]);
    }

    /**
     * Request a tenant host with an ABSOLUTE URL.
     *
     * Measured, not assumed: `withHeaders(['Host' => ...])` does not change the
     * host Symfony sees (STATUS 200 on a page that must 404), and
     * `withServerVariables(['HTTP_HOST' => ...])` does not either — both leave
     * the test client on APP_URL's host. An absolute URL is the one form that
     * actually rehosts the request, so these tests cannot silently pass while
     * testing the main host. The returned response carries ->status().
     */
    private function requestOnTenantHost(string $method, string $path, array $payload = [], string $slug = 'gulf')
    {
        return $this->call(
            $method,
            'http://'.$this->tenantHost($slug).$path,
            $payload
        );
    }

    // ── guard isolation (the one that cannot be got wrong) ───────────

    #[Test]
    public function godmode_guard_has_its_own_session_key(): void
    {
        $web = auth('web')->getName();
        $god = auth('godmode')->getName();

        $this->assertNotSame($web, $god, 'a shared session key means a web login satisfies auth:godmode');
        $this->assertStringStartsWith('login_godmode_', $god);
        $this->assertStringStartsWith('login_web_', $web);
    }

    #[Test]
    public function a_web_session_does_not_open_god_mode(): void
    {
        $agency = Agency::factory()->create(['subdomain' => 'gulf', 'status' => 'active']);
        $admin  = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
            // The factory shares ONE static hash across every user in the
            // process, so the password here must be the factory default.
            'password'  => 'password',
        ]);

        // Logged in through the NORMAL guard, for real (session key written).
        $this->realWebLogin($admin);

        // The wrong-password response is the SAME as success (generic error), so
        // "did the login take" is asked of the guard, not of the redirect.
        $this->assertTrue(auth('web')->check(), 'sanity: the web login must have succeeded');

        // Asked BEFORE any request: a god-mode request would redirect a guest
        // either way, so "no session" has to be asserted on the untouched
        // session the web login just wrote. With key collision this is true.
        $this->assertFalse(auth('godmode')->check(), 'a web login must never satisfy auth:godmode');

        $this->get(route('godmode.dashboard'))->assertRedirect('/godmode/login');
        $this->assertGuest('godmode');
    }

    #[Test]
    public function a_web_session_of_a_super_admin_still_does_not_open_god_mode(): void
    {
        // Same user object, same provider, same table — only the guard differs.
        // Password matches the factory default so the web login can be real.
        $god = $this->god(['password' => 'password']);

        $this->realWebLogin($god);
        $this->assertTrue(auth('web')->check(), 'sanity: the web login must have succeeded');

        $this->assertFalse(auth('godmode')->check(), 'a web login must never satisfy auth:godmode');

        $this->get(route('godmode.dashboard'))->assertRedirect('/godmode/login');
        $this->assertGuest('godmode');
    }

    #[Test]
    public function a_god_session_does_not_open_the_agency_area(): void
    {
        $this->god();

        // Real god login: the session key is written by the god guard itself.
        $this->post(route('godmode.login.post'), ['username' => 'landlord', 'password' => self::PASSWORD])
            ->assertRedirect(route('godmode.dashboard'));
        $this->assertTrue(auth('godmode')->check(), 'sanity: the god login must have succeeded');

        $this->assertGuest('web');
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // ── godmode.only ─────────────────────────────────────────────────

    #[Test]
    public function an_admin_on_the_godmode_guard_is_refused_403(): void
    {
        $agency = Agency::factory()->create();
        $admin  = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        // Even if the session somehow carries this user, godmode.only is the
        // gate — admin is NOT enough (that is what CheckRole would have done).
        $this->actAsGod($admin);

        $this->get(route('godmode.dashboard'))->assertForbidden();
    }

    #[Test]
    public function a_suspended_super_admin_is_refused_403(): void
    {
        $god = $this->god(['status' => 'suspended']);

        $this->actAsGod($god);

        $this->get(route('godmode.dashboard'))->assertForbidden();
    }

    #[Test]
    public function a_signed_in_operator_reaches_the_placeholder_dashboard_with_no_store(): void
    {
        $god = $this->god();
        $this->actAsGod($god);

        $response = $this->get(route('godmode.dashboard'));

        $response->assertOk();
        $response->assertSee('God Mode');
        $response->assertSee('Sign out');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    // ── login ────────────────────────────────────────────────────────

    #[Test]
    public function the_login_page_renders_with_no_register_forgot_or_public_link(): void
    {
        $response = $this->get(route('godmode.login'));

        $response->assertOk();
        $response->assertSee('name="username"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('autocomplete="current-password"', false);
        $response->assertDontSee('Remember me');
        $response->assertDontSee('name="remember"', false);
        $response->assertDontSee('/register');
        $response->assertDontSee('Forgot');
    }

    #[Test]
    public function an_operator_logs_in_by_username_and_lands_on_the_dashboard(): void
    {
        $god = $this->god();

        $response = $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]);

        $response->assertRedirect(route('godmode.dashboard'));
        $this->assertTrue(auth('godmode')->check());
        $this->assertSame($god->id, auth('godmode')->id());
        $this->assertGuest('web');

        // Session caps are seeded at login.
        $this->assertNotNull(session('godmode.started_at'));
        $this->assertNotNull(session('godmode.last_activity_at'));

        // Both spines carry the login.
        $this->assertDatabaseHas('activity_logs', ['action' => 'godmode.login', 'user_id' => $god->id]);
        $this->assertDatabaseHas('audit_events', [
            'action'        => 'login',
            'actor_user_id' => $god->id,
            'subject_type'  => User::class,
            'subject_id'    => $god->id,
        ]);

        $audit = AuditEvent::withoutGlobalScopes()->where('action', 'login')->firstOrFail();
        $this->assertNull($audit->agency_id, 'a god login is a control-plane row');
        $this->assertSame('godmode', $audit->after['channel']);
    }

    #[Test]
    public function an_operator_can_log_in_by_email(): void
    {
        $this->god();

        $this->post(route('godmode.login.post'), [
            'username' => 'landlord@godmode.local',
            'password' => self::PASSWORD,
        ])->assertRedirect(route('godmode.dashboard'));

        $this->assertTrue(auth('godmode')->check());
    }

    #[Test]
    public function a_wrong_password_fails_generically_and_logs_nothing_secret(): void
    {
        $god = $this->god();

        $response = $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => 'not-the-password',
        ]);

        $response->assertSessionHasErrors('username');
        $response->assertSessionHasInput('username', 'landlord');
        $this->assertGuest('godmode');
        $this->assertDatabaseHas('activity_logs', ['action' => 'failed_login']);

        // The submitted password never reaches a log or the session error bag.
        $this->assertStringNotContainsString(
            'not-the-password',
            json_encode(ActivityLog::withoutGlobalScopes()->get()->toArray())
        );
    }

    #[Test]
    public function an_agency_admin_gets_the_same_response_as_a_wrong_password(): void
    {
        $agency = Agency::factory()->create(['status' => 'active']);
        User::factory()->create([
            'agency_id' => $agency->id,
            'username'  => 'agencyboss',
            'email'     => 'boss@agency.test',
            'password'  => 'agency-password-123',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $wrong = $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => 'whatever',
        ]);

        $wrongError = $this->errorFor('username');
        session()->forget('errors');

        $notOperator = $this->post(route('godmode.login.post'), [
            'username' => 'agencyboss',
            'password' => 'agency-password-123',
        ]);

        $this->assertSame($wrong->status(), $notOperator->status());
        $this->assertSame($wrong->headers->get('Location'), $notOperator->headers->get('Location'));
        $this->assertSame(
            'The provided credentials do not match our records.',
            $wrongError,
            'a wrong password must not be distinguishable from a non-operator account'
        );
        $this->assertSame(
            $wrongError,
            $this->errorFor('username'),
            'a non-operator must get the SAME message as a wrong password'
        );
        $this->assertGuest('godmode');
    }

    #[Test]
    public function the_same_message_is_returned_for_a_non_operator_and_no_such_user(): void
    {
        $agency = Agency::factory()->create(['status' => 'active']);
        User::factory()->create([
            'agency_id' => $agency->id,
            'username'  => 'agencyboss',
            'password'  => 'agency-password-123',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->post(route('godmode.login.post'), ['username' => 'no-such-person', 'password' => 'x']);
        $unknown = $this->errorFor('username');
        session()->forget('errors');

        $this->post(route('godmode.login.post'), ['username' => 'agencyboss', 'password' => 'agency-password-123']);
        $nonOperator = $this->errorFor('username');

        $this->assertNotNull($unknown);
        $this->assertSame($unknown, $nonOperator);
    }

    #[Test]
    public function a_suspended_super_admin_cannot_log_in(): void
    {
        $this->god(['status' => 'pending']);

        $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ])->assertSessionHasErrors('username');

        $this->assertGuest('godmode');
    }

    #[Test]
    public function the_old_seeded_credential_is_dead(): void
    {
        // What InitialAgencySeeder used to create. It must not exist, and if a
        // stale row does exist it must carry no god-mode access: the seeder no
        // longer mints a super_admin at all.
        //
        // IMPORTANT — what this test canNOT prove. This runs against a FRESH
        // seed in the test database only. It asserts the seeder no longer
        // creates the row; it says NOTHING about whether a live database still
        // holds a pre-existing row with that email and hash. A live row planted
        // before the seeder was edited is untouched by every seeder and by this
        // test. Round-4 QA proved exactly that: admin@agency.com / password
        // still signed into god mode on the live DB while this test was green.
        // Green here means "the seeder is clean", NOT "the back door is shut".
        // Shutting a live row is an operator action:
        //   UPDATE users SET status='pending' WHERE id=1;
        // Verify with a live Hash::check against the real row, never with this test.
        $this->seed(\Database\Seeders\InitialAgencySeeder::class);

        $this->assertDatabaseMissing('users', [
            'email'     => 'admin@agency.com',
            'user_type' => 'super_admin',
        ]);

        $this->post(route('godmode.login.post'), [
            'username' => 'admin@agency.com',
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest('godmode');
    }

    #[Test]
    public function the_card_plaintext_password_is_not_a_god_credential(): void
    {
        $this->god();

        $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => 'zenmaster12',
        ])->assertSessionHasErrors('username');

        $this->assertGuest('godmode');
    }

    // ── rate limiting ────────────────────────────────────────────────

    #[Test]
    public function five_failures_lock_the_username_and_a_correct_password_still_fails(): void
    {
        $this->god();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('godmode.login.post'), ['username' => 'landlord', 'password' => 'wrong']);
        }

        $blocked = $this->post(route('godmode.login.post'), [
            'username' => 'landlord',
            'password' => self::PASSWORD,
        ]);

        $blocked->assertSessionHasErrors('username');
        $this->assertStringContainsString('Too many login attempts', $this->errorFor());
        $this->assertGuest('godmode');
        $this->assertDatabaseHas('activity_logs', ['action' => 'godmode.lockout']);
    }

    #[Test]
    public function the_per_username_decay_is_fifteen_minutes(): void
    {
        $this->god();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('godmode.login.post'), ['username' => 'landlord', 'password' => 'wrong']);
        }

        $key = \App\Http\Controllers\GodModeAuthController::userRateLimitKey('landlord');
        $this->assertGreaterThan(840, RateLimiter::availableIn($key));
        $this->assertLessThanOrEqual(900, RateLimiter::availableIn($key));
    }

    #[Test]
    public function ten_failures_from_one_ip_across_two_usernames_trip_the_ip_limit(): void
    {
        // username A: 5 failures fills its own budget; username B: 5 more. The
        // 10th failure is the last one allowed on the IP before the lockout.
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('godmode.login.post'), ['username' => 'user-a', 'password' => 'wrong']);
        }

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('godmode.login.post'), ['username' => 'user-b', 'password' => 'wrong']);
        }

        $this->assertTrue(
            RateLimiter::tooManyAttempts(
                \App\Http\Controllers\GodModeAuthController::ipRateLimitKey('127.0.0.1'),
                10
            )
        );

        // A THIRD, fresh username is refused purely on the IP budget.
        $response = $this->post(route('godmode.login.post'), ['username' => 'user-c', 'password' => 'wrong']);
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Too many login attempts', $this->errorFor());
    }

    #[Test]
    public function a_successful_login_clears_only_the_username_budget(): void
    {
        $this->god();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('godmode.login.post'), ['username' => 'landlord', 'password' => 'wrong']);
        }

        $userKey = \App\Http\Controllers\GodModeAuthController::userRateLimitKey('landlord');
        $ipKey   = \App\Http\Controllers\GodModeAuthController::ipRateLimitKey('127.0.0.1');

        $this->post(route('godmode.login.post'), ['username' => 'landlord', 'password' => self::PASSWORD])
            ->assertRedirect(route('godmode.dashboard'));

        $this->assertFalse(RateLimiter::tooManyAttempts($userKey, 1), 'a success clears the username budget');
        $this->assertTrue(RateLimiter::tooManyAttempts($ipKey, 0), 'the IP budget must survive a success');
    }

    // ── session cap ──────────────────────────────────────────────────

    #[Test]
    public function a_session_idle_for_thirty_minutes_is_sent_back_to_login(): void
    {
        $god = $this->god();

        $this->actingAs($god, 'godmode');
        $this->withSession([
            'godmode.started_at'        => now()->subMinutes(45),
            'godmode.last_activity_at'  => now()->subMinutes(31),
        ]);

        $response = $this->get(route('godmode.dashboard'));

        $response->assertRedirect(route('godmode.login'));
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('expired', $this->errorFor());
        $this->assertGuest('godmode');
    }

    #[Test]
    public function a_session_over_eight_hours_old_is_refused_even_if_active(): void
    {
        $god = $this->god();

        $this->actingAs($god, 'godmode');
        $this->withSession([
            'godmode.started_at'       => now()->subHours(9),
            'godmode.last_activity_at' => now(),
        ]);

        $this->get(route('godmode.dashboard'))->assertRedirect(route('godmode.login'));
        $this->assertGuest('godmode');
    }

    #[Test]
    public function activity_refreshes_the_idle_clock(): void
    {
        $god = $this->god();

        $this->actingAs($god, 'godmode');
        $this->withSession([
            'godmode.started_at'       => now()->subMinutes(20),
            'godmode.last_activity_at' => now()->subMinutes(20),
        ]);

        $this->get(route('godmode.dashboard'))->assertOk();

        $this->assertTrue(
            now()->diffInMinutes(session('godmode.last_activity_at'), true) < 1,
            'the idle clock must be refreshed by an authenticated request'
        );
    }

    // ── logout ───────────────────────────────────────────────────────

    #[Test]
    public function logout_clears_the_god_session_and_audits(): void
    {
        $god = $this->god();
        $this->actAsGod($god);

        $this->post(route('godmode.logout'))->assertRedirect(route('godmode.login'));

        $this->assertGuest('godmode');
        $this->assertDatabaseHas('activity_logs', ['action' => 'godmode.logout', 'user_id' => $god->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'logout', 'actor_user_id' => $god->id]);

        $this->get(route('godmode.dashboard'))->assertRedirect('/godmode/login');
    }

    // ── tenant hosts ─────────────────────────────────────────────────

    #[Test]
    public function godmode_404s_on_a_tenant_host(): void
    {
        Agency::factory()->create(['subdomain' => 'gulf', 'status' => 'active']);

        $this->requestOnTenantHost('GET', route('godmode.dashboard', absolute: false))
            ->assertNotFound();
    }

    #[Test]
    public function godmode_login_404s_on_a_tenant_host(): void
    {
        Agency::factory()->create(['subdomain' => 'gulf', 'status' => 'active']);

        $this->requestOnTenantHost('GET', route('godmode.login', absolute: false))
            ->assertNotFound();
    }

    #[Test]
    public function godmode_login_post_404s_on_a_tenant_host_and_never_authenticates(): void
    {
        Agency::factory()->create(['subdomain' => 'gulf', 'status' => 'active']);
        $this->god();

        $this->requestOnTenantHost(
            'POST',
            route('godmode.login.post', absolute: false),
            ['username' => 'landlord', 'password' => self::PASSWORD]
        )->assertNotFound();

        $this->assertGuest('godmode');
    }

    #[Test]
    public function the_control_plane_login_page_is_reachable_on_the_main_host(): void
    {
        $this->get(route('godmode.login'))->assertOk();
    }

    // ── audit trail visibility ───────────────────────────────────────

    #[Test]
    public function a_super_admin_sees_the_control_plane_row_on_the_audit_trail(): void
    {
        // Real shape: log in through the god guard, then read the trail. The
        // audit screen is behind `auth:web`, so the operator also holds a web
        // session — which is exactly the case the guard split must tolerate:
        // one user object, two session keys, two independent logins.
        $god = $this->godWithQms();
        $this->post(route('godmode.login.post'), ['username' => 'landlord', 'password' => self::PASSWORD]);
        $this->assertTrue(auth('godmode')->check());

        auth('web')->login($god);

        $response = $this->get(route('audit_trail.index'));

        $response->assertOk();
        $response->assertSee((string) $god->username);
        $response->assertSee('Includes control-plane events');
    }

    #[Test]
    public function an_agency_admin_sees_no_control_plane_rows(): void
    {
        // The audit screen sits behind module.access, which closes QMS for an
        // agency that has it off. The test is about visibility, not the switch.
        $agency = Agency::factory()->withQms()->create(['status' => 'active']);
        $admin  = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        AuditEvent::withoutGlobalScopes()->create([
            'agency_id'     => null,
            'actor_user_id' => null,
            'action'        => 'login',
            'subject_type'  => User::class,
            'subject_id'    => 0,
            'subject_label' => 'control-plane-secret-label',
            'before'        => null,
            'after'         => ['channel' => 'godmode'],
        ]);

        $this->actingAs($admin, 'web');
        $response = $this->get(route('audit_trail.index'));

        $response->assertOk();
        $response->assertDontSee('control-plane-secret-label');
        $response->assertDontSee('Includes control-plane events');
    }

    // ── regression: the normal login path is untouched ───────────────

    #[Test]
    public function the_main_host_root_still_answers_200(): void
    {
        $this->get('/')->assertOk();
    }

    #[Test]
    public function an_agency_admin_still_logs_in_on_their_subdomain(): void
    {
        $agency = Agency::factory()->create(['subdomain' => 'gulf', 'status' => 'active']);
        $admin  = User::factory()->create([
            'agency_id' => $agency->id,
            'email'     => 'boss@gulf.test',
            'password'  => 'agency-password-123',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        // POST /login is unnamed, so it cannot be built from a route name.
        $response = $this->call(
            'POST',
            'http://'.$this->tenantHost('gulf').'/login',
            ['email' => 'boss@gulf.test', 'password' => 'agency-password-123']
        );

        $response->assertRedirect(route('agency.dashboard'));
        $this->assertTrue(auth('web')->check());
        $this->assertSame($admin->id, auth('web')->id());
        $this->assertGuest('godmode');
    }
}
