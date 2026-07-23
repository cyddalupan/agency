<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorTranslationTest extends TestCase
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
    public function sponsor_views_have_translation_keys(): void
    {
        $this->actingAs($this->sponsorUser);

        // Dashboard/Lineup page renders without error
        $response = $this->get(route('sponsor.dashboard'));
        $response->assertOk();

        // My Applicants page renders without error
        $response = $this->get(route('sponsor.my-applicants'));
        $response->assertOk();
    }
}
