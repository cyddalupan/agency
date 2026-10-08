<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantMedical;
use App\Models\ApplicantVisa;
use App\Models\Branch;
use App\Models\Employer;
use App\Models\User;
use App\Services\ExpiryNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir "LANDAS: POP-UP Expire notification".
 *
 * The agency dashboard must raise a pop-up listing documents about to expire:
 *   • Medical — expiring within 2 weeks (Name, Clinic, Issue Date, Expire Date)
 *   • Visa    — expiring within 1 month  (Name, Branch, FRA, Visa No., Expire Date)
 */
class ExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => null,
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function makeApplicant(array $attributes = []): Applicant
    {
        return Applicant::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
        ], $attributes));
    }

    private function makeMedical(Applicant $applicant, string $expiry, array $attributes = []): ApplicantMedical
    {
        return $applicant->medical()->create(array_merge([
            'agency_id'   => $this->agency->id,
            'clinic_name' => 'ABC Medical Clinic',
            'issue_date'  => now()->subDays(5)->toDateString(),
            'expiry_date' => $expiry,
        ], $attributes));
    }

    private function makeVisa(Applicant $applicant, string $expiry, array $attributes = []): ApplicantVisa
    {
        return $applicant->visa()->create(array_merge([
            'agency_id'   => $this->agency->id,
            'visa_no'     => 'V-12345',
            'visa_type'   => 'Work Visa',
            'expiry_date' => $expiry,
        ], $attributes));
    }

    #[Test]
    public function service_includes_medicals_expiring_within_two_weeks_and_excludes_later_ones(): void
    {
        $soon = $this->makeApplicant(['first_name' => 'Soon', 'last_name' => 'Medical']);
        $late = $this->makeApplicant(['first_name' => 'Late', 'last_name' => 'Medical']);
        $gone = $this->makeApplicant(['first_name' => 'Gone', 'last_name' => 'Medical']);

        $this->makeMedical($soon, now()->addDays(10)->toDateString());
        $this->makeMedical($late, now()->addDays(20)->toDateString());
        $this->makeMedical($gone, now()->subDay()->toDateString());

        $results = app(ExpiryNotificationService::class)->medicals($this->user);

        $this->assertCount(1, $results);
        $this->assertEquals($soon->id, $results->first()->applicant_id);
    }

    #[Test]
    public function service_includes_visas_expiring_within_one_month_and_excludes_later_ones(): void
    {
        $soon = $this->makeApplicant(['first_name' => 'Soon', 'last_name' => 'Visa']);
        $late = $this->makeApplicant(['first_name' => 'Late', 'last_name' => 'Visa']);

        $this->makeVisa($soon, now()->addDays(25)->toDateString());
        $this->makeVisa($late, now()->addDays(45)->toDateString());

        $results = app(ExpiryNotificationService::class)->visas($this->user);

        $this->assertCount(1, $results);
        $this->assertEquals($soon->id, $results->first()->applicant_id);
    }

    #[Test]
    public function dashboard_shows_expiry_popup_with_medical_and_visa_details(): void
    {
        $branch   = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila Branch']);
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Gulf Horizon FRA']);

        $applicant = $this->makeApplicant([
            'first_name'  => 'Jane',
            'last_name'   => 'Doe',
            'branch_id'   => $branch->id,
            'employer_id' => $employer->id,
        ]);

        $this->makeMedical($applicant, now()->addDays(7)->toDateString(), ['clinic_name' => 'Health First Clinic']);
        $this->makeVisa($applicant, now()->addDays(20)->toDateString(), ['visa_no' => 'V-98765']);

        $html = $this->actingAs($this->user)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('expiry-notification-modal', $html);
        $this->assertStringContainsString('Expiring Documents', $html);

        // Medical block headers/columns.
        $this->assertStringContainsString('Clinic', $html);
        $this->assertStringContainsString('Issue Date', $html);
        $this->assertStringContainsString('Health First Clinic', $html);

        // Visa block headers/columns.
        $this->assertStringContainsString('Branch', $html);
        $this->assertStringContainsString('FRA', $html);
        $this->assertStringContainsString('Visa No.', $html);
        $this->assertStringContainsString('Manila Branch', $html);
        $this->assertStringContainsString('Gulf Horizon FRA', $html);
        $this->assertStringContainsString('V-98765', $html);

        // Applicant name appears (in both tables).
        $this->assertMatchesRegularExpression('/Jane\s+Doe/', $html);
    }

    #[Test]
    public function dashboard_hides_popup_when_nothing_is_expiring(): void
    {
        $applicant = $this->makeApplicant();
        $this->makeMedical($applicant, now()->addDays(60)->toDateString());

        $html = $this->actingAs($this->user)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('expiry-notification-modal', $html);
    }

    #[Test]
    public function expiry_records_are_scoped_to_the_current_agency(): void
    {
        $otherAgency    = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create([
            'agency_id' => $otherAgency->id,
            'first_name' => 'Other',
            'last_name'  => 'Agency',
        ]);
        $otherApplicant->medical()->create([
            'agency_id'   => $otherAgency->id,
            'clinic_name' => 'Other Agency Clinic',
            'issue_date'  => now()->subDays(3)->toDateString(),
            'expiry_date' => now()->addDays(5)->toDateString(),
        ]);

        $mine = $this->makeApplicant(['first_name' => 'Mine', 'last_name' => 'Only']);
        $this->makeMedical($mine, now()->addDays(5)->toDateString(), ['clinic_name' => 'My Clinic']);

        $results = app(ExpiryNotificationService::class)->medicals($this->user);

        $this->assertCount(1, $results);
        $this->assertEquals('My Clinic', $results->first()->clinic_name);
    }

    #[Test]
    public function branch_locked_user_only_sees_their_own_branch_expiries(): void
    {
        $branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Branch A']);
        $branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Branch B']);

        $mine   = $this->makeApplicant(['first_name' => 'Mine', 'last_name' => 'A', 'branch_id' => $branchA->id]);
        $theirs = $this->makeApplicant(['first_name' => 'Theirs', 'last_name' => 'B', 'branch_id' => $branchB->id]);

        $this->makeMedical($mine, now()->addDays(5)->toDateString(), ['clinic_name' => 'My Branch Clinic']);
        $this->makeMedical($theirs, now()->addDays(5)->toDateString(), ['clinic_name' => 'Other Branch Clinic']);

        $branchUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => $branchA->id,
        ]);

        $results = app(ExpiryNotificationService::class)->medicals($branchUser);

        $this->assertCount(1, $results);
        $this->assertEquals('My Branch Clinic', $results->first()->clinic_name);
    }
}
