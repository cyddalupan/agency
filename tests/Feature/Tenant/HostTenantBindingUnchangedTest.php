<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Scopes\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b043f7298f1df31d8e5ee0e, AC4 + AC5 — host binding is untouched.
 *
 * The split moved only the SESSION-derived half. A tenant subdomain must still
 * bind from the host, before any session exists, exactly as before:
 *   - AC4: unauthenticated GET / on an agency host behaves as it did.
 *   - AC5: a tenant host resolving to no active agency still 404s, and a
 *     godmode path on a non-main host still 404s (before any binding).
 *
 * These go through the real kernel, so they cover the global middleware too.
 */
class HostTenantBindingUnchangedTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        // Judge these as web requests: PHPUnit boots through the console
        // kernel, where the scope fails open and every assertion here would be
        // meaningless.
        TenantScope::testingAsWebRequest();

        $this->agency = Agency::factory()->create([
            'name'      => 'Host Agency',
            'subdomain' => 'host-agency',
            'status'    => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        TenantScope::testingAsWebRequest(false);

        parent::tearDown();
    }

    /** ACA… AC4: a tenant host binds its agency with no session at all. */
    #[Test]
    public function a_tenant_subdomain_binds_from_the_host_with_no_session(): void
    {
        // A full URL, not a path + HTTP_HOST: the test client builds the
        // request from APP_URL, so a host only takes effect through the URL.
        $response = $this->get('http://host-agency.fixitautoservices.com/');

        $response->assertOk();
        $this->assertTrue(app()->bound('tenant_agency'), 'an agency host bound no tenant');
        $this->assertSame($this->agency->id, app('tenant_agency')->id);

        // And the scope is live for that host: another agency's row is not
        // reachable even though this request carries no login.
        $other = Agency::factory()->create(['name' => 'Elsewhere', 'subdomain' => 'elsewhere', 'status' => 'active']);
        Applicant::factory()->create(['agency_id' => $other->id]);
        Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $this->assertSame(1, Applicant::count(), 'the host-bound tenant did not scope the query');
    }

    /** The main host binds nothing by itself — that is BindTenantFromSession's job. */
    #[Test]
    public function the_main_host_binds_no_tenant_from_the_host(): void
    {
        $this->get('http://localhost/');

        $this->assertFalse(app()->bound('tenant_agency'), 'the main host must not bind a tenant from the host');
    }

    /** AC5a: tenant-shaped host, no active agency -> 404. */
    #[Test]
    public function a_tenant_host_with_no_active_agency_still_404s(): void
    {
        Agency::factory()->create(['name' => 'Closed', 'subdomain' => 'closed-agency', 'status' => 'inactive']);

        $this->get('http://closed-agency.fixitautoservices.com/')->assertNotFound();
        $this->get('http://never-existed.fixitautoservices.com/')->assertNotFound();
    }

    /** AC5b: godmode on a non-main host -> 404, and nothing is bound for it. */
    #[Test]
    public function godmode_on_a_tenant_host_still_404s_before_binding(): void
    {
        $this->get('http://host-agency.fixitautoservices.com/godmode/login')->assertNotFound();

        $this->assertFalse(app()->bound('tenant_agency'), 'a godmode request on a tenant host bound a tenant before refusing');
    }
}
