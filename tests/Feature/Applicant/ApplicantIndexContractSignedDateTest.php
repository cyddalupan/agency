<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantContract;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16:
 * 1. Rename the applicants-list "Contract" column to "Contract Signed Date".
 * 2. Show the contract signed date (from the applicant_contracts sub-table)
 *    instead of the "View" / "Added" links.
 */
class ApplicantIndexContractSignedDateTest extends TestCase
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

    #[Test]
    public function contract_column_is_renamed_and_shows_the_signed_date(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        ApplicantContract::create([
            'agency_id'         => $this->agency->id,
            'applicant_id'      => $applicant->id,
            'rfp'               => 'RFP-001',
            'contract_received' => '2026-04-01',
            'contract_signed'   => '2026-04-10',
        ]);

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertSee('Contract Signed Date');
        $response->assertDontSee('>Contract</th>');
        $response->assertSee('Apr 10, 2026');
    }

    #[Test]
    public function contract_column_no_longer_shows_view_or_added_links(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        ApplicantContract::create([
            'agency_id'       => $this->agency->id,
            'applicant_id'    => $applicant->id,
            'rfp'             => 'RFP-002',
            'contract_signed' => '2026-05-20',
        ]);

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertDontSee('📄 View');
        $response->assertDontSee('📄 Added');
        $response->assertSee('May 20, 2026');
    }

    #[Test]
    public function contract_signed_date_column_shows_dash_when_no_contract_exists(): void
    {
        Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertDontSee('📄 View');
        $response->assertDontSee('📄 Added');
    }
}
