<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\ControlledDocument;
use App\Models\ControlledDocumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-08 (card 6afa0da5dd70c2a22cda903e) — create + list screens.
 *
 * Acceptance: a document can be created and then seen in the register with the
 * correct status. Also locks the role gate, because a document register is not
 * something every logged-in user should be able to add to.
 */
class ControlledDocumentScreenTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeUser(string $type): User
    {
        return User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => $type]);
    }

    #[Test]
    public function the_register_lists_documents_for_an_admin(): void
    {
        ControlledDocument::withoutGlobalScopes()->create([
            'agency_id'       => $this->agency->id,
            'code'            => 'QMS-001',
            'title'           => 'Leave Policy',
            'status'          => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
        ]);

        $response = $this->actingAs($this->admin)->get(route('controlled_documents.index'));

        $response->assertOk();
        $response->assertSee('QMS-001');
        $response->assertSee('Leave Policy');
        $response->assertSee('Draft');
    }

    /** The acceptance criterion, end to end through the real routes. */
    #[Test]
    public function a_document_can_be_created_and_then_appears_in_the_register(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('controlled_documents.store'), [
                'code'            => 'QMS-777',
                'title'           => 'Acceptance Criterion',
                'document_type'   => 'procedure',
                'confidentiality' => 'internal',
            ]);

        $response->assertRedirect(route('controlled_documents.index'));

        $this->assertDatabaseHas('controlled_documents', [
            'code'  => 'QMS-777',
            'title' => 'Acceptance Criterion',
        ]);

        $list = $this->actingAs($this->admin)->get(route('controlled_documents.index'));
        $list->assertOk();
        $list->assertSee('QMS-777');
        $list->assertSee('Acceptance Criterion');
        $list->assertSee('Draft');
    }

    #[Test]
    public function an_uploaded_file_creates_version_1_0(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('controlled_documents.store'), [
            'code'  => 'QMS-100',
            'title' => 'With File',
            'file'  => UploadedFile::fake()->create('leave-policy.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('controlled_documents.index'));

        $doc = ControlledDocument::withoutGlobalScopes()->where('code', 'QMS-100')->firstOrFail();
        $version = ControlledDocumentVersion::withoutGlobalScopes()
            ->where('controlled_document_id', $doc->id)
            ->firstOrFail();

        $this->assertSame('1.0', $version->version_no);
        $this->assertSame('leave-policy.pdf', $version->file_name);
        $this->assertNotNull($version->file_path);
        Storage::disk('public')->assertExists($version->file_path);

        // The document points at its first version.
        $this->assertSame($version->id, $doc->fresh()->current_version_id);
    }

    #[Test]
    public function a_duplicate_code_is_rejected_with_a_field_error_not_a_crash(): void
    {
        ControlledDocument::withoutGlobalScopes()->create([
            'agency_id'       => $this->agency->id,
            'code'            => 'QMS-001',
            'title'           => 'Original',
            'status'          => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
        ]);

        $response = $this->actingAs($this->admin)->post(route('controlled_documents.store'), [
            'code'  => 'QMS-001',
            'title' => 'Duplicate',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertSame(1, ControlledDocument::withoutGlobalScopes()->where('code', 'QMS-001')->count());
    }

    #[Test]
    public function two_agencies_may_use_the_same_code_through_the_screen(): void
    {
        ControlledDocument::withoutGlobalScopes()->create([
            'agency_id'       => $this->agency->id,
            'code'            => 'QMS-001',
            'title'           => 'Agency A doc',
            'status'          => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
        ]);

        // The second tenant acts as a real user here, so it needs QMS on as
        // well; this test is about the unique-code rule, not the gate.
        $otherAgency = Agency::factory()->withQms()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);

        app()->instance('tenant_agency', $otherAgency);

        $this->actingAs($otherAdmin)->post(route('controlled_documents.store'), [
            'code'  => 'QMS-001',
            'title' => 'Agency B doc',
        ])->assertRedirect(route('controlled_documents.index'));

        $this->assertSame(2, ControlledDocument::withoutGlobalScopes()->where('code', 'QMS-001')->count());
    }

    #[Test]
    public function the_create_form_renders_the_expected_controls(): void
    {
        $response = $this->actingAs($this->admin)->get(route('controlled_documents.create'));

        $response->assertOk();
        foreach (['code', 'title', 'document_type', 'department', 'process_owner_id', 'confidentiality', 'review_frequency', 'next_review_date', 'file'] as $field) {
            $response->assertSee('name="' . $field . '"', false);
        }
        $response->assertSee('document-form', false);
    }

    /** @test */
    #[Test]
    public function a_branch_account_cannot_reach_document_control(): void
    {
        $branch = $this->makeUser('branch');

        $this->actingAs($branch)->get(route('controlled_documents.index'))->assertForbidden();
        $this->actingAs($branch)->get(route('controlled_documents.create'))->assertForbidden();
        $this->actingAs($branch)->post(route('controlled_documents.store'), ['code' => 'X', 'title' => 'Y'])->assertForbidden();
    }

    #[Test]
    public function an_accounting_user_cannot_reach_document_control(): void
    {
        $user = $this->makeUser('accounting');

        $this->actingAs($user)->get(route('controlled_documents.index'))->assertForbidden();
    }

    #[Test]
    public function a_qms_manager_can_reach_document_control_ahead_of_qms_09(): void
    {
        $manager = $this->makeUser('qms_manager');

        $this->actingAs($manager)->get(route('controlled_documents.index'))->assertOk();
    }

    #[Test]
    public function a_super_admin_can_reach_document_control(): void
    {
        $super = $this->makeUser('super_admin');

        $this->actingAs($super)->get(route('controlled_documents.index'))->assertOk();
    }

    #[Test]
    public function the_register_can_be_filtered_by_status(): void
    {
        foreach ([['QMS-001', ControlledDocument::STATUS_DRAFT], ['QMS-002', ControlledDocument::STATUS_APPROVED]] as [$code, $status]) {
            ControlledDocument::withoutGlobalScopes()->create([
                'agency_id'       => $this->agency->id,
                'code'            => $code,
                'title'           => 'Doc ' . $code,
                'status'          => $status,
                'confidentiality' => 'internal',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('controlled_documents.index', ['status' => ControlledDocument::STATUS_APPROVED]));

        $response->assertOk();

        // Assert against the table rows, not the whole page: the search input's
        // placeholder itself contains an example code ("e.g. QMS-001"), so a
        // page-wide assertDontSee would fail on chrome rather than on data.
        $rows = $this->tableCodes($response->getContent());
        $this->assertSame(['QMS-002'], $rows);
    }

    #[Test]
    public function the_register_can_be_searched_by_code(): void
    {
        foreach (['QMS-001', 'HR-002'] as $code) {
            ControlledDocument::withoutGlobalScopes()->create([
                'agency_id'       => $this->agency->id,
                'code'            => $code,
                'title'           => 'Doc ' . $code,
                'status'          => ControlledDocument::STATUS_DRAFT,
                'confidentiality' => 'internal',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('controlled_documents.index', ['q' => 'HR-']));

        $response->assertOk();
        $this->assertSame(['HR-002'], $this->tableCodes($response->getContent()));
    }

    /** Codes present in the register table body, in order. */
    private function tableCodes(string $html): array
    {
        if (! preg_match('#<tbody>(.*?)</tbody>#s', $html, $body)) {
            return [];
        }

        // QMS-10 turned the code cell into a link to the detail screen, so read
        // the code from the link's data attribute rather than from the exact
        // cell markup — the assertion is about which documents are listed, not
        // about how the cell is rendered.
        preg_match_all('#data-document-link="([^"]+)"#', $body[1], $codes);

        return $codes[1];
    }

    #[Test]
    public function an_empty_register_says_so(): void
    {
        $response = $this->actingAs($this->admin)->get(route('controlled_documents.index'));

        $response->assertOk();
        $response->assertSee('No controlled documents yet.');
    }
}
