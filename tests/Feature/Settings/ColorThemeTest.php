<?php

namespace Tests\Feature\Settings;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir "FINAS: SETTINGS — Add Color Theme settings".
 *
 * Agencies can pick a Color Theme (Settings → Color Theme) that rewrites the
 * system's DaisyUI colour tokens for everyone under that agency.
 */
class ColorThemeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function admin_can_access_the_color_theme_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.color-theme'))
            ->assertOk()
            ->assertViewIs('settings.color-theme')
            ->assertSee('Color Theme');
    }

    #[Test]
    public function staff_cannot_access_the_color_theme_page(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)
            ->get(route('settings.color-theme'))
            ->assertForbidden();
    }

    #[Test]
    public function settings_index_links_to_the_color_theme_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee(route('settings.color-theme'));
    }

    #[Test]
    public function admin_can_set_a_color_theme_for_their_agency(): void
    {
        $this->withoutMiddleware();

        $this->actingAs($this->admin)
            ->post(route('settings.color-theme.update'), ['color_theme' => 'ocean'])
            ->assertRedirect(route('settings.color-theme'));

        $this->agency->refresh();
        $this->assertEquals('ocean', app_color_theme($this->agency));

        $style = app_color_theme_style($this->agency);
        $this->assertStringContainsString('--color-primary:#2563eb;', $style);
        $this->assertStringContainsString('--color-secondary:#0ea5e9;', $style);
    }

    #[Test]
    public function admin_can_reset_to_the_default_theme(): void
    {
        $this->withoutMiddleware();

        $this->actingAs($this->admin)
            ->post(route('settings.color-theme.update'), ['color_theme' => 'royal'])
            ->assertRedirect(route('settings.color-theme'));

        $this->actingAs($this->admin)
            ->post(route('settings.color-theme.update'), ['color_theme' => 'default'])
            ->assertRedirect(route('settings.color-theme'));

        $this->agency->refresh();
        $this->assertEquals('default', app_color_theme($this->agency));
        $this->assertEquals('', app_color_theme_style($this->agency));
    }

    #[Test]
    public function unknown_color_theme_is_rejected(): void
    {
        // Keep the session middleware (so flashed validation errors are visible)
        // but skip CSRF, which the test env rejects for token-less POSTs.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->actingAs($this->admin)
            ->post(route('settings.color-theme.update'), ['color_theme' => 'not-a-theme'])
            ->assertSessionHasErrors('color_theme');

        $this->agency->refresh();
        $this->assertEquals('default', app_color_theme($this->agency));
    }

    #[Test]
    public function selected_theme_is_rendered_into_the_site_head(): void
    {
        $this->withoutMiddleware();

        $this->actingAs($this->admin)
            ->post(route('settings.color-theme.update'), ['color_theme' => 'sunset']);

        $html = $this->actingAs($this->admin)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('--color-primary:#ea580c;', $html);
        $this->assertStringContainsString(':root', $html);
    }

    #[Test]
    public function default_theme_emits_no_override(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('--color-primary:#ea580c;', $html);
        $this->assertStringNotContainsString('--color-primary:#2563eb;', $html);
    }
}
