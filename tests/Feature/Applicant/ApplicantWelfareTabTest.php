<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantWelfareRecord;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * FINAS card: PERSONAL INFORMATION "WELFARE Tab" (2026-11-06).
 *
 * New WELFARE tab beside CASES, visible to Admin + Welfare accounts only,
 * logging a migrant-worker welfare/monitoring contact.
 */
class ApplicantWelfareTabTest extends TestCase
{
    use RefreshDatabase;

    protected Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create();
        $this->applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        app()->instance('tenant_agency', $this->agency);
    }

    private function user(string $type): User
    {
        return User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => $type]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'logged_by'              => 'Cyd Dalupan',
            'date_of_contact'        => '2026-11-06',
            'ofw_full_name'          => 'Juan Dela Cruz',
            'passport_number'        => 'P1234567A',
            'dmw_ereg_id'            => 'DMW-00123',
            'country_of_destination' => 'Saudi Arabia',
            'job_site_city'          => 'Riyadh',
            'position_designation'   => 'Welder',
            'contact_no_abroad'      => '+966500000000',
            'foreign_recruitment_agency' => 'Al Faris Recruitment',
            'direct_employer_name'   => 'Saudi Builders Co.',
            'contact_person_phone'   => 'Ahmed / +966511111111',
            'milestones'             => ['day_3', 'month_3'],
            'wages_paid'             => 'yes',
            'food_accommodation'     => 'no',
            'food_accommodation_specify' => 'Housing shared by 8 workers, no aircon.',
            'medical_status'         => 'yes',
            'contract_provisions'    => 'yes',
            'working_environment'    => 'yes',
            'remarks'                => 'Advised employer to improve housing.',
        ], $overrides);
    }

    #[Test]
    public function welfare_tab_is_visible_to_admin_and_welfare_only(): void
    {
        foreach (['admin' => true, 'welfare' => true, 'recruitment' => false, 'encoder' => false] as $type => $visible) {
            $user = $this->user($type);
            $html = $this->actingAs($user)->get(route('applicants.show', $this->applicant))->getContent();

            $this->assertSame(
                $visible,
                str_contains($html, 'data-pi-tab="welfare"'),
                "Welfare tab visibility wrong for user_type={$type}"
            );
        }
    }

    #[Test]
    public function welfare_user_can_log_a_contact_with_milestones_and_indicators(): void
    {
        $response = $this->actingAs($this->user('welfare'))
            ->post(route('applicants.sub.store', [$this->applicant, 'welfare']), $this->payload());

        $response->assertRedirect(route('applicants.show', $this->applicant));
        $response->assertSessionHasNoErrors();

        $record = ApplicantWelfareRecord::first();
        $this->assertNotNull($record);
        $this->assertSame('Juan Dela Cruz', $record->ofw_full_name);
        $this->assertSame(['day_3', 'month_3'], $record->milestones);
        $this->assertSame('no', $record->food_accommodation);
        $this->assertSame('Housing shared by 8 workers, no aircon.', $record->food_accommodation_specify);
        $this->assertSame($this->agency->id, $record->agency_id);
    }

    #[Test]
    public function welfare_record_is_shown_on_the_applicant_page(): void
    {
        $this->actingAs($this->user('admin'))
            ->post(route('applicants.sub.store', [$this->applicant, 'welfare']), $this->payload())
            ->assertSessionHasNoErrors();

        $html = $this->actingAs($this->user('admin'))
            ->get(route('applicants.show', $this->applicant))->getContent();

        $this->assertStringContainsString('Day 3 (Arrival Check)', $html);
        $this->assertStringContainsString('Saudi Builders Co.', $html);
        $this->assertStringContainsString('Advised employer to improve housing.', $html);
    }

    #[Test]
    public function recruitment_user_cannot_post_a_welfare_record(): void
    {
        $this->actingAs($this->user('recruitment'))
            ->post(route('applicants.sub.store', [$this->applicant, 'welfare']), $this->payload())
            ->assertForbidden();

        $this->assertSame(0, ApplicantWelfareRecord::count());
    }

    #[Test]
    public function invalid_indicator_value_is_rejected(): void
    {
        $this->actingAs($this->user('welfare'))
            ->post(route('applicants.sub.store', [$this->applicant, 'welfare']), $this->payload(['wages_paid' => 'maybe']))
            ->assertSessionHasErrors();

        $this->assertSame(0, ApplicantWelfareRecord::count());
    }

    #[Test]
    public function welfare_record_can_be_deleted(): void
    {
        $this->actingAs($this->user('welfare'))
            ->post(route('applicants.sub.store', [$this->applicant, 'welfare']), $this->payload());
        $record = ApplicantWelfareRecord::firstOrFail();

        $this->actingAs($this->user('welfare'))
            ->delete(route('applicants.sub.destroy', [$this->applicant, 'welfare', $record->id]))
            ->assertRedirect(route('applicants.show', $this->applicant));

        $this->assertSame(0, ApplicantWelfareRecord::count());
    }
}
