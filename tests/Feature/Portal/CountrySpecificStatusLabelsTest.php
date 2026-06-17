<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\Country;
use App\Models\StatusCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountrySpecificStatusLabelsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private StatusCode $status;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        $this->status = StatusCode::create([
            'code'        => 'visa_stamped',
            'label'       => 'Visa Received',
            'label_saudi' => 'Visa Stamped',
            'description' => 'Visa has been processed',
            'color'       => '#22c55e',
            'sort_order'  => 5,
        ]);
    }

    // === Saudi Country Shows Saudi-Specific Label ===

    #[Test]
    public function saudi_applicant_shows_label_saudi_on_dashboard(): void
    {
        $saudiCountry = Country::factory()->create(['name' => 'Saudi Arabia']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'country_id'  => $saudiCountry->id,
            'status_code' => 'visa_stamped',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Visa Stamped');
        $response->assertDontSee('Visa Received');
    }

    #[Test]
    public function saudi_applicant_shows_label_saudi_on_profile(): void
    {
        $saudiCountry = Country::factory()->create(['name' => 'Saudi Arabia']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'country_id'  => $saudiCountry->id,
            'status_code' => 'visa_stamped',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Visa Stamped');
        $response->assertDontSee('Visa Received');
    }

    // === Non-Saudi Country Shows Regular Label ===

    #[Test]
    public function non_saudi_applicant_shows_regular_label_on_dashboard(): void
    {
        $uaeCountry = Country::factory()->create(['name' => 'United Arab Emirates']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'country_id'  => $uaeCountry->id,
            'status_code' => 'visa_stamped',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Visa Received');
        $response->assertDontSee('Visa Stamped');
    }

    #[Test]
    public function non_saudi_applicant_shows_regular_label_on_profile(): void
    {
        $uaeCountry = Country::factory()->create(['name' => 'United Arab Emirates']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'country_id'  => $uaeCountry->id,
            'status_code' => 'visa_stamped',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.profile'));

        $response->assertOk();
        $response->assertSee('Visa Received');
        $response->assertDontSee('Visa Stamped');
    }

    #[Test]
    public function status_with_no_saudi_label_falls_back_to_regular_label(): void
    {
        $statusNoSaudi = StatusCode::create([
            'code'        => 'new',
            'label'       => 'New Applicant',
            'label_saudi' => null,
            'color'       => '#3b82f6',
            'sort_order'  => 1,
        ]);

        $saudiCountry = Country::factory()->create(['name' => 'Saudi Arabia']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'country_id'  => $saudiCountry->id,
            'status_code' => 'new',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('New Applicant');
    }

    #[Test]
    public function applicant_with_no_country_shows_regular_label(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'country_id'  => null,
            'status_code' => 'visa_stamped',
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Visa Received');
    }
}
