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
     * On the control plane (no tenant bound) the same failed login cannot reach
     * audit_events at all; with defect C's fix it is logged at warning level
     * with the attempted email rather than disappearing.
     */
    #[Test]
    public function failed_agency_login_without_a_tenant_is_logged_not_silent(): void
    {
        // The failed agencyLogin takes several audited bypasses (user lookup,
        // authenticate, failed-login attribution); with no tenant, EVERY one of
        // them is unattributable, so every one logs at warning level.
        $log = \Mockery::mock(\Illuminate\Log\LogManager::class);
        $attempts = [];
        $log->shouldReceive('warning')->atLeast()->once()
            ->withArgs(function (string $message, array $context) use (&$attempts) {
                if (! str_contains($message, 'could not be written to audit_events')) {
                    return false;
                }
                $attempts[] = $context['identifier'] ?? null;

                return true;
            });
        \Illuminate\Support\Facades\Log::swap($log);

        $this->post(route('agency.login.post'), [
            'email'    => 'nobody@example.test',
            'password' => 'wrong-password',
        ]);

        $this->assertContains('attempted email: nobody@example.test', $attempts,
            'the unattributable failed-login bypass never logged the attempted email: ' . json_encode($attempts));
    }
}
