<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantContract;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Bug (Toybits report 2026-08-15): a contract added on the applicant page's
 * Documents tab -> Contract section saves to the `applicant_contracts`
 * sub-table, but the applicants list Contract / Contract Received columns
 * only read the legacy `applicants.contract` (file path) and
 * `applicants.contract_received_date` columns — so the columns stay empty.
 */
class ApplicantContractListColumnTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    /** Exactly what the Documents tab -> Contract "Save Contract" form posts. */
    private function addContractViaDocumentsTab(Applicant $applicant, array $overrides = []): void
    {
        $this->actingAs($this->user)->post(
            route('applicants.sub.store', [$applicant, 'contract']),
            array_merge([
                'rfp'               => 'RFP-001',
                'sponsor'           => 'Al Farid Co',
                'sponsor_id'        => 'SP-9',
                'contact'           => '021234567',
                'address'           => 'Riyadh',
                'contract_received' => '2026-04-01',
                'contract_signed'   => '2026-04-10',
            ], $overrides)
        )->assertRedirect(route('applicants.show', $applicant));
    }

    #[Test]
    public function contract_added_via_documents_tab_appears_in_applicants_list(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $this->addContractViaDocumentsTab($applicant);

        $this->assertDatabaseHas('applicant_contracts', [
            'applicant_id' => $applicant->id,
            'rfp'          => 'RFP-001',
        ]);

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertSee('RFP-001');
        $response->assertSee('Apr 01, 2026'); // contract_received from sub-table
    }

    #[Test]
    public function contract_added_via_documents_tab_appears_in_withdrawn_list(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'status_code' => 38, // Cancel -> Withdrawn & Repat tab
        ]);

        $this->addContractViaDocumentsTab($applicant, ['rfp' => 'RFP-002']);

        $response = $this->actingAs($this->user)->get(route('applicants.withdrawn'));

        $response->assertOk();
        $response->assertSee('RFP-002');
        $response->assertSee('Apr 01, 2026');
    }

    #[Test]
    public function legacy_contract_file_column_still_shows_view_link(): void
    {
        Storage::fake('public');

        $applicant = Applicant::factory()->create([
            'agency_id'             => $this->agency->id,
            'contract'              => 'contracts/legacy.pdf',
            'contract_received_date' => '2026-03-15',
        ]);

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertSee('contracts/legacy.pdf');
        $response->assertSee('Mar 15, 2026');
    }

    #[Test]
    public function fra_onprocess_shows_contract_dates_from_documents_tab(): void
    {
        $employer = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'employer',
            'status'    => 'active',
        ]);

        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 8, // on-process
            'status'      => 'active',
        ]);

        ApplicantContract::create([
            'agency_id'         => $this->agency->id,
            'applicant_id'      => $applicant->id,
            'rfp'               => 'RFP-003',
            'contract_received' => '2026-06-01',
            'contract_signed'   => '2026-06-10',
        ]);

        $response = $this->actingAs($employer)->get(route('fra.onprocess'));

        $response->assertOk();
        $response->assertSee('Yes');    // contract_received cell
        $response->assertSee('SIGNED'); // contract_signed cell
    }
}
