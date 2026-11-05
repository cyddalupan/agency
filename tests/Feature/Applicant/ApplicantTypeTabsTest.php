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
 * Mjolnir card "LANDAS: Applicant module 'Tabs'" (2026-11-05).
 *
 * Client order for the type tabs is ALL TYPES → HOUSEHOLD → SKILLED on both
 * the Applicants list and the Backout, Cancelled & Repat list. Gulf hides the
 * HOUSEHOLD / SKILLED counters, FINAS keeps them, and the ALL TYPES counter is
 * never hidden.
 */
class ApplicantTypeTabsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAgency(string $name, ?bool $hideCounts): Agency
    {
        $agency = Agency::factory()->create(['name' => $name]);

        if ($hideCounts !== null) {
            $settings = is_array($agency->settings) ? $agency->settings : [];
            $settings['applicants']['hide_type_tab_counts'] = $hideCounts;
            $agency->settings = $settings;
            $agency->save();
        }

        return $agency;
    }

    private function makeUser(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function seedApplicants(Agency $agency): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id'      => $agency->id,
            'applicant_type' => Applicant::TYPE_SKILLED,
            'status_code'    => 45,
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id'      => $agency->id,
            'applicant_type' => Applicant::TYPE_HOUSEHOLD,
            'status_code'    => 45,
        ]);
    }

    /** @return array<int, string> tab labels in rendered order */
    private function tabOrder(string $html): array
    {
        preg_match_all('/>(ALL TYPES|HOUSEHOLD|SKILLED)</', $html, $m);

        return array_values(array_unique($m[1]));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);
    }

    #[Test]
    public function tabs_are_ordered_all_types_then_household_then_skilled(): void
    {
        $agency = $this->makeAgency('Finas Manpower Recruitment Corp.', false);
        $this->seedApplicants($agency);

        $response = $this->actingAs($this->makeUser($agency))->get(route('applicants.index'));
        $response->assertOk();

        $this->assertSame(['ALL TYPES', 'HOUSEHOLD', 'SKILLED'], $this->tabOrder($response->getContent()));
    }

    #[Test]
    public function withdrawn_module_uses_the_same_tab_order(): void
    {
        $agency = $this->makeAgency('Finas Manpower Recruitment Corp.', false);
        $this->seedApplicants($agency);

        $response = $this->actingAs($this->makeUser($agency))->get(route('applicants.withdrawn'));
        $response->assertOk();

        $this->assertSame(['ALL TYPES', 'HOUSEHOLD', 'SKILLED'], $this->tabOrder($response->getContent()));
    }

    #[Test]
    public function finas_shows_every_counter(): void
    {
        $agency = $this->makeAgency('Finas Manpower Recruitment Corp.', false);
        $this->seedApplicants($agency);

        $html = $this->actingAs($this->makeUser($agency))
            ->get(route('applicants.index'))
            ->getContent();

        $this->assertMatchesRegularExpression('/HOUSEHOLD<\/span>\s*<span class="badge[^"]*">\s*2\s*</', $html);
        $this->assertMatchesRegularExpression('/SKILLED<\/span>\s*<span class="badge[^"]*">\s*3\s*</', $html);
        $this->assertMatchesRegularExpression('/ALL TYPES<\/span>\s*<span class="badge[^"]*">\s*5\s*</', $html);
    }

    #[Test]
    public function gulf_hides_household_and_skilled_counters_but_keeps_all_types(): void
    {
        $agency = $this->makeAgency('Gulf Horizon International Services Inc.', true);
        $this->seedApplicants($agency);

        $html = $this->actingAs($this->makeUser($agency))
            ->get(route('applicants.index'))
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/HOUSEHOLD<\/span>\s*<span class="badge/', $html);
        $this->assertDoesNotMatchRegularExpression('/SKILLED<\/span>\s*<span class="badge/', $html);
        $this->assertMatchesRegularExpression('/ALL TYPES<\/span>\s*<span class="badge[^"]*">\s*5\s*</', $html);
    }

    #[Test]
    public function gulf_hides_counters_on_the_withdrawn_module_too(): void
    {
        $agency = $this->makeAgency('Gulf Horizon International Services Inc.', true);
        $this->seedApplicants($agency);

        $html = $this->actingAs($this->makeUser($agency))
            ->get(route('applicants.withdrawn'))
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/HOUSEHOLD<\/span>\s*<span class="badge/', $html);
        $this->assertDoesNotMatchRegularExpression('/SKILLED<\/span>\s*<span class="badge/', $html);
        $this->assertMatchesRegularExpression('/ALL TYPES<\/span>\s*<span class="badge/', $html);
    }

    #[Test]
    public function agency_named_gulf_hides_counts_even_without_the_setting(): void
    {
        $agency = $this->makeAgency('Gulf Horizon International Services Inc.', null);
        $this->seedApplicants($agency);

        $html = $this->actingAs($this->makeUser($agency))
            ->get(route('applicants.index'))
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/HOUSEHOLD<\/span>\s*<span class="badge/', $html);
        $this->assertMatchesRegularExpression('/ALL TYPES<\/span>\s*<span class="badge/', $html);
    }
}
