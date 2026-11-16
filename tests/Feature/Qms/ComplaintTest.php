<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-13 (card 6afa0daa5b58fd9984641757) — complaints table + model.
 *
 * Acceptance from the card: target dates default from an agency-configurable
 * SLA, with a sensible fallback of ack 2 days / resolve 15 days.
 */
class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);
    }

    private function makeComplaint(array $overrides = []): Complaint
    {
        return Complaint::withoutGlobalScopes()->create(array_merge([
            'agency_id'        => $this->agency->id,
            'ref_no'           => 'CMP-001',
            'complainant_name' => 'Juan Dela Cruz',
            'complainant_type' => Complaint::TYPE_APPLICANT,
            'description'      => 'Fee was charged twice.',
            'date_received'    => '2026-11-01',
            'status'           => Complaint::STATUS_OPEN,
        ], $overrides));
    }

    /** Put an SLA into the agency's settings JSON. */
    private function configureSla(?int $ackDays, ?int $resolveDays): void
    {
        $settings = (array) ($this->agency->settings ?? []);
        $settings[Complaint::SLA_SETTINGS_KEY] = array_filter([
            'ack_days'     => $ackDays,
            'resolve_days' => $resolveDays,
        ], fn ($v) => $v !== null);

        $this->agency->update(['settings' => $settings]);
        $this->agency->refresh();
    }

    // -----------------------------------------------------------------
    // Acceptance: the fallback SLA
    // -----------------------------------------------------------------

    #[Test]
    public function an_unconfigured_agency_falls_back_to_two_days_to_acknowledge_and_fifteen_to_resolve(): void
    {
        $complaint = $this->makeComplaint();

        $this->assertSame('2026-11-03', $complaint->target_ack_date->toDateString(), 'ack is received + 2 days');
        $this->assertSame('2026-11-16', $complaint->target_resolve_date->toDateString(), 'resolve is received + 15 days');
    }

    #[Test]
    public function the_fallback_is_reported_by_slaFor_when_nothing_is_configured(): void
    {
        $this->assertSame(
            ['ack_days' => 2, 'resolve_days' => 15],
            Complaint::slaFor($this->agency)
        );
    }

    // -----------------------------------------------------------------
    // Acceptance: the SLA is agency-configurable
    // -----------------------------------------------------------------

    #[Test]
    public function a_configured_sla_drives_the_target_dates(): void
    {
        $this->configureSla(1, 7);

        $complaint = $this->makeComplaint();

        $this->assertSame('2026-11-02', $complaint->target_ack_date->toDateString());
        $this->assertSame('2026-11-08', $complaint->target_resolve_date->toDateString());
    }

    #[Test]
    public function each_agency_uses_its_own_sla(): void
    {
        $this->configureSla(1, 7);

        $other = Agency::factory()->create();
        $otherSettings = (array) ($other->settings ?? []);
        $otherSettings[Complaint::SLA_SETTINGS_KEY] = ['ack_days' => 5, 'resolve_days' => 30];
        $other->update(['settings' => $otherSettings]);

        $mine = $this->makeComplaint(['ref_no' => 'CMP-001']);

        app()->instance('tenant_agency', $other);
        $theirs = Complaint::withoutGlobalScopes()->create([
            'agency_id'        => $other->id,
            'ref_no'           => 'CMP-001',
            'complainant_name' => 'Other agency complainant',
            'description'      => 'Different SLA',
            'date_received'    => '2026-11-01',
        ]);

        $this->assertSame('2026-11-02', $mine->target_ack_date->toDateString());
        $this->assertSame('2026-11-06', $theirs->target_ack_date->toDateString());
        $this->assertSame('2026-11-08', $mine->target_resolve_date->toDateString());
        $this->assertSame('2026-12-01', $theirs->target_resolve_date->toDateString());
    }

    #[Test]
    public function a_partially_configured_sla_fills_the_missing_half_from_the_default(): void
    {
        $this->configureSla(4, null);

        $complaint = $this->makeComplaint();

        $this->assertSame('2026-11-05', $complaint->target_ack_date->toDateString(), 'configured 4 days');
        $this->assertSame('2026-11-16', $complaint->target_resolve_date->toDateString(), 'default 15 days');
    }

    #[Test]
    public function a_zero_or_nonsense_sla_falls_back_rather_than_creating_an_instant_deadline(): void
    {
        // 0 is much more likely a half-filled form than "acknowledge instantly".
        $this->configureSla(0, -3);

        $this->assertSame(
            ['ack_days' => 2, 'resolve_days' => 15],
            Complaint::slaFor($this->agency->fresh())
        );

        $settings = (array) ($this->agency->fresh()->settings ?? []);
        $settings[Complaint::SLA_SETTINGS_KEY] = ['ack_days' => 'soon', 'resolve_days' => 'later'];
        $this->agency->update(['settings' => $settings]);

        $this->assertSame(
            ['ack_days' => 2, 'resolve_days' => 15],
            Complaint::slaFor($this->agency->fresh())
        );
    }

    // -----------------------------------------------------------------
    // The deadlines are frozen, not recomputed
    // -----------------------------------------------------------------

    #[Test]
    public function changing_the_agency_sla_does_not_move_the_deadline_of_an_existing_complaint(): void
    {
        $complaint = $this->makeComplaint();
        $this->assertSame('2026-11-03', $complaint->target_ack_date->toDateString());

        // The agency later widens its window.
        $this->configureSla(10, 40);

        // Touch a real date attribute so the target dates are in play on the
        // save. Updating an unrelated column is not enough: Eloquent only
        // writes dirty attributes, so the stored targets would never be
        // re-derived and the test could not fail whatever the guard did.
        $complaint->update(['acknowledged_at' => now()]);
        $complaint->refresh();

        $this->assertSame('2026-11-03', $complaint->target_ack_date->toDateString(), 'the promise made on arrival stands');
        $this->assertSame('2026-11-16', $complaint->target_resolve_date->toDateString());
    }

    #[Test]
    public function an_explicitly_supplied_target_date_is_respected(): void
    {
        $complaint = $this->makeComplaint(['target_resolve_date' => '2026-12-31']);

        $this->assertSame('2026-12-31', $complaint->target_resolve_date->toDateString());
        // The half that was not supplied still comes from the SLA.
        $this->assertSame('2026-11-03', $complaint->target_ack_date->toDateString());
    }

    /**
     * `date_received` is NOT NULL by design: a complaint with no known arrival
     * date cannot have an SLA clock run against it, and a register entry whose
     * clock has no start is not auditable. The guard in applyTargetDates()
     * still exists so the model degrades safely if the column is ever made
     * nullable.
     */
    #[Test]
    public function a_received_date_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Complaint::withoutGlobalScopes()->create([
            'agency_id'        => $this->agency->id,
            'ref_no'           => 'CMP-NO-DATE',
            'complainant_name' => 'No date given',
            'description'      => 'Arrived by phone, date to be confirmed.',
            'date_received'    => null,
        ]);
    }

    // -----------------------------------------------------------------
    // Overdue flags
    // -----------------------------------------------------------------

    #[Test]
    public function an_unacknowledged_complaint_past_its_ack_date_is_ack_overdue(): void
    {
        $past   = $this->makeComplaint(['ref_no' => 'CMP-PAST', 'date_received' => now()->subDays(10)->toDateString()]);
        $future = $this->makeComplaint(['ref_no' => 'CMP-FUTURE', 'date_received' => now()->toDateString()]);

        $this->assertTrue($past->is_ack_overdue);
        $this->assertFalse($future->is_ack_overdue);
    }

    #[Test]
    public function acknowledging_stops_the_ack_overdue_flag(): void
    {
        $complaint = $this->makeComplaint([
            'date_received'   => now()->subDays(10)->toDateString(),
            'acknowledged_at' => now(),
            'status'          => Complaint::STATUS_ACKNOWLEDGED,
        ]);

        $this->assertFalse($complaint->is_ack_overdue);
    }

    #[Test]
    public function a_resolved_complaint_is_never_resolve_overdue(): void
    {
        $complaint = $this->makeComplaint([
            'date_received' => now()->subDays(30)->toDateString(),
            'status'        => Complaint::STATUS_RESOLVED,
            'resolved_at'   => now(),
        ]);

        $this->assertFalse($complaint->is_resolve_overdue);
    }

    #[Test]
    public function a_live_complaint_past_its_resolve_date_is_resolve_overdue(): void
    {
        $complaint = $this->makeComplaint([
            'date_received' => now()->subDays(30)->toDateString(),
            'status'        => Complaint::STATUS_INVESTIGATING,
        ]);

        $this->assertTrue($complaint->is_resolve_overdue);
        $this->assertLessThan(0, $complaint->days_to_resolve, 'should be negative when overdue');
    }

    // -----------------------------------------------------------------
    // Register rules
    // -----------------------------------------------------------------

    #[Test]
    public function ref_no_is_unique_per_agency_but_reusable_across_agencies(): void
    {
        $this->makeComplaint(['ref_no' => 'CMP-001']);

        $other = Agency::factory()->create();
        $second = Complaint::withoutGlobalScopes()->create([
            'agency_id'        => $other->id,
            'ref_no'           => 'CMP-001',
            'complainant_name' => 'Different agency, same ref',
            'description'      => 'Allowed',
            'date_received'    => '2026-11-01',
        ]);
        $this->assertNotNull($second->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->makeComplaint(['ref_no' => 'CMP-001', 'complainant_name' => 'Duplicate']);
    }

    #[Test]
    public function a_complaint_is_scoped_to_its_tenant(): void
    {
        $this->makeComplaint();

        app()->instance('tenant_agency', Agency::factory()->withQms()->create());

        $this->assertSame(0, Complaint::query()->count());
    }

    #[Test]
    public function writing_a_complaint_records_an_audit_event_labelled_with_its_reference(): void
    {
        $user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);
        $this->actingAs($user);

        $complaint = $this->makeComplaint();

        $this->assertDatabaseHas('audit_events', [
            'subject_type'  => Complaint::class,
            'subject_id'    => $complaint->id,
            'subject_label' => 'CMP-001',
            'action'        => 'created',
        ]);
    }

    #[Test]
    public function every_status_has_a_label_and_a_badge(): void
    {
        foreach (Complaint::STATUSES as $status) {
            $complaint = $this->makeComplaint(['ref_no' => 'CMP-' . $status, 'status' => $status]);
            $this->assertArrayHasKey($status, Complaint::STATUS_LABELS);
            $this->assertNotEmpty($complaint->status_label);
            $this->assertNotEmpty($complaint->status_badge);
        }
    }

    #[Test]
    public function the_applicant_link_is_optional(): void
    {
        // An employer or internal complainant has no applicant record.
        $complaint = $this->makeComplaint([
            'complainant_type' => Complaint::TYPE_EMPLOYER,
            'applicant_id'     => null,
        ]);

        $this->assertNull($complaint->applicant_id);
        $this->assertNull($complaint->applicant);
        $this->assertSame(Complaint::TYPE_EMPLOYER, $complaint->complainant_type);
    }
}
