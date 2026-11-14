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
 * Replaces the old ApplicantWelfareTabTest.
 *
 * HISTORY: the WELFARE tab lived inside Personal Information (FINAS card
 * 2026-11-06). Josh asked for it to be REMOVED and turned into its own module
 * under Backout, Cancelled & Repat (2026-11-14), so this file now pins that the
 * tab is gone and that welfare can no longer be written through the applicant.
 *
 * The module's own behaviour is covered by
 * tests/Feature/WelfareModule/WelfareModuleTest.php.
 */
class ApplicantWelfareTabRemovedTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create(['subdomain' => 'finas']);
        $this->applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        app()->instance('tenant_agency', $this->agency);
    }

    private function user(string $type): User
    {
        return User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => $type]);
    }

    #[Test]
    public function the_welfare_tab_is_not_rendered_for_anyone(): void
    {
        foreach (['admin', 'welfare', 'super_admin'] as $type) {
            $html = $this->actingAs($this->user($type))
                ->get(route('applicants.show', $this->applicant))
                ->assertOk()
                ->getContent();

            $this->assertStringNotContainsString('data-pi-tab="welfare"', $html, "Tab still shows for {$type}");
            $this->assertStringNotContainsString('data-pi-panel="welfare"', $html, "Panel still shows for {$type}");
        }
    }

    #[Test]
    public function welfare_can_no_longer_be_written_through_the_applicant(): void
    {
        // The old write path is closed for every role, admin included — the
        // Welfare module is the only way in now.
        foreach (['admin', 'welfare', 'super_admin'] as $type) {
            $this->actingAs($this->user($type))->post(
                route('applicants.sub.store', [$this->applicant, 'welfare']),
                ['ofw_full_name' => 'Should Not Save', 'date_of_contact' => now()->toDateString()]
            )->assertForbidden();
        }

        $this->assertSame(0, ApplicantWelfareRecord::count());
    }

    #[Test]
    public function existing_welfare_records_are_preserved_and_still_visible_in_the_module(): void
    {
        // Data logged before the tab was removed must not disappear.
        $record = ApplicantWelfareRecord::create([
            'agency_id'          => $this->agency->id,
            'applicant_id'       => $this->applicant->id,
            'ofw_full_name'      => 'Juan P. Dela Cruz',
            'passport_number'    => 'P1234567',
            'country_of_destination' => 'KSA',
            'remarks'            => 'Legacy record from the old tab.',
            'date_of_contact'    => now()->toDateString(),
        ]);

        $this->actingAs($this->user('admin'))->get(route('welfare.index'))
            ->assertOk()
            ->assertSee('Juan P. Dela Cruz')
            ->assertSee('KSA');

        $this->assertNotNull($record->fresh());
    }
}
