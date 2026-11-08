<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Position;
use App\Models\StatusCode;
use App\Models\User;
use App\Support\ApplicantDuplicateChecker;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-08 (LANDAS card 6af02dd56cff0b42b7c91ee3): "Add Applicant —
 * notify when the same applicant name is entered (first/middle/last) or
 * almost the same (MARY CORTEZ vs MARRY ANN CORTEZ)" and show Name, Contact#,
 * Gender, Age, Branch, Agent, Position, Country, FRA Status of the matches.
 */
class ApplicantDuplicateFuzzyTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function existing(array $attrs = []): Applicant
    {
        return Applicant::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'first_name' => 'MARRY',
            'middle_name' => 'ANN',
            'last_name' => 'CORTEZ',
        ], $attrs));
    }

    #[Test]
    public function mary_cortez_matches_existing_marry_ann_cortez(): void
    {
        $existing = $this->existing();

        $matches = ApplicantDuplicateChecker::find($this->agency->id, [
            'first_name' => 'MARY',
            'last_name' => 'CORTEZ',
        ]);

        $this->assertCount(1, $matches);
        $this->assertSame($existing->id, $matches->first()['applicant']->id);
        $this->assertStringContainsString('Similar name', implode(' ', $matches->first()['reasons']));
    }

    #[Test]
    public function mary_does_not_match_maria(): void
    {
        $this->existing(['first_name' => 'MARIA', 'middle_name' => null]);

        $matches = ApplicantDuplicateChecker::find($this->agency->id, [
            'first_name' => 'MARY',
            'last_name' => 'CORTEZ',
        ]);

        $this->assertCount(0, $matches);
    }

    #[Test]
    public function middle_name_differences_are_ignored(): void
    {
        $this->existing(['first_name' => 'MARY', 'middle_name' => null]);

        $matches = ApplicantDuplicateChecker::find($this->agency->id, [
            'first_name' => 'MARY',
            'middle_name' => 'ANN',
            'last_name' => 'CORTEZ',
        ]);

        $this->assertCount(1, $matches);
        // Exact name is reported once — never as both exact and similar.
        $reasons = $matches->first()['reasons'];
        $this->assertCount(1, $reasons);
        $this->assertStringContainsString('Same name', $reasons[0]);
    }

    #[Test]
    public function store_is_blocked_with_a_rich_payload_and_confirm_duplicate_proceeds(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Cebu Branch']);
        $agentModel = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Rosa Agent']);
        $position = Position::factory()->create(['name' => 'Domestic Helper']);
        $country = Country::factory()->create(['name' => 'Qatar']);

        $this->existing([
            'contact' => '09171234567',
            'gender' => 'female',
            'birthdate' => now()->subYears(30)->format('Y-m-d'),
            'branch_id' => $branch->id,
            'agent_id' => $agentModel->id,
            'position_id' => $position->id,
            'country_id' => $country->id,
            'fra' => 'for_fra',
            'status_code' => StatusCode::query()->min('code'),
        ]);

        $payload = [
            'first_name' => 'MARY',
            'last_name' => 'CORTEZ',
            'gender' => 'female',
        ];

        $response = $this->actingAs($this->user)->post(route('applicants.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['duplicate']);
        $dupes = session('duplicate_applicants');
        $this->assertNotEmpty($dupes);

        // The bounced page must render the detail table and a working
        // "Create anyway" button (this button was missing entirely).
        $this->get(route('applicants.create'))
            ->assertOk()
            ->assertSee('Possible duplicate applicant')
            ->assertSee('Create anyway')
            ->assertSee('FRA Status');

        $dupe = $dupes[0];
        foreach (['name', 'contact', 'gender', 'age', 'branch', 'agent', 'position', 'country', 'fra', 'status', 'url', 'reasons'] as $key) {
            $this->assertArrayHasKey($key, $dupe, "Duplicate payload is missing '{$key}'.");
        }
        $this->assertSame('MARRY ANN CORTEZ', trim($dupe['name']));
        $this->assertSame('09171234567', $dupe['contact']);
        $this->assertSame('female', $dupe['gender']);
        $this->assertSame('30', $dupe['age']);
        $this->assertSame('Cebu Branch', $dupe['branch']);
        $this->assertSame('Rosa Agent', $dupe['agent']);
        $this->assertSame('Domestic Helper', $dupe['position']);
        $this->assertSame('Qatar', $dupe['country']);
        $this->assertSame('For FRA', $dupe['fra']);

        $this->assertSame(0, Applicant::where('first_name', 'MARY')->count());

        // "Create anyway"
        $ok = $this->actingAs($this->user)->post(route('applicants.store'), array_merge($payload, ['confirm_duplicate' => 1]));
        $ok->assertRedirect(route('applicants.index'));
        $this->assertSame(1, Applicant::where('first_name', 'MARY')->count());
    }

    #[Test]
    public function bulk_row_fuzzy_duplicate_of_an_existing_applicant_is_a_row_error(): void
    {
        $this->existing();

        $csv = "First Name,Last Name\n"
            ."MARY,CORTEZ\n";

        $response = $this->actingAs($this->user)->post(route('applicants.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('applicants.csv', $csv),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('bulk_errors');
        $errors = session('bulk_errors');
        $this->assertStringContainsString('Possible duplicate', $errors[0]['errors'][0]);
        $this->assertSame(0, Applicant::where('first_name', 'MARY')->count());
    }
}
