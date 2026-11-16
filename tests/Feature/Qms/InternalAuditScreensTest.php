<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\AuditFinding;
use App\Models\InternalAudit;
use App\Models\NonConformity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * QMS-18 (card 6afa0dafe7637ea026605c1c).
 *
 * The card's acceptance is the whole flow: plan an audit, conduct it, record
 * findings, raise an NC from one. So the first test here is that flow end to
 * end through the screens, and the rest are the edges — the role split, the
 * numbering, and the cases where raising an NC must not happen.
 */
class InternalAuditScreensTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $manager;

    private User $auditor;

    private User $billing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();

        $this->manager = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'qms_manager',
        ]);

        $this->auditor = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'internal_auditor',
        ]);

        $this->billing = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
    }

    private function plannedAudit(array $overrides = []): InternalAudit
    {
        return InternalAudit::create(array_merge([
            'agency_id'      => $this->agency->id,
            'ref_no'         => InternalAudit::suggestNextRef(),
            'title'          => 'Payroll process audit',
            'scope'          => 'Payroll',
            'criteria'       => 'Finance procedure 4.2',
            'scheduled_date' => '2026-11-10',
            'status'         => InternalAudit::STATUS_PLANNED,
            'created_by'     => $this->manager->id,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // The card's acceptance: plan -> conduct -> findings -> NC
    // ---------------------------------------------------------------------

    public function test_the_full_flow_from_plan_to_raised_non_conformity(): void
    {
        // 1. Plan the audit on the screens.
        $this->actingAs($this->manager)
            ->post(route('internal_audits.store'), [
                'ref_no'         => 'AUD-FLOW-1',
                'title'          => 'Payroll process audit',
                'scope'          => 'Payroll',
                'criteria'       => 'Finance procedure 4.2',
                'scheduled_date' => '2026-11-10',
                'status'         => 'planned',
            ])
            ->assertRedirect();

        $audit = InternalAudit::where('ref_no', 'AUD-FLOW-1')->firstOrFail();
        $this->assertSame(InternalAudit::STATUS_PLANNED, $audit->status);

        // 2. Conduct it by recording a finding. This is the auditor's own work.
        $this->actingAs($this->auditor)
            ->post(route('internal_audits.findings.store', $audit), [
                'type'             => 'major_nc',
                'clause_reference' => '4.2.1',
                'description'      => 'Monthly reconciliation was not signed off.',
                'evidence'         => 'Sampled three months, none signed.',
            ])
            ->assertRedirect(route('internal_audits.show', $audit));

        $audit->refresh();
        $this->assertSame(InternalAudit::STATUS_IN_PROGRESS, $audit->status,
            'Recording a finding on a planned audit should start it.');
        $this->assertNotNull($audit->conducted_date);

        $finding = AuditFinding::where('internal_audit_id', $audit->id)->sole();
        $this->assertSame('F-1', $finding->finding_no);

        // 3. Raise the non-conformity from it.
        $this->actingAs($this->auditor)
            ->post(route('internal_audits.findings.raise_nc', [$audit, $finding]))
            ->assertRedirect(route('internal_audits.show', $audit))
            ->assertSessionHas('success');

        $finding->refresh();
        $this->assertNotNull($finding->non_conformity_id);

        $nc = NonConformity::findOrFail($finding->non_conformity_id);
        $this->assertSame(NonConformity::SOURCE_AUDIT, $nc->source);
        $this->assertSame('Monthly reconciliation was not signed off.', $nc->description);
        $this->assertSame('4.2.1', $nc->requirement_breached);

        // 4. Both sides show the link.
        $this->actingAs($this->manager)
            ->get(route('internal_audits.show', $audit))
            ->assertOk()
            ->assertSee($nc->ref_no);

        $this->actingAs($this->manager)
            ->get(route('non_conformities.show', $nc))
            ->assertOk()
            ->assertSee($finding->finding_no);
    }

    // ---------------------------------------------------------------------
    // The screens themselves
    // ---------------------------------------------------------------------

    public function test_the_register_lists_audits(): void
    {
        $audit = $this->plannedAudit();

        $this->actingAs($this->manager)
            ->get(route('internal_audits.index'))
            ->assertOk()
            ->assertSee($audit->ref_no)
            ->assertSee('Payroll process audit');
    }

    public function test_the_register_filters_by_status(): void
    {
        $planned = $this->plannedAudit();
        $done = $this->plannedAudit([
            'ref_no' => 'AUD-002',
            'title'  => 'Recruitment audit',
            'status' => InternalAudit::STATUS_COMPLETED,
        ]);

        // Assert on the rendered rows, not on page text: the search placeholder
        // literally contains "AUD-001", so assertDontSee on the ref is a false
        // signal.
        $response = $this->actingAs($this->manager)
            ->get(route('internal_audits.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee($done->ref_no);

        $this->assertStringNotContainsString(
            'audit-row-' . $planned->id,
            $response->getContent(),
            'A planned audit must not appear when filtering to completed.'
        );
    }

    public function test_the_register_searches_title_and_scope(): void
    {
        $this->plannedAudit();
        $this->plannedAudit(['ref_no' => 'AUD-002', 'title' => 'Recruitment audit', 'scope' => 'Recruitment']);

        $this->actingAs($this->manager)
            ->get(route('internal_audits.index', ['q' => 'recruit']))
            ->assertOk()
            ->assertSee('Recruitment audit')
            ->assertDontSee('Payroll process audit');
    }

    public function test_the_create_form_suggests_the_next_reference(): void
    {
        $this->plannedAudit();

        $this->actingAs($this->manager)
            ->get(route('internal_audits.create'))
            ->assertOk()
            ->assertSee('AUD-002');
    }

    public function test_the_detail_screen_shows_scope_and_criteria(): void
    {
        $audit = $this->plannedAudit();

        $this->actingAs($this->manager)
            ->get(route('internal_audits.show', $audit))
            ->assertOk()
            ->assertSee('Payroll')
            ->assertSee('Finance procedure 4.2');
    }

    public function test_the_detail_screen_saves_plan_edits(): void
    {
        $audit = $this->plannedAudit();

        $this->actingAs($this->manager)
            ->put(route('internal_audits.update', $audit), [
                'title'  => 'Payroll audit, revised',
                'status' => InternalAudit::STATUS_COMPLETED,
            ])
            ->assertRedirect(route('internal_audits.show', $audit));

        $this->assertSame('Payroll audit, revised', $audit->fresh()->title);
        $this->assertSame(InternalAudit::STATUS_COMPLETED, $audit->fresh()->status);
    }

    public function test_a_duplicate_reference_is_refused(): void
    {
        $this->plannedAudit();

        $this->actingAs($this->manager)
            ->post(route('internal_audits.store'), [
                'ref_no' => 'AUD-001',
                'title'  => 'Another audit',
                'status' => 'planned',
            ])
            ->assertSessionHasErrors('ref_no');

        $this->assertSame(1, InternalAudit::count());
    }

    public function test_findings_are_removable_until_one_has_raised_a_non_conformity(): void
    {
        $audit = $this->plannedAudit();
        $finding = AuditFinding::create([
            'agency_id'         => $this->agency->id,
            'internal_audit_id' => $audit->id,
            'finding_no'        => 'F-1',
            'type'              => AuditFinding::TYPE_OBSERVATION,
            'description'       => 'Records kept in two places.',
        ]);

        $this->actingAs($this->manager)
            ->delete(route('internal_audits.findings.destroy', [$audit, $finding]))
            ->assertRedirect(route('internal_audits.show', $audit));

        $this->assertNull(AuditFinding::find($finding->id));
    }

    public function test_a_finding_that_raised_a_non_conformity_cannot_be_removed(): void
    {
        $audit = $this->plannedAudit();
        $finding = AuditFinding::create([
            'agency_id'         => $this->agency->id,
            'internal_audit_id' => $audit->id,
            'finding_no'        => 'F-1',
            'type'              => 'major_nc',
            'description'       => 'Reconciliation not signed.',
        ]);

        $this->actingAs($this->manager)
            ->post(route('internal_audits.findings.raise_nc', [$audit, $finding]));

        $this->actingAs($this->manager)
            ->delete(route('internal_audits.findings.destroy', [$audit, $finding]))
            ->assertSessionHas('error');

        $this->assertNotNull(AuditFinding::find($finding->id),
            'A finding that has raised an NC is evidence and must survive.');
    }

    public function test_raising_twice_is_refused_and_creates_no_second_non_conformity(): void
    {
        $audit = $this->plannedAudit();
        $finding = AuditFinding::create([
            'agency_id'         => $this->agency->id,
            'internal_audit_id' => $audit->id,
            'finding_no'        => 'F-1',
            'type'              => 'major_nc',
            'description'       => 'Reconciliation not signed.',
        ]);

        $this->actingAs($this->manager)
            ->post(route('internal_audits.findings.raise_nc', [$audit, $finding]));

        $this->actingAs($this->manager)
            ->post(route('internal_audits.findings.raise_nc', [$audit, $finding]))
            ->assertSessionHas('error');

        $this->assertSame(1, NonConformity::count());
    }

    public function test_a_closed_audit_refuses_new_findings(): void
    {
        $audit = $this->plannedAudit(['status' => InternalAudit::STATUS_CLOSED]);

        $this->actingAs($this->manager)
            ->post(route('internal_audits.findings.store', $audit), [
                'type'        => 'observation',
                'description' => 'Late finding.',
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, AuditFinding::count());
    }

    // ---------------------------------------------------------------------
    // The role split
    // ---------------------------------------------------------------------

    public function test_an_auditor_can_read_but_cannot_plan(): void
    {
        $audit = $this->plannedAudit();

        $this->actingAs($this->auditor)->get(route('internal_audits.index'))->assertOk();
        $this->actingAs($this->auditor)->get(route('internal_audits.show', $audit))->assertOk();

        $this->actingAs($this->auditor)->get(route('internal_audits.create'))->assertForbidden();
        $this->actingAs($this->auditor)
            ->post(route('internal_audits.store'), ['ref_no' => 'AUD-9', 'title' => 'x', 'status' => 'planned'])
            ->assertForbidden();
        $this->actingAs($this->auditor)
            ->put(route('internal_audits.update', $audit), ['title' => 'x', 'status' => 'closed'])
            ->assertForbidden();
    }

    public function test_an_auditor_can_record_findings_on_an_audit_that_exists(): void
    {
        $audit = $this->plannedAudit();

        $this->actingAs($this->auditor)
            ->post(route('internal_audits.findings.store', $audit), [
                'type'        => 'observation',
                'description' => 'Two files disagreed.',
            ])
            ->assertRedirect();

        $this->assertSame(1, AuditFinding::count());
    }

    public function test_accounting_cannot_see_internal_audits(): void
    {
        $this->actingAs($this->billing)->get(route('internal_audits.index'))->assertForbidden();
    }

    public function test_the_new_audit_button_is_hidden_from_an_auditor(): void
    {
        $this->actingAs($this->auditor)
            ->get(route('internal_audits.index'))
            ->assertOk()
            ->assertDontSee('audit-new', false);
    }
}
