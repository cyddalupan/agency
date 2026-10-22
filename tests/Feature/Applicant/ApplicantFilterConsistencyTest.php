<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits filtering fix (2026-10-22).
 *
 * Reported:
 *  - "Nawawala ung mga list paggumamit ng filter."
 *  - "Nakalagay 694 na for passporting, pero pagclick ko 4 lang nalabas."
 *
 * The root cause was that status-chip counts were computed for ALL applicant
 * types while the list (and the click-through) was scoped to the SKILLED tab.
 * These tests pin the invariant: a chip's number must equal the rows it opens,
 * and a zero-result filter must never hide the controls needed to recover.
 */
class ApplicantFilterConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function seedApplicants(string $type, int $statusCode, int $count, array $attrs = []): void
    {
        Applicant::factory()->count($count)->create(array_merge([
            'agency_id' => $this->agency->id,
            'applicant_type' => $type,
            'status_code' => $statusCode,
        ], $attrs));
    }

    #[Test]
    public function status_chip_count_is_scoped_to_the_active_type(): void
    {
        // Gulf-like data: 690 household + 4 skilled at For Passporting (51).
        $this->seedApplicants(Applicant::TYPE_HOUSEHOLD, 51, 6);
        $this->seedApplicants(Applicant::TYPE_SKILLED, 51, 2);

        $household = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk();
        $this->assertSame(6, (int) $household->viewData('statusCounts')->get(51));

        $skilled = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled']))
            ->assertOk();
        $this->assertSame(2, (int) $skilled->viewData('statusCounts')->get(51));
    }

    #[Test]
    public function chip_count_equals_rows_after_clicking_it(): void
    {
        $this->seedApplicants(Applicant::TYPE_HOUSEHOLD, 51, 6);
        $this->seedApplicants(Applicant::TYPE_SKILLED, 51, 2);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household', 'status' => 51]))
            ->assertOk();

        // Chip said 6, list must contain 6 — not the 2-row global leftovers.
        $this->assertSame(6, $response->viewData('applicants')->total());
    }

    #[Test]
    public function all_types_tab_lists_both_types(): void
    {
        $this->seedApplicants(Applicant::TYPE_HOUSEHOLD, 51, 6);
        $this->seedApplicants(Applicant::TYPE_SKILLED, 51, 2);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'all', 'status' => 51]))
            ->assertOk();

        // This is the dashboard's landing view: all 694, not just the 4.
        $this->assertSame(8, $response->viewData('applicants')->total());
    }

    #[Test]
    public function zero_result_filter_keeps_the_filter_form_and_offers_a_clear(): void
    {
        $this->seedApplicants(Applicant::TYPE_SKILLED, 0, 1);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled', 'search' => 'zzz-no-such-name']))
            ->assertOk();

        $response->assertSee('No applicants match your filters');
        $response->assertSee('Clear Filters');
        // The search input must still be rendered so the user can edit it.
        $response->assertSee('name="search"', false);
    }

    #[Test]
    public function clear_filters_link_keeps_the_active_type_tab(): void
    {
        $this->seedApplicants(Applicant::TYPE_HOUSEHOLD, 0, 1);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household', 'search' => 'zzz-no-such-name']))
            ->assertOk();

        // Clearing must not bounce a household user back to the SKILLED tab.
        $response->assertSee(route('applicants.index', ['type' => 'household']), false);
    }

    #[Test]
    public function search_matches_email_and_contact_not_just_names(): void
    {
        $this->seedApplicants(Applicant::TYPE_SKILLED, 0, 1, [
            'first_name' => 'Searchable',
            'last_name' => 'Person',
            'email' => 'needle@example.com',
            'contact' => '09179998888',
        ]);

        $byEmail = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled', 'search' => 'needle@example.com']))
            ->assertOk();
        $byEmail->assertSee('Searchable');

        $byContact = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled', 'search' => '09179998888']))
            ->assertOk();
        $byContact->assertSee('Searchable');
    }

    #[Test]
    public function export_respects_the_type_filter(): void
    {
        $this->seedApplicants(Applicant::TYPE_HOUSEHOLD, 51, 2, ['first_name' => 'HouseholdOnly']);
        $this->seedApplicants(Applicant::TYPE_SKILLED, 51, 1, ['first_name' => 'SkilledOnly']);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.export', ['type' => 'household', 'status' => 51]))
            ->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('HouseholdOnly', $csv);
        $this->assertStringNotContainsString('SkilledOnly', $csv);
    }
}
