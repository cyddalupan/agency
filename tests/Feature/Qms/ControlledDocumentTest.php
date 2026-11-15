<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\ControlledDocument;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-06 (card 6afa0da3de9f235c9653dab7) — the document-control spine.
 *
 * The acceptance criterion is the tenancy of the document code: two agencies
 * may each register "QMS-001", but one agency may not register it twice.
 */
class ControlledDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function make(Agency $agency, string $code, array $overrides = []): ControlledDocument
    {
        return ControlledDocument::withoutGlobalScopes()->create(array_merge([
            'agency_id'      => $agency->id,
            'code'           => $code,
            'title'          => 'Document ' . $code,
            'status'         => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
        ], $overrides));
    }

    #[Test]
    public function a_document_can_be_created_with_the_expected_defaults(): void
    {
        $agency = Agency::factory()->create();

        $doc = $this->make($agency, 'QMS-001');

        $this->assertNotNull($doc->id);
        $this->assertSame(ControlledDocument::STATUS_DRAFT, $doc->status);
        $this->assertSame('internal', $doc->confidentiality);
    }

    /** The acceptance criterion. */
    #[Test]
    public function two_agencies_may_each_have_the_same_code(): void
    {
        $agencyA = Agency::factory()->create();
        $agencyB = Agency::factory()->create();

        $docA = $this->make($agencyA, 'QMS-001');
        $docB = $this->make($agencyB, 'QMS-001');

        $this->assertNotSame($docA->id, $docB->id);
        $this->assertSame('QMS-001', $docA->fresh()->code);
        $this->assertSame('QMS-001', $docB->fresh()->code);
    }

    #[Test]
    public function one_agency_may_not_register_the_same_code_twice(): void
    {
        $agency = Agency::factory()->create();
        $this->make($agency, 'QMS-001');

        $this->expectException(QueryException::class);

        $this->make($agency, 'QMS-001');
    }

    #[Test]
    public function the_tenant_scope_hides_another_agencys_documents(): void
    {
        $agencyA = Agency::factory()->create();
        $agencyB = Agency::factory()->create();

        $this->make($agencyA, 'QMS-001');
        $this->make($agencyB, 'QMS-002');

        // The scope reads the container binding that IdentifyAgency middleware
        // sets per request — not auth()->user(), so act the way the app acts.
        app()->instance('tenant_agency', $agencyA);

        $codes = ControlledDocument::query()->pluck('code')->all();

        $this->assertSame(['QMS-001'], $codes);
    }

    #[Test]
    public function documents_are_soft_deleted(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->make($agency, 'QMS-001');

        $doc->delete();

        // Default query excludes the row; withTrashed still sees it.
        // Note: withoutGlobalScopes() would ALSO strip SoftDeletingScope, so the
        // soft-delete filter must be observed on a normal (unscoped) query.
        $this->assertNull(ControlledDocument::find($doc->id));
        $this->assertNotNull(ControlledDocument::withTrashed()->find($doc->id));
        $this->assertSoftDeleted('controlled_documents', ['id' => $doc->id]);
    }

    #[Test]
    public function every_status_constant_is_labelled_and_badged(): void
    {
        foreach (ControlledDocument::STATUSES as $status) {
            $this->assertArrayHasKey($status, ControlledDocument::STATUS_LABELS);
            $this->assertArrayHasKey($status, ControlledDocument::STATUS_BADGES);
        }
    }

    #[Test]
    public function status_label_and_badge_accessors_fall_back_gracefully(): void
    {
        $agency = Agency::factory()->create();

        $doc = $this->make($agency, 'QMS-001', ['status' => ControlledDocument::STATUS_IN_REVIEW]);
        $this->assertSame('In Review', $doc->status_label);
        $this->assertSame('badge-warning', $doc->status_badge);

        $unknown = $this->make($agency, 'QMS-002', ['status' => 'archived_elsewhere']);
        $this->assertSame('Archived elsewhere', $unknown->status_label);
        $this->assertSame('badge-ghost', $unknown->status_badge);
    }

    #[Test]
    public function an_approved_document_past_its_review_date_is_flagged_overdue(): void
    {
        $agency = Agency::factory()->create();

        $overdue = $this->make($agency, 'QMS-001', [
            'status'           => ControlledDocument::STATUS_APPROVED,
            'next_review_date' => now()->subDay()->toDateString(),
        ]);
        $this->assertTrue($overdue->is_review_overdue);

        $current = $this->make($agency, 'QMS-002', [
            'status'           => ControlledDocument::STATUS_APPROVED,
            'next_review_date' => now()->addMonth()->toDateString(),
        ]);
        $this->assertFalse($current->is_review_overdue);

        // A draft is not overdue even if its review date is in the past.
        $draft = $this->make($agency, 'QMS-003', [
            'status'           => ControlledDocument::STATUS_DRAFT,
            'next_review_date' => now()->subDay()->toDateString(),
        ]);
        $this->assertFalse($draft->is_review_overdue);
    }
}
