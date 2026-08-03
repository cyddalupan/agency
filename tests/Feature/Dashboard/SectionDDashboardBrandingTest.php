<?php

namespace Tests\Feature\Dashboard;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Section D — Dashboard (per-agency branding).
 *
 * D1: Render agency Name (agencies.name) instead of 'Default Agency' label
 * D2: Render agency Logo (agencies.logo) instead of 'Agency Super' heading
 * D3: Hide/remove default icon when agency logo present
 * D4: Wire dashboard to authenticated agency's branding (scope by agency_id)
 * D5: Smoke-test branding for Universe 1 (corporate) and Universe 2 (Landas) themes
 */
class SectionDDashboardBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Default to Universe 1 (corporate) unless a test overrides config.
        config(['app.universe' => 1]);
    }

    private function agencyWithLogo(string $name = 'Alpha Corp'): Agency
    {
        Storage::fake('public');

        $logoPath = 'logos/alpha-logo.png';
        Storage::disk('public')->put($logoPath, 'fake-png-bytes');

        return Agency::factory()->create([
            'name' => $name,
            'logo' => $logoPath,
        ]);
    }

    #[Test]
    public function dashboard_renders_authenticated_agency_name_not_default_label(): void
    {
        $agency = Agency::factory()->create(['name' => 'Manila Manpower']);
        $user = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->assertSee('Manila Manpower')
            ->assertDontSee('Default Agency');
    }

    #[Test]
    public function brand_helper_returns_authenticated_agency_name(): void
    {
        $agency = Agency::factory()->create(['name' => 'Cebu Staffing']);
        $user = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        $this->actingAs($user);

        $this->assertSame('Cebu Staffing', app_brand_name());
    }

    #[Test]
    public function dashboard_renders_agency_logo_image_when_present(): void
    {
        $agency = $this->agencyWithLogo('Alpha Corp');
        $user = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        $responseHtml = $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('alpha-logo.png', $responseHtml, 'Logo file URL rendered');
        $this->assertStringContainsString(Storage::url($agency->logo), $responseHtml, 'Logo public URL rendered');
    }

    #[Test]
    public function default_icon_is_hidden_when_agency_logo_present(): void
    {
        $agency = $this->agencyWithLogo('Alpha Corp');
        $user = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        $this->actingAs($user);

        $this->assertTrue(app_brand_has_logo(), 'Helper reports logo present');
        $this->assertFalse(app_brand_show_icon(), 'Icon hidden when logo present');
        $this->assertSame(Storage::url($agency->logo), app_brand_logo(), 'Logo URL helper');

        $html = $this->get(route('agency.dashboard'))->getContent();
        $this->assertStringContainsString('alpha-logo.png', $html, 'Logo img rendered on dashboard');
    }

    #[Test]
    public function agency_branding_is_scoped_to_authenticated_agency(): void
    {
        $this->agencyWithLogo('Alpha Corp');
        // Agency B has a different name and NO logo.
        $agencyB = Agency::factory()->create(['name' => 'Beta Agency', 'logo' => null]);
        $userB = User::factory()->create(['agency_id' => $agencyB->id, 'user_type' => 'admin']);

        $html = $this->actingAs($userB)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Beta Agency', $html, 'Shows its own agency name');
        $this->assertStringNotContainsString('Alpha Corp', $html, 'Does not leak other agency name');
        $this->assertStringNotContainsString('alpha-logo.png', $html, 'Does not leak other agency logo');
        $this->assertFalse(app_brand_has_logo(), 'No logo for agency B');
    }

    #[Test]
    public function brand_name_respects_universe_when_no_agency(): void
    {
        // No authenticated/tenanted agency -> falls back to universe brand name.
        config(['app.universe' => 2]);
        $user = User::factory()->create(['agency_id' => null, 'user_type' => 'super_admin']);

        $this->actingAs($user);

        $this->assertSame('LANDAS', app_brand_name());

        config(['app.universe' => 1]);
        $this->assertSame('Agency Super', app_brand_name());
    }
}
