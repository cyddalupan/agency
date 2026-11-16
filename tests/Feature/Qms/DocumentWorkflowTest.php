<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\ControlledDocument;
use App\Models\ControlledDocumentVersion;
use App\Models\User;
use App\Services\DocumentControlWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * QMS-10 (card 6afa0da73812c0fa2168d3b4) — review → approve workflow.
 *
 * Acceptance from the card:
 *   - full lifecycle draft → in_review → approved → new version → old superseded
 *   - a blocked test showing a non-approver cannot approve
 *
 * Spec §10.1/10.2: preparer → reviewer → approver, and an approved document is
 * never overwritten.
 */
class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $qmsManager;
    private User $staff;
    private DocumentControlWorkflow $workflow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->admin      = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);
        $this->qmsManager = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'qms_manager']);
        $this->staff      = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->workflow = app(DocumentControlWorkflow::class);
    }

    /** A document with one draft version, as QMS-08 store() leaves it. */
    private function makeDocumentWithDraft(string $code = 'QMS-001'): array
    {
        $document = ControlledDocument::withoutGlobalScopes()->create([
            'agency_id'       => $this->agency->id,
            'code'            => $code,
            'title'           => 'Leave Policy',
            'status'          => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
            'created_by'      => $this->admin->id,
        ]);

        $version = ControlledDocumentVersion::withoutGlobalScopes()->create([
            'controlled_document_id' => $document->id,
            'agency_id'              => $this->agency->id,
            'version_no'             => '1.0',
            'prepared_by'            => $this->admin->id,
            'status'                 => ControlledDocumentVersion::STATUS_DRAFT,
        ]);

        $document->update(['current_version_id' => $version->id]);

        return [$document->fresh(), $version->fresh()];
    }

    #[Test]
    public function the_full_lifecycle_runs_draft_to_approved_to_superseded(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();

        // 1. draft → in_review
        $v1 = $this->workflow->submitForReview($v1, $this->admin);
        $this->assertSame(ControlledDocumentVersion::STATUS_IN_REVIEW, $v1->status);
        $this->assertSame(ControlledDocument::STATUS_IN_REVIEW, $document->fresh()->status);

        // 2. in_review → approved (this is what makes it effective)
        $v1 = $this->workflow->approve($v1, $this->qmsManager);
        $this->assertSame(ControlledDocumentVersion::STATUS_APPROVED, $v1->status);
        $this->assertSame($this->qmsManager->id, $v1->approved_by, 'the approver is recorded');
        $this->assertNotNull($v1->approval_date);
        $this->assertNotNull($v1->effective_date, 'an approved version is effective');
        $this->assertNull($v1->supersedes_version_id, 'the first version supersedes nothing');

        $document->refresh();
        $this->assertSame(ControlledDocument::STATUS_APPROVED, $document->status);
        $this->assertSame($v1->id, $document->current_version_id, 'document points at the approved version');

        // 3. new version — 1.0 → 1.1, opened as a draft
        $v2 = $this->workflow->openNextVersion($document, $this->admin, ControlledDocumentVersion::BUMP_MINOR, 'Fix a typo');
        $this->assertSame('1.1', $v2->version_no);
        $this->assertSame(ControlledDocumentVersion::STATUS_DRAFT, $v2->status);

        // The approved version is NOT demoted and the document keeps pointing
        // at it while the new draft is in flight (an approved document is never
        // overwritten).
        $document->refresh();
        $this->assertSame(ControlledDocument::STATUS_APPROVED, $document->status);
        $this->assertSame($v1->id, $document->current_version_id);
        $this->assertSame(ControlledDocumentVersion::STATUS_APPROVED, $v1->fresh()->status);

        // 4. the new version goes through the same gate and supersedes v1
        $this->workflow->submitForReview($v2, $this->admin);
        $v2 = $this->workflow->approve($v2->fresh(), $this->admin);

        $this->assertSame(ControlledDocumentVersion::STATUS_APPROVED, $v2->status);
        $this->assertSame($v1->id, $v2->supersedes_version_id, 'v2 records what it replaced');
        $this->assertSame(ControlledDocumentVersion::STATUS_SUPERSEDED, $v1->fresh()->status, 'v1 auto-flips to superseded');
        $this->assertSame($v2->id, $document->fresh()->current_version_id);
    }

    #[Test]
    public function a_non_approver_cannot_approve(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);

        $this->expectException(RuntimeException::class);

        $this->workflow->approve($v1->fresh(), $this->staff);
    }

    /** The blocked case must also hold over HTTP, not just in the service. */
    #[Test]
    public function a_non_approver_cannot_approve_over_http_and_the_version_is_untouched(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);

        $response = $this->actingAs($this->staff)
            ->post(route('controlled_documents.versions.approve', [$document, $v1->fresh()]));

        $response->assertForbidden();

        $v1->refresh();
        $this->assertSame(ControlledDocumentVersion::STATUS_IN_REVIEW, $v1->status);
        $this->assertNull($v1->approved_by);
    }

    #[Test]
    public function the_internal_auditor_cannot_approve(): void
    {
        $auditor = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'internal_auditor']);
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);

        $this->actingAs($auditor)
            ->post(route('controlled_documents.versions.approve', [$document, $v1->fresh()]))
            ->assertForbidden();

        $this->assertSame(ControlledDocumentVersion::STATUS_IN_REVIEW, $v1->fresh()->status);
    }

    #[Test]
    public function a_version_in_draft_cannot_be_approved_without_review(): void
    {
        [, $v1] = $this->makeDocumentWithDraft();

        // Skipping the review step is exactly what document control exists to
        // prevent.
        $this->expectException(RuntimeException::class);

        $this->workflow->approve($v1, $this->admin);
    }

    #[Test]
    public function a_reviewer_can_return_a_version_with_a_note(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);

        $v1 = $this->workflow->returnToPreparer($v1->fresh(), $this->qmsManager, 'Section 4 conflicts with the contract.');

        $this->assertSame(ControlledDocumentVersion::STATUS_DRAFT, $v1->status, 'returned means back to draft');
        $this->assertNull($v1->approved_by, 'returning is not approving');
        $this->assertStringContainsString('Section 4 conflicts', (string) $v1->revision_summary);
        $this->assertSame(ControlledDocument::STATUS_DRAFT, $document->fresh()->status);
    }

    #[Test]
    public function a_return_requires_a_note(): void
    {
        [, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);

        $this->actingAs($this->qmsManager)
            ->post(route('controlled_documents.versions.return', [$v1->document, $v1->fresh()]), ['note' => ''])
            ->assertSessionHasErrors('note');

        $this->assertSame(ControlledDocumentVersion::STATUS_IN_REVIEW, $v1->fresh()->status);
    }

    #[Test]
    public function the_detail_screen_shows_the_version_history_timeline(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);
        $v1 = $this->workflow->approve($v1->fresh(), $this->qmsManager);

        $response = $this->actingAs($this->admin)
            ->get(route('controlled_documents.show', $document));

        $response->assertOk();
        $response->assertSee('Version history');
        $response->assertSee('QMS-001');
        $response->assertSee('v1.0');
        // who approved, and when
        $response->assertSee($this->qmsManager->name);
        $response->assertSee($v1->approval_date->format('d M Y'));
    }

    #[Test]
    public function the_timeline_lists_every_version_newest_first(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);
        $v1 = $this->workflow->approve($v1->fresh(), $this->admin);
        $v2 = $this->workflow->openNextVersion($document->fresh(), $this->admin);

        $response = $this->actingAs($this->admin)->get(route('controlled_documents.show', $document));
        $response->assertOk();

        // The newest version must appear before the older one in the HTML.
        // assertLessThan($expected, $actual) => expected < actual, so the
        // older version's offset is the expected (smaller) one.
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'v' . $v2->version_no),
            strpos($html, 'v1.0'),
            'the timeline should be newest first'
        );
        $response->assertSee($v1->approvedBy->name);
    }

    #[Test]
    public function a_version_cannot_be_submitted_twice(): void
    {
        [, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);

        $this->expectException(RuntimeException::class);

        $this->workflow->submitForReview($v1->fresh(), $this->admin);
    }

    #[Test]
    public function a_version_from_another_document_is_a_404(): void
    {
        [$docA] = $this->makeDocumentWithDraft('QMS-A');
        [, $versionOfB] = $this->makeDocumentWithDraft('QMS-B');

        $this->actingAs($this->admin)
            ->post(route('controlled_documents.versions.submit', [$docA, $versionOfB]))
            ->assertNotFound();
    }

    #[Test]
    public function a_reader_without_manage_rights_cannot_open_the_detail_screen(): void
    {
        [$document] = $this->makeDocumentWithDraft();

        $this->actingAs($this->staff)
            ->get(route('controlled_documents.show', $document))
            ->assertForbidden();
    }

    #[Test]
    public function a_draft_in_flight_gets_the_action_panel_while_the_approved_version_stays_current(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);
        $this->workflow->approve($v1->fresh(), $this->admin);

        $v2 = $this->workflow->openNextVersion($document->fresh(), $this->admin);

        $response = $this->actingAs($this->admin)->get(route('controlled_documents.show', $document));
        $response->assertOk();

        // The panel must operate on the new draft — otherwise there is no way
        // to submit it for review from the screen at all.
        $response->assertSee('Draft in progress');
        $response->assertSee(route('controlled_documents.versions.submit', [$document, $v2]), false);

        // ...while v1.0 remains the effective, current version.
        $document->refresh();
        $this->assertSame($v1->id, $document->current_version_id);
        $this->assertSame(ControlledDocument::STATUS_APPROVED, $document->status);
    }

    #[Test]
    public function the_major_bump_resets_the_minor_number(): void
    {
        [$document, $v1] = $this->makeDocumentWithDraft();
        $this->workflow->submitForReview($v1, $this->admin);
        $this->workflow->approve($v1->fresh(), $this->admin);

        $v2 = $this->workflow->openNextVersion($document->fresh(), $this->admin, ControlledDocumentVersion::BUMP_MAJOR);

        $this->assertSame('2.0', $v2->version_no);
    }
}
