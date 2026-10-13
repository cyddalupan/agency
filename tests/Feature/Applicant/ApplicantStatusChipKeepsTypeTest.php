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
 * APPLICANTS MODULE card (Cyd, 2026-10-13): "click pending pero wala display".
 *
 * Root cause: the pipeline status chips on the main applicants list linked to
 * applicants.index with ONLY the status param, dropping the active ?type tab.
 * On a household-heavy agency (Gulf), the default SKILLED tab is empty, so
 * clicking "Pending" always reset to SKILLED and rendered the empty state —
 * even though the agency had pending household applicants. The withdrawn list
 * already preserved ?type on its chips; the main list did not.
 *
 * Fix: chips preserve the active ?type, mirroring withdrawn.blade.php.
 */
class ApplicantStatusChipKeepsTypeTest extends TestCase
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

    private function seedPending(string $type, int $count): void
    {
        Applicant::factory()->count($count)->create([
            'agency_id' => $this->agency->id,
            'applicant_type' => $type,
            'status_code' => 0, // Pending
        ]);
    }

    #[Test]
    public function household_status_chip_link_keeps_the_household_tab(): void
    {
        // Household pending exist, skilled pending do not (Gulf-like).
        $this->seedPending(Applicant::TYPE_HOUSEHOLD, 3);

        $html = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk()
            ->getContent();

        // The "Pending" chip must point to a URL that keeps type=household.
        $this->assertStringContainsString('type=household', $html);
        $this->assertMatchesRegularExpression(
            '/href="[^"]*type=household[^"]*status=0[^"]*"|href="[^"]*status=0[^"]*type=household[^"]*"/',
            $html,
            'Pending chip should keep type=household.'
        );
    }

    #[Test]
    public function pending_filter_on_household_tab_lists_household_applicants(): void
    {
        $this->seedPending(Applicant::TYPE_HOUSEHOLD, 3);
        $this->seedPending(Applicant::TYPE_SKILLED, 4);

        $household = Applicant::where('agency_id', $this->agency->id)
            ->where('applicant_type', Applicant::TYPE_HOUSEHOLD)->pluck('first_name');

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household', 'status' => 0]));

        $response->assertOk();
        $response->assertDontSee('No Applicants Yet');
        foreach ($household as $name) {
            $response->assertSee($name);
        }
        $this->assertCount(3, $response->viewData('applicants'));
    }

    #[Test]
    public function pending_filter_on_skilled_tab_lists_skilled_applicants(): void
    {
        $this->seedPending(Applicant::TYPE_HOUSEHOLD, 3);
        $this->seedPending(Applicant::TYPE_SKILLED, 4);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'skilled', 'status' => 0]));

        $response->assertOk();
        $this->assertCount(4, $response->viewData('applicants'));
    }

    #[Test]
    public function all_chip_keeps_the_active_tab(): void
    {
        $this->seedPending(Applicant::TYPE_HOUSEHOLD, 3);

        $html = $this->actingAs($this->admin)
            ->get(route('applicants.index', ['type' => 'household']))
            ->assertOk()
            ->getContent();

        // The "All" chip must not drop back to the bare index (default SKILLED).
        $this->assertStringContainsString('type=household', $html);
    }
}
