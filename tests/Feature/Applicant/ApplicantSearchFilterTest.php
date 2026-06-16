<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantSearchFilterTest extends TestCase
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

        app()->instance('tenant_agency', $this->agency);
    }

    // ──────────── SEARCH BY NAME ────────────

    #[Test]
    public function can_search_applicants_by_first_name(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['search' => 'Maria']));

        $response->assertOk();
        $response->assertSee('Maria Santos');
        $this->assertCount(1, $response->viewData('applicants'));
    }

    #[Test]
    public function can_search_applicants_by_last_name(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['search' => 'Dela Cruz']));

        $response->assertOk();
        $response->assertSee('Juan Dela Cruz');
        $this->assertCount(1, $response->viewData('applicants'));
    }

    #[Test]
    public function search_is_case_insensitive(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'RODRIGO',
            'last_name' => 'Duterte',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['search' => 'rodrigo']));

        $response->assertOk();
        $response->assertSee('RODRIGO Duterte');
        $this->assertCount(1, $response->viewData('applicants'));
    }

    #[Test]
    public function search_matches_partial_name(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Johnny',
            'last_name' => 'Walker',
        ]);
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['search' => 'John']));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('applicants'));
    }

    // ──────────── FILTER BY STATUS ────────────

    #[Test]
    public function can_filter_applicants_by_status_code(): void
    {
        Applicant::factory()->withStatus(0)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Pending',
        ]);
        Applicant::factory()->withStatus(1)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Interview',
        ]);
        Applicant::factory()->withStatus(8)->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Deployed',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 1]));

        $response->assertOk();
        $response->assertSeeText('Interview');
        $this->assertCount(1, $response->viewData('applicants'));
        // Verify only the Interview applicant is returned
        $this->assertEquals('Interview', $response->viewData('applicants')->first()->first_name);
    }

    // ──────────── FILTER BY GENDER ────────────

    #[Test]
    public function can_filter_applicants_by_gender(): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
            'gender' => 'male',
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
            'gender' => 'female',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['gender' => 'female']));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('applicants'));
    }

    // ──────────── COMBINED SEARCH + FILTER ────────────

    #[Test]
    public function can_combine_search_and_filters(): void
    {
        // Male, named Maria, pending
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'gender' => 'female',
            'status_code' => 0,
        ]);
        // Male, named Maria, deployed
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'gender' => 'female',
            'status_code' => 8,
        ]);
        // Male, named Juan, pending
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'gender' => 'male',
            'status_code' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', [
                'search' => 'Maria',
                'gender' => 'female',
                'status' => 8,
            ]));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('applicants'));
    }

    // ──────────── NO RESULTS ────────────

    #[Test]
    public function search_with_no_matches_shows_empty(): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['search' => 'xxxxnotfoundxxxx']));

        $response->assertOk();
        $this->assertCount(0, $response->viewData('applicants'));
    }

    // ──────────── FILTER CLEARS ────────────

    #[Test]
    public function no_filters_shows_all_applicants(): void
    {
        Applicant::factory()->count(5)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertOk();
        $this->assertCount(5, $response->viewData('applicants'));
    }
}
