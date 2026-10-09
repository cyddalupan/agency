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
 * Toybits report 2026-10-09: "applicant page pagination - changing page
 * returns blank list".
 *
 * Root cause: the paginators were built with a bare paginate(15), so the
 * generated page links dropped every query parameter (type/search/status...)
 * and always fell back to the default SKILLED tab. On an agency whose SKILLED
 * tab is shorter than the page requested, page 2+ rendered empty.
 *
 * Fix: ->withQueryString() so the active tab + filters survive a page change.
 */
class ApplicantPaginationQueryStringTest extends TestCase
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

    private function seedType(string $type, int $count, int $statusCode = 7): void
    {
        Applicant::factory()->count($count)->create([
            'agency_id' => $this->agency->id,
            'applicant_type' => $type,
            'status_code' => $statusCode, // non-null + not withdrawn (35/38/50)
        ]);
    }

    #[Test]
    public function index_pagination_links_keep_the_active_tab(): void
    {
        $this->seedType(Applicant::TYPE_HOUSEHOLD, 20);
        $this->seedType(Applicant::TYPE_SKILLED, 20);

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk()
            // page-2 URL must carry the tab, not fall back to SKILLED.
            ->assertSee('type=household&amp;page=2', false);
    }

    #[Test]
    public function index_pagination_links_keep_active_filters(): void
    {
        $this->seedType(Applicant::TYPE_SKILLED, 20);

        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled', 'search' => 'zzz']))
            ->assertOk();
        // No rows match 'zzz', so no pagination is rendered — nothing to leak.
        // With a broad search we get pagination and the filter must persist.
        $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled', 'search' => 'a']))
            ->assertOk()
            ->assertSee('search=a&amp;page=2', false);
    }

    #[Test]
    public function changing_to_an_in_range_page_still_lists_the_tab(): void
    {
        // Mirrors the reported Finas case: household has 2+ pages but skilled
        // is short. Page 2 must stay on household and show rows.
        $this->seedType(Applicant::TYPE_HOUSEHOLD, 20);
        $this->seedType(Applicant::TYPE_SKILLED, 2);

        $html = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household', 'page' => 2]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('type=household', $html);
        $this->assertStringContainsString('HOUSEHOLD', $html);
    }

    #[Test]
    public function withdrawn_pagination_links_keep_the_active_tab(): void
    {
        // Withdrawn statuses: Repatriated (35), Cancel (38), Backout (50).
        $this->seedType(Applicant::TYPE_HOUSEHOLD, 20, 38);
        $this->seedType(Applicant::TYPE_SKILLED, 20, 38);

        $this->actingAs($this->admin)
            ->get(route('applicants.withdrawn', ['type' => 'household']))
            ->assertOk()
            ->assertSee('type=household&amp;page=2', false);
    }
}
