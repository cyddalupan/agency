<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\AuditFinding;
use App\Models\InternalAudit;
use App\Models\NonConformity;
use App\Models\User;
use App\Services\AuditFindingToNonConformity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * QMS-17 (card 6afa0dae821b259e5f9fd4d4).
 *
 * The card's acceptance is one sentence: a finding can spawn a linked NC and
 * the relationship is visible from both sides. Most of this file is that
 * sentence, plus the surrounding numbering that would otherwise let two
 * records drift apart.
 */
class InternalAuditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        $this->auditor = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => User::QMS_ROLES[0] ?? 'qms_manager',
        ]);

        // The tenant scope keys off this binding; without it every query here
        // silently returns nothing.
        app()->instance('tenant_agency', $this->agency);
    }

    private function audit(array $overrides = []): InternalAudit
    {
        return InternalAudit::create(array_merge([
            'agency_id'        => $this->agency->id,
            'ref_no'           => InternalAudit::suggestNextRef($this->agency->id),
            'title'            => 'Payroll process audit',
            'scope'            => 'Payroll',
            'criteria'         => 'Finance procedure 4.2',
            'scheduled_date'   => '2026-11-10',
            'conducted_date'   => '2026-11-12',
            'lead_auditor_id'  => $this->auditor->id,
            'status'           => InternalAudit::STATUS_IN_PROGRESS,
            'created_by'       => $this->auditor->id,
        ], $overrides));
    }

    private function finding(InternalAudit $audit, array $overrides = []): AuditFinding
    {
        return AuditFinding::create(array_merge([
            'agency_id'         => $this->agency->id,
            'internal_audit_id' => $audit->id,
            'finding_no'        => AuditFinding::suggestNextNo($audit->id),
            'type'              => AuditFinding::TYPE_MAJOR_NC,
            'clause_reference'  => '4.2.1',
            'description'       => 'Monthly reconciliation was not signed off.',
            'evidence'          => 'Sample of three months, none signed.',
            'created_by'        => $this->auditor->id,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // The card's acceptance criterion
    // ---------------------------------------------------------------------

    public function test_a_finding_can_spawn_a_linked_non_conformity(): void
    {
        $audit = $this->audit();
        $finding = $this->finding($audit);

        $this->assertNull($finding->non_conformity_id);

        $nc = app(AuditFindingToNonConformity::class)->spawn($finding, $this->auditor->id);
        $finding->refresh();

        $this->assertInstanceOf(NonConformity::class, $nc);
        $this->assertSame($nc->id, $finding->non_conformity_id);
        $this->assertSame($nc->id, $finding->nonConformity->id);
    }

    public function test_the_relationship_is_visible_from_the_non_conformity_side(): void
    {
        $audit = $this->audit();
        $finding = $this->finding($audit);

        $nc = app(AuditFindingToNonConformity::class)->spawn($finding, $this->auditor->id);
        $nc->refresh();

        $this->assertNotNull($nc->auditFinding);
        $this->assertSame($finding->id, $nc->auditFinding->id);
        $this->assertTrue($nc->cameFromAudit());
        $this->assertSame($audit->id, $nc->auditFinding->internalAudit->id);
    }

    public function test_the_spawned_non_conformity_carries_the_findings_content(): void
    {
        $audit = $this->audit();
        $finding = $this->finding($audit);

        $nc = app(AuditFindingToNonConformity::class)->spawn($finding, $this->auditor->id);

        $this->assertSame('Monthly reconciliation was not signed off.', $nc->description);
        $this->assertSame('4.2.1', $nc->requirement_breached);
        $this->assertSame(NonConformity::SOURCE_AUDIT, $nc->source);
        $this->assertSame($this->agency->id, $nc->agency_id);
        $this->assertSame(NonConformity::STATUS_OPEN, $nc->status);
        $this->assertSame($nc->id, $finding->fresh()->non_conformity_id);
    }

    public function test_a_major_finding_spawns_a_major_non_conformity(): void
    {
        $finding = $this->finding($this->audit(['status' => InternalAudit::STATUS_COMPLETED]));

        $nc = app(AuditFindingToNonConformity::class)->spawn($finding, $this->auditor->id);

        $this->assertSame(NonConformity::SEVERITY_MAJOR, $nc->severity);
    }

    public function test_an_observation_spawns_a_minor_non_conformity(): void
    {
        $finding = $this->finding($this->audit(), ['type' => AuditFinding::TYPE_OBSERVATION]);

        $nc = app(AuditFindingToNonConformity::class)->spawn($finding, $this->auditor->id);

        $this->assertSame(NonConformity::SEVERITY_MINOR, $nc->severity);
    }

    public function test_a_finding_cannot_spawn_a_second_non_conformity(): void
    {
        $finding = $this->finding($this->audit());
        $service = app(AuditFindingToNonConformity::class);

        $service->spawn($finding, $this->auditor->id);

        $this->expectException(ValidationException::class);

        $service->spawn($finding->fresh(), $this->auditor->id);
    }

    public function test_a_failed_spawn_does_not_create_a_second_non_conformity(): void
    {
        $finding = $this->finding($this->audit());
        $service = app(AuditFindingToNonConformity::class);

        $first = $service->spawn($finding, $this->auditor->id);

        $countBefore = NonConformity::count();

        try {
            $service->spawn($finding->fresh(), $this->auditor->id);
            $this->fail('A second spawn should have been refused.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame($countBefore, NonConformity::count());
        $this->assertSame($first->id, $finding->fresh()->non_conformity_id);
    }

    // ---------------------------------------------------------------------
    // Numbering
    // ---------------------------------------------------------------------

    public function test_audit_references_increment(): void
    {
        $first = $this->audit();
        $second = $this->audit(['ref_no' => InternalAudit::suggestNextRef($this->agency->id)]);

        $this->assertSame('AUD-001', $first->ref_no);
        $this->assertSame('AUD-002', $second->ref_no);
    }

    public function test_finding_numbers_increment_within_one_audit(): void
    {
        $audit = $this->audit();
        $other = $this->audit(['ref_no' => InternalAudit::suggestNextRef($this->agency->id)]);

        $f1 = $this->finding($audit);
        $f2 = $this->finding($audit, ['finding_no' => AuditFinding::suggestNextNo($audit->id)]);
        $f3 = $this->finding($other);

        $this->assertSame('F-1', $f1->finding_no);
        $this->assertSame('F-2', $f2->finding_no);
        // A different audit starts again at F-1.
        $this->assertSame('F-1', $f3->finding_no);
    }

    // ---------------------------------------------------------------------
    // The rest of the shape
    // ---------------------------------------------------------------------

    public function test_an_audit_lists_its_findings(): void
    {
        $audit = $this->audit();
        $this->finding($audit);
        $this->finding($audit, ['finding_no' => 'F-2']);

        $this->assertCount(2, $audit->fresh()->findings);
    }

    public function test_a_soft_deleted_finding_disappears_from_the_audit(): void
    {
        $audit = $this->audit();
        $finding = $this->finding($audit);

        $finding->delete();

        $this->assertCount(0, $audit->fresh()->findings);
        $this->assertCount(1, $audit->fresh()->findings()->withTrashed()->get());
    }

    public function test_a_finding_survives_its_audit_being_soft_deleted(): void
    {
        $audit = $this->audit();
        $finding = $this->finding($audit);

        $audit->delete();

        $this->assertNotNull(AuditFinding::find($finding->id));
    }

    public function test_deleting_the_non_conformity_clears_the_findings_link(): void
    {
        $finding = $this->finding($this->audit());
        $nc = app(AuditFindingToNonConformity::class)->spawn($finding, $this->auditor->id);

        $nc->forceDelete();

        $this->assertNull($finding->fresh()->non_conformity_id);
    }

    public function test_an_audit_reports_its_status_label_and_badge(): void
    {
        $audit = $this->audit(['status' => InternalAudit::STATUS_PLANNED]);

        $this->assertSame('Planned', $audit->status_label);
        $this->assertSame('badge-ghost', $audit->status_badge);
        $this->assertFalse($audit->isClosed());
    }

    public function test_a_completed_audit_can_be_closed(): void
    {
        $audit = $this->audit(['status' => InternalAudit::STATUS_COMPLETED]);

        $audit->update(['status' => InternalAudit::STATUS_CLOSED]);

        $this->assertTrue($audit->fresh()->isClosed());
    }

    public function test_a_finding_reports_its_type_label_and_badge(): void
    {
        $finding = $this->finding($this->audit(), ['type' => AuditFinding::TYPE_MAJOR_NC]);

        $this->assertSame('Major non-conformity', $finding->type_label);
        $this->assertSame('badge-error', $finding->type_badge);
        $this->assertFalse($finding->hasNonConformity());
    }

    public function test_audits_are_scoped_to_their_agency(): void
    {
        $this->audit();

        $other = Agency::factory()->create();
        $otherAuditor = User::factory()->create(['agency_id' => $other->id]);

        InternalAudit::create([
            'agency_id'       => $other->id,
            'ref_no'          => 'AUD-001',
            'title'           => 'Someone else\'s audit',
            'lead_auditor_id' => $otherAuditor->id,
            'status'          => InternalAudit::STATUS_PLANNED,
        ]);

        $this->assertSame(1, InternalAudit::count());
        $this->assertSame('Payroll process audit', InternalAudit::first()->title);
    }
}
