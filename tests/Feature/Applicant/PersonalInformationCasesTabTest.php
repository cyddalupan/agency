<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Cases;
use App\Models\Employer;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * LANDAS "Personal Information" — Cases tab (TDD).
 *
 * Applicant profile shows a Cases tab with full CRUD:
 * Date Received, Date Hearing, FRA/Employer (dropdown from employers),
 * Status, message box (description), Case Number, Case Title, Court.
 */
class PersonalInformationCasesTabTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->applicant = Applicant::factory()->create([
            'agency_id'    => $this->agency->id,
            'has_passport' => 'with',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    #[Test]
    public function show_page_renders_cases_tab(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-pi-tab="cases"', $html);
        $this->assertStringContainsString('data-pi-panel="cases"', $html);
    }

    #[Test]
    public function case_can_be_added_from_profile(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Al-Futtaim Manpower',
        ]);

        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'cases']), [
                'case_number'   => 'C-2026-001',
                'title'         => 'Contract dispute',
                'description'   => 'Employer withholding salary for 2 months',
                'date_received' => '2026-08-01',
                'date_hearing'  => '2026-09-15',
                'employer_id'   => $employer->id,
                'court'         => 'NLRC Manila',
                'status'        => 'open',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('cases', [
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'case_number'   => 'C-2026-001',
            'title'         => 'Contract dispute',
            'employer_id'   => $employer->id,
            'status'        => 'open',
        ]);
    }

    #[Test]
    public function added_case_is_visible_on_profile_page(): void
    {
        $employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Gulf Manpower Co.',
        ]);

        Cases::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'employer_id'   => $employer->id,
            'case_number'   => 'C-2026-002',
            'title'         => 'OWWA complaint',
            'description'   => 'Pending verification with OWWA',
            'date_received' => '2026-07-20',
            'date_hearing'  => '2026-08-30',
            'court'         => 'OWWA Arbitration',
            'status'        => 'open',
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('C-2026-002', $html);
        $this->assertStringContainsString('OWWA complaint', $html);
        $this->assertStringContainsString('Gulf Manpower Co.', $html);
        $this->assertStringContainsString('OWWA Arbitration', $html);
        $this->assertStringContainsString('Pending verification with OWWA', $html);
    }

    #[Test]
    public function case_can_be_updated_from_profile(): void
    {
        $case = Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'title'        => 'Old title',
            'status'       => 'open',
        ]);

        $this->actingAs($this->user)
            ->put(route('applicants.sub.update', [$this->applicant, 'cases', $case->id]), [
                'title'       => 'Updated title',
                'status'      => 'closed',
                'case_number' => 'C-2026-003',
                'court'       => 'NLRC Cebu',
            ])
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertDatabaseHas('cases', [
            'id'          => $case->id,
            'title'       => 'Updated title',
            'status'      => 'closed',
            'case_number' => 'C-2026-003',
            'court'       => 'NLRC Cebu',
        ]);
    }

    #[Test]
    public function case_can_be_deleted_from_profile(): void
    {
        $case = Cases::create([
            'agency_id'    => $this->agency->id,
            'applicant_id' => $this->applicant->id,
            'title'        => 'Delete me',
            'status'       => 'open',
        ]);

        $this->actingAs($this->user)
            ->delete(route('applicants.sub.destroy', [$this->applicant, 'cases', $case->id]))
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertSoftDeleted('cases', ['id' => $case->id]);
    }

    #[Test]
    public function multiple_cases_per_applicant_are_supported(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            Cases::create([
                'agency_id'    => $this->agency->id,
                'applicant_id' => $this->applicant->id,
                'case_number'  => "C-2026-0{$i}",
                'title'        => "Case {$i}",
                'status'       => 'open',
            ]);
        }

        $this->assertSame(3, $this->applicant->cases()->count());
    }

    #[Test]
    public function adding_case_requires_title(): void
    {
        $this->actingAs($this->user)
            ->post(route('applicants.sub.store', [$this->applicant, 'cases']), [
                'status' => 'open',
            ])
            ->assertSessionHasErrors('title', null, 'cases');
    }
}
