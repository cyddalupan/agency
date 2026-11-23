<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b043f7298f1df31d8e5ee0e, AC3 — the failure mode that matters.
 *
 * The risk of moving tenant binding across the session boundary is not
 * performance: it is one agency's user being silently served another agency's
 * data because the binding now comes from a different place, in a different
 * order, on a request that carries no session at all (a remember-me cookie).
 *
 * So this test runs the real recaller path for TWO agencies at once:
 *   - the user logs in with only an encrypted recaller cookie (no session),
 *   - the dashboard must render,
 *   - the bound tenant must be THAT user's agency,
 *   - and the other agency's applicants must be invisible: a cross-agency
 *     Applicant count inside the request is 0.
 *
 * The agencies are named so that the WRONG pick is the one that sorts first
 * ("AAA Other" < "Zeta Agency"): anything that guesses "first agency" fails.
 *
 * Evidence rule (the one that invalidated af819c26): the cookie must be the
 * encrypted `CookieValuePrefix::create(...) . $plain` form with serialize=false.
 * A plain `id|token|hash` is nulled by EncryptCookies and the request is a
 * guest — which would make every assertion below about a guest page.
 */
class RecallerTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private const RECALLER_COOKIE = 'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d';

    private Agency $agency;
    private Agency $otherAgency;
    private User $admin;
    private User $otherAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Judged as a web request: PHPUnit boots through the console kernel,
        // where TenantScope fails OPEN. Without this the legs below would pass
        // against code that 500s in a browser.
        TenantScope::testingAsWebRequest();

        $this->otherAgency = Agency::factory()->create(['name' => 'AAA Other', 'subdomain' => 'aaa-other', 'status' => 'active']);
        $this->agency = Agency::factory()->create(['name' => 'Zeta Agency', 'subdomain' => 'zeta-agency', 'status' => 'active']);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Zeta Admin',
            'email'     => 'zeta-admin@example.test',
            'user_type' => 'admin',
            'status'    => 'active',
            'remember_token' => str_repeat('z', 60),
        ]);

        $this->otherAdmin = User::factory()->create([
            'agency_id' => $this->otherAgency->id,
            'name'      => 'Other Admin',
            'email'     => 'other-admin@example.test',
            'user_type' => 'admin',
            'status'    => 'active',
            'remember_token' => str_repeat('o', 60),
        ]);

        Applicant::factory()->create(['agency_id' => $this->agency->id, 'first_name' => 'OwnApplicant']);
        Applicant::factory()->create(['agency_id' => $this->otherAgency->id, 'first_name' => 'ForeignApplicant']);
        Applicant::factory()->create(['agency_id' => $this->otherAgency->id, 'first_name' => 'ForeignApplicant2']);
    }

    protected function tearDown(): void
    {
        TenantScope::testingAsWebRequest(false);

        parent::tearDown();
    }

    /** The recaller exactly as a browser carries it through EncryptCookies. */
    private function encryptedRecaller(User $user): string
    {
        $plain = $user->getAuthIdentifier() . '|' . $user->getRememberToken() . '|' . $user->getAuthPassword();

        return encrypt(
            CookieValuePrefix::create(self::RECALLER_COOKIE, app('encrypter')->getKey()) . $plain,
            false
        );
    }

    #[Test]
    public function a_recaller_only_login_sees_only_its_own_agencys_data(): void
    {
        $this->assertFalse(app()->bound('tenant_agency'), 'a tenant was already bound before the request');

        $response = $this->withUnencryptedCookie(self::RECALLER_COOKIE, $this->encryptedRecaller($this->admin))
            ->get('/dashboard');

        // ── the recaller really authenticated (not a guest page) ──────────
        $response->assertOk();
        $this->assertTrue(Auth::guard('web')->check(), 'the recaller did not authenticate — the cookie evidence is invalid');
        $this->assertSame($this->admin->id, Auth::guard('web')->id());

        // ── the tenant bound is this user's agency, not the other one ─────
        $this->assertTrue(app()->bound('tenant_agency'), 'no tenant was bound for a recaller-only request');
        $this->assertSame($this->agency->id, app('tenant_agency')->id,
            'bound tenant ' . app('tenant_agency')->id . ' is not the user\'s agency ' . $this->agency->id);

        // ── and the scope honours it: zero rows from the other agency ─────
        $this->assertSame(0, Applicant::where('agency_id', $this->otherAgency->id)->count(),
            'a cross-agency Applicant is visible through the tenant scope');
        $this->assertSame(1, Applicant::count(), 'the scope did not isolate the request to its own agency');
        $this->assertSame(1, DB::table('applicants')->where('agency_id', $this->agency->id)->count());

        // ── the page shows its own data only ─────────────────────────────
        $response->assertDontSee('ForeignApplicant');
        $response->assertDontSee('Whoops', false);
    }

    #[Test]
    public function the_other_agency_user_binds_the_other_agency_and_nothing_else(): void
    {
        // Same request shape, the other user: the binding must follow the USER,
        // not the fixture order. If anything cached or guessed the first
        // agency, this leg flips red.
        $response = $this->withUnencryptedCookie(self::RECALLER_COOKIE, $this->encryptedRecaller($this->otherAdmin))
            ->get('/dashboard');

        $response->assertOk();
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertSame($this->otherAdmin->id, Auth::guard('web')->id());

        $this->assertSame($this->otherAgency->id, app('tenant_agency')->id,
            'the bound tenant did not follow the recaller user');

        $this->assertSame(0, Applicant::where('agency_id', $this->agency->id)->count(),
            'the other agency\'s applicant leaked into this request');
        $this->assertSame(2, Applicant::count());
    }

    /**
     * The unit-level guard for the over-binding failure mode: a user agency
     * bound from a session must never replace one already bound from the host.
     * Driven through the middleware directly so the ordering of "host first,
     * session only when unbound" is asserted rather than implied.
     */
    #[Test]
    public function a_session_binding_never_overrides_a_host_binding(): void
    {
        $hostAgency = $this->otherAgency;

        // Simulate the state ResolveTenantHost leaves behind for a tenant host.
        app()->instance('tenant_agency', $hostAgency);

        $middleware = app(\App\Http\Middleware\BindTenantFromSession::class);
        $request = \Illuminate\Http\Request::create('http://zeta-agency.fixitautoservices.com/dashboard', 'GET');
        $request->attributes->set('tenant_agency', $hostAgency);

        $this->actingAs($this->admin); // user belongs to $this->agency

        $middleware->handle($request, function () use ($hostAgency) {
            $this->assertSame($hostAgency->id, app('tenant_agency')->id,
                'the session binding overrode the host binding — this is the cross-agency leak');

            return response('ok');
        });
    }
}
