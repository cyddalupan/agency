<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\Complaint;
use App\Models\ControlledDocument;
use App\Models\CorrectiveAction;
use App\Models\InternalAudit;
use App\Models\NonConformity;
use App\Models\Risk;
use App\Models\User;
use App\Services\ComplianceDeadlineFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * QMS-19 (card 6afa0db0a24da820db1980db).
 *
 * The card's acceptance: seeded fixtures in each source appear in the right
 * bucket, and overdue sorts correctly. The bucketing rule is tested directly
 * against the service (that is where it lives), and the screens are tested
 * through the route so a broken adapter is caught too.
 */
class ComplianceCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->manager = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'qms_manager',
        ]);

        app()->instance('tenant_agency', $this->agency);

        // A fixed "now" so the buckets are not a race against the clock.
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function feed(): ComplianceDeadlineFeed
    {
        return app(ComplianceDeadlineFeed::class);
    }

    // ---------------------------------------------------------------------
    // The bucketing rule
    // ---------------------------------------------------------------------

    public function test_the_four_buckets_are_decided_by_calendar_day(): void
    {
        $now = Carbon::parse('2026-11-16 10:00:00'); // a Monday
        $feed = $this->feed();

        $this->assertSame('overdue', $feed->bucketFor(Carbon::parse('2026-11-15'), $now));
        $this->assertSame('this_week', $feed->bucketFor(Carbon::parse('2026-11-16'), $now), 'Today is not overdue.');
        $this->assertSame('this_week', $feed->bucketFor(Carbon::parse('2026-11-20'), $now));
        $this->assertSame('this_month', $feed->bucketFor(Carbon::parse('2026-11-25'), $now));
        $this->assertSame('later', $feed->bucketFor(Carbon::parse('2026-12-05'), $now));
    }

    public function test_something_due_today_is_not_overdue(): void
    {
        $this->assertSame(
            'this_week',
            $this->feed()->bucketFor(Carbon::parse('2026-11-16 00:01'), Carbon::parse('2026-11-16 23:59'))
        );
    }

    public function test_overdue_sorts_first_and_most_overdue_comes_top(): void
    {
        $this->makeNc('NC-OLD', '2026-11-01');
        $this->makeNc('NC-NEW', '2026-11-15');
        $this->makeNc('NC-FUTURE', '2026-11-20');

        $refs = $this->feed()->all()->pluck('ref')->all();

        $this->assertSame(['NC-OLD', 'NC-NEW', 'NC-FUTURE'], $refs,
            'Overdue first, then by date.');
        $this->assertSame('overdue', $this->feed()->all()->first()['bucket']);
    }

    // ---------------------------------------------------------------------
    // Every source appears
    // ---------------------------------------------------------------------

    public function test_a_controlled_document_review_appears(): void
    {
        ControlledDocument::create([
            'agency_id'        => $this->agency->id,
            'code'             => 'DOC-001',
            'title'            => 'Finance procedure 4.2',
            'status'           => ControlledDocument::STATUS_APPROVED,
            'next_review_date' => '2026-11-10',
        ]);

        $items = $this->feed()->all();
        $this->assertCount(1, $items);
        $this->assertSame('controlled_document', $items->first()['source']);
        $this->assertSame('overdue', $items->first()['bucket']);
    }

    public function test_an_open_risk_appears_but_a_closed_one_does_not(): void
    {
        Risk::create([
            'agency_id'   => $this->agency->id,
            'ref_no'      => 'RISK-001',
            'title'       => 'Single approver',
            'status'      => Risk::STATUS_OPEN,
            'review_date' => '2026-11-12',
        ]);
        Risk::create([
            'agency_id'   => $this->agency->id,
            'ref_no'      => 'RISK-002',
            'title'       => 'Retired risk',
            'status'      => Risk::STATUS_CLOSED,
            'review_date' => '2026-11-12',
        ]);

        $items = $this->feed()->all();

        $this->assertCount(1, $items);
        $this->assertSame('Single approver', $items->first()['title']);
    }

    public function test_an_open_complaint_contributes_both_targets(): void
    {
        Complaint::create([
            'agency_id'            => $this->agency->id,
            'ref_no'               => 'CMP-001',
            'complainant_name'     => 'Someone',
            'category'             => 'Billing dispute',
            'description'          => 'Charged twice.',
            'date_received'        => '2026-11-10',
            'target_ack_date'      => '2026-11-12',
            'target_resolve_date'  => '2026-11-20',
            'status'               => Complaint::STATUS_OPEN,
        ]);

        $items = $this->feed()->all();

        $this->assertCount(2, $items);
        $this->assertEqualsCanonicalizing(
            ['Acknowledge complaint', 'Resolve complaint'],
            $items->pluck('source_label')->all()
        );
    }

    public function test_a_resolved_complaint_is_not_chased(): void
    {
        Complaint::create([
            'agency_id'           => $this->agency->id,
            'ref_no'              => 'CMP-002',
            'complainant_name'    => 'Someone',
            'category'            => 'Billing dispute',
            'description'         => 'Charged twice.',
            'date_received'       => '2026-11-01',
            'target_resolve_date' => '2026-11-05',
            'status'              => Complaint::STATUS_RESOLVED,
        ]);

        $this->assertCount(0, $this->feed()->all());
    }

    public function test_a_non_conformity_appears_until_it_is_closed(): void
    {
        $this->makeNc('NC-OPEN', '2026-11-18');
        // The closure gate is a real rule: you cannot open a record straight
        // into `closed`. Create it open, then write the status past the gate
        // the way a historical import would, because this test is about the
        // calendar, not about closure.
        $closed = NonConformity::create([
            'agency_id'     => $this->agency->id,
            'ref_no'        => 'NC-CLOSED',
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => '2026-11-01',
            'description'   => 'Closed already.',
            'severity'      => NonConformity::SEVERITY_MINOR,
            'status'        => NonConformity::STATUS_OPEN,
            'due_date'      => '2026-11-18',
        ]);
        \Illuminate\Support\Facades\DB::table('non_conformities')
            ->where('id', $closed->id)
            ->update(['status' => NonConformity::STATUS_CLOSED]);

        $items = $this->feed()->all();

        $this->assertCount(1, $items);
        $this->assertSame('NC-OPEN', $items->first()['ref']);
    }

    public function test_a_corrective_action_appears_until_it_is_completed(): void
    {
        $nc = $this->makeNc('NC-001', '2026-12-01');

        CorrectiveAction::create([
            'agency_id'         => $this->agency->id,
            'non_conformity_id' => $nc->id,
            'action_text'       => 'Issue a signed checklist',
            'due_date'          => '2026-11-20',
            'status'            => 'open',
        ]);
        CorrectiveAction::create([
            'agency_id'         => $this->agency->id,
            'non_conformity_id' => $nc->id,
            'action_text'       => 'Already done',
            'due_date'          => '2026-11-20',
            'completed_date'    => '2026-11-15',
            'status'            => 'complete',
        ]);

        $items = $this->feed()->all()->where('source', 'corrective_action');

        $this->assertCount(1, $items);
        $this->assertSame('Issue a signed checklist', $items->first()['title']);
    }

    public function test_a_scheduled_audit_appears_until_it_is_conducted(): void
    {
        InternalAudit::create([
            'agency_id'      => $this->agency->id,
            'ref_no'         => 'AUD-001',
            'title'          => 'Payroll audit',
            'scheduled_date' => '2026-11-19',
            'status'         => InternalAudit::STATUS_PLANNED,
        ]);
        InternalAudit::create([
            'agency_id'      => $this->agency->id,
            'ref_no'         => 'AUD-002',
            'title'          => 'Done already',
            'scheduled_date' => '2026-11-19',
            'conducted_date' => '2026-11-15',
            'status'         => InternalAudit::STATUS_COMPLETED,
        ]);

        $items = $this->feed()->all();

        $this->assertCount(1, $items);
        $this->assertSame('AUD-001', $items->first()['ref']);
    }

    // ---------------------------------------------------------------------
    // The screen
    // ---------------------------------------------------------------------

    public function test_the_calendar_groups_every_source_into_the_right_bucket(): void
    {
        $this->makeNc('NC-LATE', '2026-11-09');           // overdue
        $this->makeNc('NC-SOON', '2026-11-18');           // this week
        $this->makeNc('NC-MONTH', '2026-11-27');          // this month
        $this->makeNc('NC-LATER', '2027-01-15');          // later

        $response = $this->actingAs($this->manager)
            ->get(route('compliance_calendar.index'))
            ->assertOk();

        $grouped = $this->feed()->grouped();
        $this->assertSame(['NC-LATE'], $grouped['overdue']->pluck('ref')->all());
        $this->assertSame(['NC-SOON'], $grouped['this_week']->pluck('ref')->all());
        $this->assertSame(['NC-MONTH'], $grouped['this_month']->pluck('ref')->all());
        $this->assertSame(['NC-LATER'], $grouped['later']->pluck('ref')->all());

        $response->assertSee('NC-LATE');
        $response->assertSee('NC-LATER');
    }

    public function test_the_calendar_shows_the_overdue_count(): void
    {
        $this->makeNc('NC-LATE-1', '2026-11-09');
        $this->makeNc('NC-LATE-2', '2026-11-10');

        $this->actingAs($this->manager)
            ->get(route('compliance_calendar.index'))
            ->assertOk()
            ->assertSee('overdue-count', false);
    }

    public function test_the_calendar_can_filter_by_source_without_losing_counts(): void
    {
        $this->makeNc('NC-001', '2026-11-10');
        Risk::create([
            'agency_id'   => $this->agency->id,
            'ref_no'      => 'RISK-FILTER',
            'title'       => 'Single approver',
            'status'      => Risk::STATUS_OPEN,
            'review_date' => '2026-11-10',
        ]);

        $this->actingAs($this->manager)
            ->get(route('compliance_calendar.index', ['source' => 'risk']))
            ->assertOk()
            ->assertSee('Single approver')
            ->assertDontSee('NC-001');
    }

    public function test_an_empty_calendar_says_so(): void
    {
        $this->actingAs($this->manager)
            ->get(route('compliance_calendar.index'))
            ->assertOk()
            ->assertSee('deadlines-empty', false);
    }

    public function test_accounting_cannot_see_the_calendar(): void
    {
        $billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);

        $this->actingAs($billing)
            ->get(route('compliance_calendar.index'))
            ->assertForbidden();
    }

    public function test_an_internal_auditor_can_read_the_calendar(): void
    {
        $auditor = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'internal_auditor',
        ]);

        $this->actingAs($auditor)
            ->get(route('compliance_calendar.index'))
            ->assertOk();
    }

    // ---------------------------------------------------------------------

    private function makeNc(string $ref, string $due): NonConformity
    {
        return NonConformity::create([
            'agency_id'     => $this->agency->id,
            'ref_no'        => $ref,
            'source'        => NonConformity::SOURCE_AUDIT,
            'detected_date' => '2026-11-01',
            'description'   => 'Reconciliation not signed.',
            'severity'      => NonConformity::SEVERITY_MINOR,
            'status'        => NonConformity::STATUS_OPEN,
            'due_date'      => $due,
        ]);
    }
}
