<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd card "LANDAS: Applicant Branch filter" (2026-10-22).
 *
 * The applicants list could be searched by status, gender, employer and
 * country but not by branch, so multi-branch agencies had to eyeball the
 * Branch column. These tests pin the new ?branch=<id> filter on the list,
 * the withdrawn tab and both CSV exports, plus the rule that a branch-locked
 * account only ever sees its own branch in the dropdown.
 */
class ApplicantBranchFilterTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Branch $manila;
    private Branch $cebu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->manila = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Manila',
        ]);
        $this->cebu = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Cebu',
        ]);

        Applicant::factory()->count(3)->create([
            'agency_id'      => $this->agency->id,
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
            'status_code'    => 51,
            'branch_id'      => $this->manila->id,
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id'      => $this->agency->id,
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
            'status_code'    => 51,
            'branch_id'      => $this->cebu->id,
        ]);
    }

    #[Test]
    public function branch_filter_narrows_the_list_and_the_matching_badge(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household', 'branch' => $this->manila->id]))
            ->assertOk();

        $this->assertSame(3, $response->viewData('applicants')->total());
        $this->assertSame($this->manila->id, $response->viewData('applicants')->first()->branch_id);
        $response->assertSee('3 matching');
    }

    #[Test]
    public function branch_filter_options_are_scoped_to_the_agency(): void
    {
        $other = Agency::factory()->create();
        Branch::factory()->create(['agency_id' => $other->id, 'name' => 'Davao']);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk();

        $names = $response->viewData('branches')->pluck('name')->all();
        $this->assertContains('Manila', $names);
        $this->assertContains('Cebu', $names);
        $this->assertNotContains('Davao', $names);
    }

    #[Test]
    public function branch_locked_user_only_sees_their_own_branch_option_and_rows(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->cebu->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk();

        $this->assertSame(['Cebu'], $response->viewData('branches')->pluck('name')->all());
        $this->assertSame(2, $response->viewData('applicants')->total());

        // Asking for another branch must not leak rows.
        $forced = $this->actingAs($staff)
            ->get(route('applicants.index', ['type' => 'household', 'branch' => $this->manila->id]))
            ->assertOk();
        $this->assertSame(0, $forced->viewData('applicants')->total());
    }

    #[Test]
    public function branch_filter_applies_to_the_csv_export(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('applicants.export', ['type' => 'household', 'branch' => $this->cebu->id]))
            ->assertOk();

        $lines = array_values(array_filter(explode("\n", trim($response->streamedContent()))));
        // Header + the two Cebu applicants only.
        $this->assertCount(3, $lines);
    }

    #[Test]
    public function branch_filter_applies_to_the_withdrawn_tab_and_its_export(): void
    {
        Applicant::factory()->count(2)->create([
            'agency_id'      => $this->agency->id,
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
            'status_code'    => 38, // Cancel — withdrawn & repat tab
            'branch_id'      => $this->cebu->id,
        ]);

        $page = $this->actingAs($this->admin)
            ->get(route('applicants.withdrawn', ['type' => 'household', 'branch' => $this->cebu->id]))
            ->assertOk();
        $this->assertSame(2, $page->viewData('applicants')->total());

        $export = $this->actingAs($this->admin)
            ->get(route('applicants.withdrawn.export', ['type' => 'household', 'branch' => $this->cebu->id]))
            ->assertOk();
        $lines = array_values(array_filter(explode("\n", trim($export->streamedContent()))));
        $this->assertCount(3, $lines);
    }
}
