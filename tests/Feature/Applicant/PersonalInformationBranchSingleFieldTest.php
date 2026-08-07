<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "For Fixing" card follow-up: single Branch source of truth.
 *
 * The Add/Edit Applicant form must use ONLY the branch_id dropdown. There must
 * be NO free-text "branch" textarea. The applicants table view shows the branch
 * NAME of the selected dropdown, and Edit pre-selects the saved branch_id.
 */
class PersonalInformationBranchSingleFieldTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branchA;
    private Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Alabang Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Makati Branch']);
    }

    #[Test]
    public function create_form_has_branch_dropdown_and_no_branch_textarea(): void
    {
        $response = $this->actingAs($this->user)->get(route('applicants.create'));

        $response->assertOk();
        $html = $response->getContent();

        // The dropdown must exist and list the branches.
        $this->assertStringContainsString('name="branch_id"', $html);
        $this->assertStringContainsString($this->branchA->name, $html);
        $this->assertStringContainsString($this->branchB->name, $html);

        // The free-text branch textarea must NOT exist.
        $this->assertStringNotContainsString('<textarea name="branch"', $html);
    }

    #[Test]
    public function saving_with_branch_id_persists_and_table_shows_dropdown_branch_name(): void
    {
        $response = $this->actingAs($this->user)->post(route('applicants.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'source' => 'Branch',
            'branch_id' => $this->branchA->id,
        ]);

        $response->assertRedirect();

        $applicant = Applicant::first();
        $this->assertNotNull($applicant);
        $this->assertSame($this->branchA->id, $applicant->branch_id);
        $this->assertSame($this->branchA->name, $applicant->branch->name);

        // Table view shows the dropdown's branch NAME, not free text.
        $index = $this->actingAs($this->user)->get(route('applicants.index'))->getContent();
        $this->assertStringContainsString($this->branchA->name, $index);
        $this->assertStringNotContainsString('Branch (note / custom field)', $index);
    }

    #[Test]
    public function edit_form_preselects_saved_branch_id_and_has_no_textarea(): void
    {
        $applicant = Applicant::create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'source' => 'Branch',
            'branch_id' => $this->branchB->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('applicants.edit', $applicant));
        $response->assertOk();
        $html = $response->getContent();

        // Pre-selects the saved branch in the dropdown.
        $this->assertStringContainsString('value="' . $this->branchB->id . '" selected', $html);
        // No free-text branch textarea on the edit form.
        $this->assertStringNotContainsString('<textarea name="branch"', $html);
    }

    #[Test]
    public function updating_preserves_branch_id_from_dropdown(): void
    {
        $applicant = Applicant::create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'source' => 'Branch',
            'branch_id' => $this->branchA->id,
        ]);

        $response = $this->actingAs($this->user)->patch(route('applicants.update', $applicant), [
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'source' => 'Branch',
            'branch_id' => $this->branchB->id,
        ]);

        $response->assertRedirect();
        $applicant->refresh();
        $this->assertSame($this->branchB->id, $applicant->branch_id);
    }
}
