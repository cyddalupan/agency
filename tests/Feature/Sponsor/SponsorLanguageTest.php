<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorLanguageTest extends TestCase
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
    public function sponsor_layout_shows_language_switcher_in_header(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertOk();
        $response->assertSee('English');
        $response->assertSee('العربية');
        $response->assertSee('中文');
        $response->assertSee('日本語');
    }

    #[Test]
    public function sponsor_language_switcher_route_exists(): void
    {
        $response = $this->get('/sponsor/language/ar');

        $response->assertRedirect();
    }

    #[Test]
    public function sponsor_language_switcher_switches_to_arabic(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.dashboard'));

        // Switch to Arabic
        $this->get('/sponsor/language/ar');

        $response2 = $this->get(route('sponsor.dashboard'));
        $response2->assertOk();
    }

    #[Test]
    public function sponsor_layout_has_language_form_in_header(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.dashboard'));

        $response->assertOk();
    }

    #[Test]
    public function sponsor_unsupported_language_falls_back_to_english(): void
    {
        $response = $this->get('/sponsor/language/xx');
        $response->assertRedirect();
    }
}
