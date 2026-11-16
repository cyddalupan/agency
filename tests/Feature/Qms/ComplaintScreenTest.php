<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-14 (card 6afa0dab2a673e08b88cdd67) — complaint intake, register and
 * resolution screens.
 *
 * Acceptance from the card: a full flow test of complaint received →
 * acknowledged → resolved, with the overdue badge appearing once a target
 * date has passed.
 */
class ComplaintScreenTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->manager = $this->userWithType('qms_manager');
    }

    private function userWithType(string $type): User
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ]);

        return $user;
    }

    private function makeComplaint(array $overrides = []): Complaint
    {
        return Complaint::withoutGlobalScopes()->create(array_merge([
            'agency_id'        => $this->agency->id,
            'ref_no'           => 'CMP-' . random_int(100, 999),
            'complainant_name' => 'Juan Dela Cruz',
            'complainant_type' => Complaint::TYPE_APPLICANT,
            'description'      => 'Fee was charged twice.',
            'date_received'    => now()->toDateString(),
            'status'           => Complaint::STATUS_OPEN,
        ], $overrides));
    }

    // -----------------------------------------------------------------
    // Acceptance: the full lifecycle
    // -----------------------------------------------------------------

    #[Test]
    public function the_full_flow_from_received_to_resolved(): void
    {
        $this->actingAs($this->manager);

        // 1. Received — through the real intake form.
        $this->post(route('complaints.store'), [
            'ref_no'           => 'CMP-100',
            'complainant_name' => 'Maria Santos',
            'complainant_type' => Complaint::TYPE_APPLICANT,
            'category'         => 'placement fee',
            'description'      => 'Was quoted one fee and billed another.',
            'date_received'    => '2026-11-01',
        ])->assertRedirect();

        $complaint = Complaint::withoutGlobalScopes()->where('ref_no', 'CMP-100')->firstOrFail();

        $this->assertSame(Complaint::STATUS_OPEN, $complaint->status);
        $this->assertSame('2026-11-03', $complaint->target_ack_date->toDateString(), 'ack deadline derived from the SLA');
        $this->assertSame('2026-11-16', $complaint->target_resolve_date->toDateString(), 'resolve deadline derived from the SLA');

        // 2. Acknowledged.
        $this->post(route('complaints.acknowledge', $complaint))
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();
        $this->assertSame(Complaint::STATUS_ACKNOWLEDGED, $complaint->status);
        $this->assertNotNull($complaint->acknowledged_at, 'the ack clock is stamped');

        // 3. Resolved, with the outcome written down.
        $this->post(route('complaints.resolve', $complaint), [
            'resolution' => 'Overcharge refunded and the fee schedule re-issued to the complainant.',
        ])->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();
        $this->assertSame(Complaint::STATUS_RESOLVED, $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
        $this->assertStringContainsString('refunded', (string) $complaint->resolution);
    }

    #[Test]
    public function the_overdue_badge_appears_when_the_target_date_has_passed(): void
    {
        // Received three weeks ago under the default 2/15 window: both clocks
        // are long past.
        $complaint = $this->makeComplaint([
            'date_received' => now()->subDays(21)->toDateString(),
        ]);

        $this->assertTrue($complaint->is_ack_overdue, 'ack is overdue');
        $this->assertTrue($complaint->is_resolve_overdue, 'resolve is overdue');

        $this->actingAs($this->manager)
            ->get(route('complaints.show', $complaint))
            ->assertOk()
            ->assertSee('acknowledgement overdue')
            ->assertSee('resolution overdue');

        $this->actingAs($this->manager)
            ->get(route('complaints.index'))
            ->assertOk()
            ->assertSee('ack-overdue-' . $complaint->id, false);
    }

    #[Test]
    public function the_overdue_badge_is_absent_for_a_complaint_inside_its_window(): void
    {
        $complaint = $this->makeComplaint(['date_received' => now()->toDateString()]);

        $this->assertFalse($complaint->is_ack_overdue);
        $this->assertFalse($complaint->is_resolve_overdue);

        $this->actingAs($this->manager)
            ->get(route('complaints.show', $complaint))
            ->assertOk()
            ->assertDontSee('acknowledgement overdue')
            ->assertDontSee('resolution overdue');
    }

    #[Test]
    public function acknowledging_stops_the_ack_overdue_badge(): void
    {
        // 10 days ago under the default 2/15 window: the 2-day ack deadline has
        // passed but the 15-day resolve deadline has not.
        $complaint = $this->makeComplaint(['date_received' => now()->subDays(10)->toDateString()]);
        $this->assertTrue($complaint->is_ack_overdue);
        $this->assertFalse($complaint->is_resolve_overdue, 'the resolve window is still open at ten days');

        $this->actingAs($this->manager)->post(route('complaints.acknowledge', $complaint));

        $complaint->refresh();
        $this->assertFalse($complaint->is_ack_overdue, 'once acknowledged the ack clock no longer runs');
    }

    #[Test]
    public function resolving_stops_the_resolve_overdue_badge(): void
    {
        $complaint = $this->makeComplaint(['date_received' => now()->subDays(30)->toDateString()]);
        $this->assertTrue($complaint->is_resolve_overdue);

        $this->actingAs($this->manager)->post(route('complaints.resolve', $complaint), [
            'resolution' => 'Refunded.',
        ]);

        $complaint->refresh();
        $this->assertFalse($complaint->is_resolve_overdue, 'a resolved complaint is never resolve overdue');
    }

    // -----------------------------------------------------------------
    // The register and tabs
    // -----------------------------------------------------------------

    #[Test]
    public function the_register_lists_complaints(): void
    {
        $this->makeComplaint(['ref_no' => 'CMP-500', 'complainant_name' => 'Listed Person']);

        $this->actingAs($this->manager)
            ->get(route('complaints.index'))
            ->assertOk()
            ->assertSee('CMP-500')
            ->assertSee('Listed Person');
    }

    #[Test]
    public function an_empty_register_says_so(): void
    {
        $this->actingAs($this->manager)
            ->get(route('complaints.index'))
            ->assertOk()
            ->assertSee('complaint-empty', false);
    }

    #[Test]
    public function the_status_tab_filters_the_register(): void
    {
        $this->makeComplaint(['ref_no' => 'CMP-OPEN', 'status' => Complaint::STATUS_OPEN]);
        $this->makeComplaint(['ref_no' => 'CMP-RES', 'status' => Complaint::STATUS_RESOLVED]);

        $response = $this->actingAs($this->manager)
            ->get(route('complaints.index', ['status' => Complaint::STATUS_OPEN]))
            ->assertOk();

        $response->assertSee('CMP-OPEN');
        $response->assertDontSee('CMP-RES');
    }

    #[Test]
    public function the_overdue_tab_lists_only_live_late_complaints(): void
    {
        $late = $this->makeComplaint([
            'ref_no'        => 'CMP-LATE',
            'date_received' => now()->subDays(30)->toDateString(),
        ]);
        $this->makeComplaint([
            'ref_no'        => 'CMP-FRESH',
            'date_received' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->manager)
            ->get(route('complaints.index', ['status' => 'overdue']))
            ->assertOk();

        $response->assertSee('CMP-LATE');
        $response->assertDontSee('CMP-FRESH');
    }

    #[Test]
    public function a_resolved_complaint_drops_out_of_the_overdue_tab(): void
    {
        $this->makeComplaint([
            'ref_no'        => 'CMP-DONE',
            'date_received' => now()->subDays(60)->toDateString(),
            'status'        => Complaint::STATUS_RESOLVED,
            'resolved_at'   => now(),
        ]);

        $this->actingAs($this->manager)
            ->get(route('complaints.index', ['status' => 'overdue']))
            ->assertOk()
            ->assertDontSee('CMP-DONE');
    }

    // -----------------------------------------------------------------
    // Intake
    // -----------------------------------------------------------------

    #[Test]
    public function the_intake_form_shows_the_agency_sla(): void
    {
        $settings = (array) ($this->agency->settings ?? []);
        $settings[Complaint::SLA_SETTINGS_KEY] = ['ack_days' => 5, 'resolve_days' => 30];
        $this->agency->update(['settings' => $settings]);

        $this->actingAs($this->manager)
            ->get(route('complaints.create'))
            ->assertOk()
            ->assertSee('sla-ack', false)
            ->assertSee('5')
            ->assertSee('30');
    }

    #[Test]
    public function the_intake_form_suggests_the_next_reference(): void
    {
        $this->makeComplaint(['ref_no' => 'CMP-007']);

        $this->actingAs($this->manager)
            ->get(route('complaints.create'))
            ->assertOk()
            ->assertSee('CMP-008');
    }

    #[Test]
    public function a_duplicate_reference_is_rejected_within_the_agency(): void
    {
        $this->makeComplaint(['ref_no' => 'CMP-DUP']);

        $this->actingAs($this->manager)
            ->from(route('complaints.create'))
            ->post(route('complaints.store'), [
                'ref_no'           => 'CMP-DUP',
                'complainant_name' => 'Someone Else',
                'complainant_type' => Complaint::TYPE_APPLICANT,
                'description'      => 'Different complaint.',
                'date_received'    => now()->toDateString(),
            ])
            ->assertRedirect(route('complaints.create'))
            ->assertSessionHasErrors('ref_no');
    }

    #[Test]
    public function the_description_is_required(): void
    {
        $this->actingAs($this->manager)
            ->from(route('complaints.create'))
            ->post(route('complaints.store'), [
                'ref_no'           => 'CMP-NODESC',
                'complainant_name' => 'Someone',
                'complainant_type' => Complaint::TYPE_APPLICANT,
                'date_received'    => now()->toDateString(),
            ])
            ->assertSessionHasErrors('description');
    }

    // -----------------------------------------------------------------
    // Lifecycle rules are enforced, not merely offered
    // -----------------------------------------------------------------

    #[Test]
    public function a_complaint_cannot_be_resolved_without_resolution_text(): void
    {
        $complaint = $this->makeComplaint();

        $this->actingAs($this->manager)
            ->from(route('complaints.show', $complaint))
            ->post(route('complaints.resolve', $complaint), ['resolution' => ''])
            ->assertSessionHasErrors('resolution');

        $this->assertSame(Complaint::STATUS_OPEN, $complaint->fresh()->status, 'status did not move');
    }

    #[Test]
    public function an_open_complaint_cannot_be_closed(): void
    {
        $complaint = $this->makeComplaint(['status' => Complaint::STATUS_OPEN]);

        $this->actingAs($this->manager)
            ->from(route('complaints.show', $complaint))
            ->post(route('complaints.close', $complaint), ['closure_notes' => 'Looks fine'])
            ->assertRedirect(route('complaints.show', $complaint))
            ->assertSessionHas('error');

        $this->assertSame(Complaint::STATUS_OPEN, $complaint->fresh()->status);
    }

    #[Test]
    public function a_resolved_complaint_can_be_closed_with_notes(): void
    {
        $complaint = $this->makeComplaint([
            'status'      => Complaint::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution'  => 'Refunded.',
        ]);

        $this->actingAs($this->manager)
            ->post(route('complaints.close', $complaint), [
                'closure_notes' => 'Complainant confirmed the refund landed.',
            ])
            ->assertRedirect(route('complaints.show', $complaint));

        $complaint->refresh();
        $this->assertSame(Complaint::STATUS_CLOSED, $complaint->status);
        $this->assertStringContainsString('confirmed', (string) $complaint->closure_notes);
    }

    #[Test]
    public function a_closed_complaint_cannot_be_acknowledged(): void
    {
        $complaint = $this->makeComplaint(['status' => Complaint::STATUS_CLOSED]);

        $this->actingAs($this->manager)
            ->from(route('complaints.show', $complaint))
            ->post(route('complaints.acknowledge', $complaint))
            ->assertSessionHas('error');

        $this->assertSame(Complaint::STATUS_CLOSED, $complaint->fresh()->status);
    }

    #[Test]
    public function acknowledging_twice_keeps_the_first_timestamp(): void
    {
        $complaint = $this->makeComplaint();

        $this->actingAs($this->manager)->post(route('complaints.acknowledge', $complaint));
        $first = $complaint->fresh()->acknowledged_at;

        $this->travel(2)->hours();

        $this->actingAs($this->manager)->post(route('complaints.acknowledge', $complaint));
        $second = $complaint->fresh()->acknowledged_at;

        $this->assertTrue($first->equalTo($second), 're-acknowledging must not make a late ack look timely');
    }

    #[Test]
    public function investigating_stamps_the_acknowledgement_when_it_was_missed(): void
    {
        $complaint = $this->makeComplaint();
        $this->assertNull($complaint->acknowledged_at);

        $this->actingAs($this->manager)->post(route('complaints.investigate', $complaint));

        $complaint->refresh();
        $this->assertSame(Complaint::STATUS_INVESTIGATING, $complaint->status);
        $this->assertNotNull($complaint->acknowledged_at);
    }

    // -----------------------------------------------------------------
    // Tenancy and roles
    // -----------------------------------------------------------------

    #[Test]
    public function another_agencys_complaint_is_out_of_reach(): void
    {
        $other = Agency::factory()->create();
        $foreign = Complaint::withoutGlobalScopes()->create([
            'agency_id'        => $other->id,
            'ref_no'           => 'CMP-FOREIGN',
            'complainant_name' => 'Not Yours',
            'complainant_type' => Complaint::TYPE_APPLICANT,
            'description'      => 'x',
            'date_received'    => now()->toDateString(),
            'status'           => Complaint::STATUS_OPEN,
        ]);

        $this->actingAs($this->manager)
            ->get(route('complaints.show', $foreign))
            ->assertNotFound();
    }

    #[Test]
    public function an_accounting_user_cannot_reach_complaints(): void
    {
        $this->actingAs($this->userWithType('billing'))
            ->get(route('complaints.index'))
            ->assertForbidden();
    }

    #[Test]
    public function an_internal_auditor_can_read_but_not_handle(): void
    {
        $auditor = $this->userWithType('internal_auditor');
        $complaint = $this->makeComplaint();

        $this->actingAs($auditor)->get(route('complaints.index'))->assertOk();
        $this->actingAs($auditor)->get(route('complaints.show', $complaint))->assertOk();

        $this->actingAs($auditor)->get(route('complaints.create'))->assertForbidden();
        $this->actingAs($auditor)->post(route('complaints.acknowledge', $complaint))->assertForbidden();

        $this->assertSame(Complaint::STATUS_OPEN, $complaint->fresh()->status);
    }

    #[Test]
    public function the_auditor_does_not_see_the_handling_forms(): void
    {
        $auditor = $this->userWithType('internal_auditor');
        $complaint = $this->makeComplaint();

        $this->actingAs($auditor)
            ->get(route('complaints.show', $complaint))
            ->assertOk()
            ->assertSee('read-only-notice', false)
            ->assertDontSee('btn-acknowledge', false);
    }

    #[Test]
    public function a_qms_manager_sees_the_complaints_sidebar_link(): void
    {
        $this->actingAs($this->manager)
            ->get(route('complaints.index'))
            ->assertOk()
            ->assertSee(route('complaints.index'), false);
    }
}
