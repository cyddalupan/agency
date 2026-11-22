<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\AuditEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b008b8dc9020955b2cc16e1, defect D: the agencyLogin failed-login bypass
 * dropped the $email it already had in scope, so it wrote a tenant_bypass row
 * that did not say WHAT was attempted — the one thing an unauthenticated
 * bypass can name. The other login bypasses pass attempted_email; this one
 * now does too.
 */
class AgencyLoginBypassAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function failed_agency_login_records_the_attempted_email(): void
    {
        // There is no user row, so the request NEVER needs to know a tenant.
        // binding none, the failed-login bypass has nothing to attribute its
        // audit row to (agency_id is NOT NULL), so it takes the logged path
        // from defect C instead. Give the request a tenant, as the middleware
        // would on an authenticated/agency host, to exercise the audit row.
        $agency = \App\Models\Agency::factory()->create();
        $this->app->instance('tenant_agency', $agency);
        session(['tenant_agency_id' => $agency->id]);

        $this->post(route('agency.login.post'), [
            'email'    => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        $audit = AuditEvent::withoutGlobalScopes()
            ->where('action', AuditEvent::ACTION_TENANT_BYPASS)
            ->where('subject_type', 'tenant_bypass')
            ->get()
            ->first(fn ($row) => str_contains((string) $row->subject_label, 'agencyLogin: failed-login attribution'));

        $this->assertNotNull($audit, 'the agencyLogin failed-login bypass did not audit at all');
        $this->assertSame($agency->id, $audit->agency_id);
        $this->assertSame('nobody@example.test', $audit->after['attempted_email'] ?? null);
        $this->assertStringContainsString('attempted email: nobody@example.test', (string) $audit->subject_label);
    }

    /**
     * On the control plane, the failed login's bypasses are now written as
     * control-plane audit rows (agency_id NULL — card 6b0341cf42a2071fc8b38ea5
     * made the column nullable), so the attempted email is still recorded
     * rather than only logged at warning level.
     */
    #[Test]
    public function failed_agency_login_without_a_tenant_records_control_plane_rows(): void
    {
        $this->post(route('agency.login.post'), [
            'email'    => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        $rows = AuditEvent::withoutGlobalScopes()
            ->where('action', AuditEvent::ACTION_TENANT_BYPASS)
            ->whereNull('agency_id')
            ->get();

        $this->assertNotEmpty($rows, 'a control-plane bypass must still leave an audit row');

        $attribution = $rows->first(fn ($row) => str_contains((string) $row->subject_label, 'failed-login attribution'));
        $this->assertNotNull($attribution, 'the failed-login bypass was not recorded');
        $this->assertSame('nobody@example.test', $attribution->after['attempted_email'] ?? null);
    }
}
