<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorLineupTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $sponsorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['status' => 'active']);

        $this->sponsorUser = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Test Sponsor',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'SPONSOR-001',
            'company_name' => 'Test Sponsor Co',
            'email'        => 'sponsor@test.com',
            'status'       => 'active',
        ]);
    }

    #[Test]
    public function lineup_shows_position_filter_pills(): void
    {
        $this->actingAs($this->sponsorUser);

        // Create a position (reference table) and applicant so filter pills render
        $position = \App\Models\Position::create(['name' => 'Nurse']);

        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'position_id' => $position->id,
            'status_code' => 0,
            'status'      => 'active',
        ]);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertOk();
        // Filter pills should exist (the "All" pill)
        $response->assertSee('All', false);
    }

    #[Test]
    public function lineup_has_export_button(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertOk();
        $response->assertSee('Export');
    }

    #[Test]
    public function lineup_export_returns_csv(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.lineup.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function lineup_can_be_filtered_by_position(): void
    {
        $this->actingAs($this->sponsorUser);

        // Create a position
        \App\Models\JobPosition::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Nurse',
        ]);

        $response = $this->get(route('sponsor.dashboard', ['position' => 'Nurse']));

        $response->assertOk();
    }

    #[Test]
    public function lineup_with_no_applicants_shows_empty_state(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertOk();
        $response->assertSee('No applicants');
    }
}
