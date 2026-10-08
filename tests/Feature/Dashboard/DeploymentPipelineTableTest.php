<?php

namespace Tests\Feature\Dashboard;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\Employer;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Deployment Pipeline rendered as a table (FRA rows × stage columns + TOTAL),
 * with Month/Year + Country filters.
 * Mjolnir card "DEPLOYMENT PIPELINE - (Table form)", 2026-10-08.
 */
class DeploymentPipelineTableTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'user_type' => 'admin',
            'agency_id' => $this->agency->id,
        ]);

        $this->seed(StatusCodesSeeder::class);
    }

    private function code(string $label): int
    {
        return (int) StatusCode::where('label', $label)->value('code');
    }

    private function applicant(int $employerId, string $stageLabel, array $attrs = []): Applicant
    {
        return Applicant::factory()->create(array_merge([
            'agency_id'   => $this->agency->id,
            'employer_id' => $employerId,
            'status_code' => $this->code($stageLabel),
        ], $attrs));
    }

    #[Test]
    public function pipeline_table_renders_fra_rows_stage_columns_and_total_row(): void
    {
        $fraA = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Alpha FRA']);
        $fraB = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Beta FRA']);

        $this->applicant($fraA->id, 'Reserved');
        $this->applicant($fraA->id, 'Reserved');
        $this->applicant($fraA->id, 'Deployed');
        $this->applicant($fraB->id, 'Visa');

        $res = $this->actingAs($this->user)->get(route('agency.dashboard'));

        $res->assertOk();
        foreach (['FRA', 'Reserved', 'Selected', 'Interview', 'Contract', 'OEC', 'OWWA', 'Visa', 'Deployed', 'Repat', 'Backout'] as $header) {
            $res->assertSee($header);
        }
        $res->assertSee('TOTAL');
        $res->assertSee('Alpha FRA');
        $res->assertSee('Beta FRA');
    }

    #[Test]
    public function pipeline_table_folds_round_two_variants_into_the_base_stage(): void
    {
        $fra = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Gamma FRA']);

        $this->applicant($fra->id, 'Reserved');
        $this->applicant($fra->id, 'Reserved 2');

        $res = $this->actingAs($this->user)->get(route('agency.dashboard'));
        $res->assertOk();

        // Gamma FRA's Reserved column should hold the combined count (2).
        $this->assertMatchesRegularExpression('/Gamma FRA.*?>2</s', $res->getContent());
    }

    #[Test]
    public function pipeline_table_year_filter_limits_the_counts(): void
    {
        $fra = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Delta FRA']);

        $this->applicant($fra->id, 'Reserved', ['created_at' => now()]);
        $old = $this->applicant($fra->id, 'Reserved');
        $old->forceFill(['created_at' => now()->subYears(2)])->save();

        $res = $this->actingAs($this->user)
            ->get(route('agency.dashboard', ['pipeline_year' => now()->year]));

        $res->assertOk();
        $this->assertMatchesRegularExpression('/Delta FRA.*?>1</s', $res->getContent());
    }

    #[Test]
    public function pipeline_table_country_filter_scopes_to_the_fras_country(): void
    {
        $ksa = Country::factory()->create(['name' => 'KSA']);
        $uae = Country::factory()->create(['name' => 'UAE']);

        $fraKsa = Employer::factory()->create([
            'agency_id' => $this->agency->id, 'name' => 'KSA FRA', 'country_id' => $ksa->id,
        ]);
        $fraUae = Employer::factory()->create([
            'agency_id' => $this->agency->id, 'name' => 'UAE FRA', 'country_id' => $uae->id,
        ]);

        $this->applicant($fraKsa->id, 'Reserved');
        $this->applicant($fraUae->id, 'Reserved');

        $res = $this->actingAs($this->user)
            ->get(route('agency.dashboard', ['pipeline_country' => $ksa->id]));

        $res->assertOk();
        $res->assertSee('KSA FRA');
        $res->assertDontSee('UAE FRA');
    }
}
