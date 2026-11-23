<?php

namespace Tests\Feature\Auth;

use App\Exceptions\UnboundTenantException;
use App\Models\Agency;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b03c8be082e813a8c1ffe6f (A3) — the in-CI smoke check.
 *
 * Three incidents, one cause: every automated check asked the front door
 * whether it opened for a stranger. `GET /` was 200 the whole time a logged-in
 * browser got a 500, because the failures live PAST the door — on an
 * authenticated, tenant-scoped page.
 *
 * The legs, on an otherwise EMPTY tenant (one agency row, one user row):
 *   1. guest        GET /                     -> 200 + the real title
 *   2. agency-bound GET /dashboard, /payments -> 200, no UnboundTenantException
 *   3. recaller     agency-bound remember-me  -> authenticates, page renders
 *   4. recaller     operator    remember-me   -> refused to godmode.login, no 5xx
 *
 * Two things make this test worth its runtime, and both are load-bearing:
 *
 * TenantScope::testingAsWebRequest() is ON for every method. PHPUnit boots
 * through the console kernel, and the console branch of TenantScope fails
 * OPEN — without the flag, every leg here passes against code that 500s in a
 * browser. That is the exact false green that let today's 500 survive two
 * rounds of "fixed". tearDown() turns it off again: it is static, and leaving
 * it on would disable the console exemption for every later test in the process.
 *
 * Every leg counts the tenant tables to zero BEFORE it fires the request, so a
 * seeded fixture cannot silently make a leg pass while exercising nothing.
 *
 * Recaller cookies are sent ENCRYPTED, the way a browser carries them. The web
 * group starts with EncryptCookies, which decrypts every cookie it sees
 * (`CookieValuePrefix` + `encrypt(..., false)`); a plain `id|token|hash` string
 * is not a valid payload, so EncryptCookies nulls it and the request is a
 * plain guest request — the assertion passes for the wrong reason. This is
 * the opposite of what af819c26 assumed, and the reason that test proves
 * nothing about the recaller path (see the report on the card).
 *
 * No login POST anywhere: no RateLimiter key is consumed and no audit_events
 * row is written, so the 5-attempts-per-email budget real users share is
 * untouched.
 */
class SmokeAuthPagesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    /** The web guard's historical recaller cookie name, as a browser holds it. */
    private const RECALLER_COOKIE = 'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d';

    protected function setUp(): void
    {
        parent::setUp();

        TenantScope::testingAsWebRequest();

        $this->agency = Agency::factory()->create([
            'name'      => 'Smoke Test Agency',
            'subdomain' => 'smoke-test',
            'status'    => 'active',
        ]);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Smoke Admin',
            'email'     => 'smoke-admin@example.test',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        // Static flag: leaving it on would disable the console exemption for
        // every later test in the process, and leaving it off would let these
        // legs pass against broken code.
        TenantScope::testingAsWebRequest(false);

        parent::tearDown();
    }

    // ── helpers ─────────────────────────────────────────────────────

    private function operator(): User
    {
        return User::factory()->create([
            'agency_id' => null,
            'name'      => 'Operator',
            'email'     => 'admin@agency.com',
            'username'  => 'landlord',
            'user_type' => 'super_admin',
            'status'    => 'active',
        ]);
    }

    private function withRememberToken(User $user, string $token): User
    {
        $user->forceFill(['remember_token' => $token])->save();

        return $user->fresh();
    }

    /** The recaller as a browser holds it, i.e. as EncryptCookies expects it. */
    private function encryptedRecaller(User $user): string
    {
        $plain = $user->getAuthIdentifier() . '|' . $user->getRememberToken() . '|' . $user->getAuthPassword();

        return encrypt(
            CookieValuePrefix::create(self::RECALLER_COOKIE, app('encrypter')->getKey()) . $plain,
            false
        );
    }

    /**
     * The empty-tenant precondition, asserted BEFORE the request.
     *
     * Every tenant table a leg reads is counted, and it must be zero. `users`
     * is not asserted here: the fixture is one, and three legs add a second
     * deliberately (the remembered user, the operator).
     */
    private function assertTenantIsEmpty(): void
    {
        foreach (['applicants', 'employers', 'job_positions', 'payments', 'bills'] as $table) {
            $this->assertSame(0, DB::table($table)->count(), "tenant table [{$table}] is not empty before the request");
        }

        $this->assertSame(1, DB::table('agencies')->count(), 'exactly one agency row is expected (the fixture)');
    }

    // ── AC3: guest ──────────────────────────────────────────────────

    #[Test]
    public function the_guest_front_door_is_200_and_is_the_real_page(): void
    {
        $this->assertTenantIsEmpty();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('LANDAS — Recruitment Agency Management', false);
        $response->assertDontSee('Whoops', false);
        $response->assertDontSee('UnboundTenantException', false);
    }

    // ── AC4: authenticated, agency-bound ────────────────────────────

    #[Test]
    public function an_agency_bound_dashboard_renders_without_an_unbound_tenant(): void
    {
        $this->assertTenantIsEmpty();

        // withoutExceptionHandling() so a throw is a thrown exception here and
        // not a 500 that a loose status assertion could be written around.
        $response = $this->actingAs($this->admin)
            ->withoutExceptionHandling()
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Smoke Test Agency', false);
        $response->assertDontSee('Whoops', false);
    }

    #[Test]
    public function an_agency_bound_payments_ledger_renders_without_an_unbound_tenant(): void
    {
        $this->assertTenantIsEmpty();

        $response = $this->actingAs($this->admin)
            ->withoutExceptionHandling()
            ->get('/payments');

        $response->assertOk();
        $response->assertDontSee('Whoops', false);
    }

    // ── AC5: agency-bound recaller logs in ──────────────────────────

    #[Test]
    public function an_agency_bound_remember_me_cookie_authenticates_and_the_page_is_200(): void
    {
        $bound = $this->withRememberToken(
            User::factory()->create([
                'agency_id' => $this->agency->id,
                'email'     => 'remembered@example.test',
                'user_type' => 'admin',
                'status'    => 'active',
            ]),
            str_repeat('a', 60)
        );

        $this->assertTenantIsEmpty();

        $response = $this->withUnencryptedCookie(self::RECALLER_COOKIE, $this->encryptedRecaller($bound))
            ->get('/dashboard');

        // No session id and no session cookie: the recaller is the only thing
        // that can have authenticated this request.
        $this->assertNotSame(500, $response->status(), 'the agency-bound recaller threw');
        $response->assertOk();
        $this->assertTrue(Auth::guard('web')->check(), 'the recaller did not log the agency-bound user in');
        $this->assertSame($bound->id, Auth::guard('web')->id());
    }

    // ── AC6: operator recaller is refused, not 500 ──────────────────

    #[Test]
    public function an_agency_less_operator_recaller_is_refused_to_godmode_login_and_does_not_500(): void
    {
        $operator = $this->withRememberToken($this->operator(), str_repeat('t', 60));

        $this->assertTenantIsEmpty();

        $response = $this->withUnencryptedCookie(self::RECALLER_COOKIE, $this->encryptedRecaller($operator))
            ->get('/dashboard');

        // The 11:40:04 production shape: sessions empty, recaller cookie only.
        $this->assertNotSame(500, $response->status(), 'the operator recaller threw');
        $this->assertLessThan(500, $response->status(), 'a 5xx reached the browser');
        $response->assertRedirect(route('godmode.login'));
        $this->assertGuest('web');
        // The token that minted the cookie is retired, so a replay cannot work.
        $this->assertNull($operator->fresh()->remember_token);
    }

    // ── AC7: the flag is live, not a dead switch ────────────────────

    #[Test]
    public function the_console_exemption_is_really_what_the_flag_turns_off(): void
    {
        // PHPUnit boots through the console kernel, where the scope fails
        // OPEN. If the flag were ignored, EVERY leg in this file would pass
        // against code that 500s in a browser — the exact false green this
        // card exists to kill. So the flag's effect is asserted on a real
        // request, in the direction the browser runs:
        //
        //   flag ON  + no bound tenant -> the request reaches its controller
        //   flag OFF + no bound tenant -> the same request is refused
        //
        // An agency-less, non-operator session is used because nothing
        // retires it: the operator case is handled by design and would not
        // prove the flag.
        $orphan = User::factory()->create([
            'agency_id' => null,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->assertTenantIsEmpty();
        $this->assertFalse(app()->bound('tenant_agency'), 'a tenant is already bound; the assertion below would prove nothing');

        // ON: judged as a web request with nothing bound, the page must fail.
        // The exception handler turns the throw into a 500 (that is what a
        // browser sees), so the status is the observable, not a rethrown
        // exception. If it comes back 200, the console exemption won: the flag
        // never reached the scope, and every leg above proves nothing.
        $onStatus = $this->actingAs($orphan)->get('/applicants')->status();

        $this->assertSame(
            500,
            $onStatus,
            'with the flag ON an unbound web request was served instead of failing: the console exemption won'
        );

        // OFF: the same request, now on the console path. It must NOT throw —
        // that is the fail-open branch, and seeing it here is the proof that
        // the flag is what flips the behaviour.
        TenantScope::testingAsWebRequest(false);

        // The orphan is still authenticated for this request (actingAs), so
        // the same unbound page is now served on the console path instead of
        // failing: the one difference between these two statuses is the flag.
        $offStatus = $this->actingAs($orphan)->get('/applicants')->status();

        $this->assertNotSame(
            500,
            $offStatus,
            'with the flag OFF the request still failed: the flag is not the switch it claims to be'
        );
    }
}
