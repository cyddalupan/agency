<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgencyBrandingTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        Storage::fake('public');
    }

    // ─── ACCESS: BRANDING SETTINGS PAGE ──────────────────────────────

    #[Test]
    public function admin_can_access_branding_page(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('agencies.branding', $this->agency));

        $response->assertOk();
        $response->assertViewIs('agencies.branding');
        $response->assertViewHas('agency');
    }

    #[Test]
    public function super_admin_can_access_branding_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetAgency = Agency::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.branding', $targetAgency));

        $response->assertOk();
        $response->assertViewIs('agencies.branding');
    }

    #[Test]
    public function staff_cannot_access_branding_page(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('agencies.branding', $this->agency));

        $response->assertForbidden();
    }

    #[Test]
    public function unauthenticated_user_is_redirected_from_branding_page(): void
    {
        $response = $this->get(route('agencies.branding', $this->agency));

        $response->assertRedirect(route('login'));
    }

    // ─── LOGO UPLOAD ──────────────────────────────────────────────────

    #[Test]
    public function admin_can_upload_agency_logo(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $logo = UploadedFile::fake()->image('agency-logo.png', 200, 200);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'logo' => $logo,
            ]);

        $response->assertRedirect(route('agencies.branding', $this->agency));
        $response->assertSessionHas('success');

        $this->agency->refresh();
        $this->assertNotNull($this->agency->logo);

        Storage::disk('public')->assertExists($this->agency->logo);
    }

    #[Test]
    public function logo_upload_rejects_non_image_files(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $fakePdf = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'logo' => $fakePdf,
            ]);

        $response->assertSessionHasErrors('logo');
    }

    #[Test]
    public function logo_upload_rejects_oversized_files(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $hugeLogo = UploadedFile::fake()->image('huge-logo.png')
            ->size(3000); // 3MB, over 2MB limit

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'logo' => $hugeLogo,
            ]);

        $response->assertSessionHasErrors('logo');
    }

    #[Test]
    public function logo_upload_is_optional(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color'   => '#3490dc',
                'secondary_color' => '#38c172',
            ]);

        $response->assertRedirect(route('agencies.branding', $this->agency));
        $response->assertSessionHas('success');
    }

    // ─── FAVICON UPLOAD ──────────────────────────────────────────────

    #[Test]
    public function admin_can_upload_favicon(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $favicon = UploadedFile::fake()->image('favicon.ico', 32, 32);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'favicon' => $favicon,
            ]);

        $response->assertRedirect(route('agencies.branding', $this->agency));

        $this->agency->refresh();
        $settings = $this->agency->settings;
        $this->assertNotNull($settings['favicon'] ?? null);

        Storage::disk('public')->assertExists($settings['favicon']);
    }

    #[Test]
    public function favicon_upload_rejects_non_image_files(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $textFile = UploadedFile::fake()->create('note.txt', 50);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'favicon' => $textFile,
            ]);

        $response->assertSessionHasErrors('favicon');
    }

    #[Test]
    public function favicon_upload_rejects_oversized_files(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $hugeFavicon = UploadedFile::fake()->image('big.ico')
            ->size(600); // 600KB, over 500KB limit

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'favicon' => $hugeFavicon,
            ]);

        $response->assertSessionHasErrors('favicon');
    }

    #[Test]
    public function favicon_upload_is_optional(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), []);

        $response->assertRedirect(route('agencies.branding', $this->agency));
    }

    // ─── COLOR SETTINGS ──────────────────────────────────────────────

    #[Test]
    public function admin_can_update_primary_and_secondary_colors(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color'   => '#3490dc',
                'secondary_color' => '#38c172',
            ]);

        $response->assertRedirect(route('agencies.branding', $this->agency));

        $this->agency->refresh();
        $settings = $this->agency->settings;
        $this->assertEquals('#3490dc', $settings['primary_color'] ?? null);
        $this->assertEquals('#38c172', $settings['secondary_color'] ?? null);
    }

    #[Test]
    public function primary_color_must_be_valid_hex(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color' => 'not-a-color',
            ]);

        $response->assertSessionHasErrors('primary_color');
    }

    #[Test]
    public function primary_color_can_be_three_or_six_digit_hex(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // 3-digit hex
        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color'   => '#abc',
                'secondary_color' => '#abc123',
            ]);

        $response->assertSessionDoesntHaveErrors(['primary_color', 'secondary_color']);
    }

    #[Test]
    public function colors_require_hash_prefix(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color' => '3490dc',
            ]);

        $response->assertSessionHasErrors('primary_color');
    }

    #[Test]
    public function secondary_color_is_optional(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color' => '#3490dc',
            ]);

        $response->assertSessionDoesntHaveErrors('secondary_color');
    }

    // ─── AUTHORIZATION ────────────────────────────────────────────────

    #[Test]
    public function staff_cannot_update_branding(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)
            ->put(route('agencies.branding.update', $this->agency), [
                'primary_color' => '#ff0000',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function super_admin_can_update_branding_for_any_agency(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetAgency = Agency::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->put(route('agencies.branding.update', $targetAgency), [
                'primary_color'   => '#dc3545',
                'secondary_color' => '#6610f2',
            ]);

        $response->assertRedirect(route('agencies.branding', $targetAgency));

        $targetAgency->refresh();
        $settings = $targetAgency->settings;
        $this->assertEquals('#dc3545', $settings['primary_color'] ?? null);
        $this->assertEquals('#6610f2', $settings['secondary_color'] ?? null);
    }

    // ─── BRANDING PREVIEW ─────────────────────────────────────────────

    #[Test]
    public function branding_page_shows_current_logo(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // Set a logo on the agency
        $this->agency->update(['logo' => 'logos/test-logo.png']);

        $response = $this->actingAs($admin)
            ->get(route('agencies.branding', $this->agency));

        $response->assertOk();
        $response->assertSee('logos/test-logo.png');
    }

    #[Test]
    public function branding_page_shows_current_colors(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->agency->update([
            'settings' => [
                'primary_color'   => '#3490dc',
                'secondary_color' => '#38c172',
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('agencies.branding', $this->agency));

        $response->assertOk();
        // Verify the page contains the color values
        $response->assertSee('#3490dc');
        $response->assertSee('#38c172');
    }

    // ─── DEFAULTS ─────────────────────────────────────────────────────

    #[Test]
    public function agency_branding_starts_with_no_logo_and_defaults(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('agencies.branding', $this->agency));

        $response->assertOk();
        $this->assertNull($this->agency->logo);
    }

    #[Test]
    public function logo_upload_replaces_old_logo(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // Upload first logo
        $oldLogo = UploadedFile::fake()->image('old-logo.png');
        $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'logo' => $oldLogo,
            ]);

        $oldPath = $this->agency->fresh()->logo;

        // Upload second logo
        $newLogo = UploadedFile::fake()->image('new-logo.png');
        $this->actingAs($admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'logo' => $newLogo,
            ]);

        $newPath = $this->agency->fresh()->logo;

        $this->assertNotEquals($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }
}
