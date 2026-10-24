<?php

namespace Tests\Feature\Settings;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "Add Applicant: Source" — agencies can ADD their own sources in
 * Settings (not only tick the six built-ins), and the added sources show up on
 * the Add/Edit Applicant form.
 */
class ApplicantSourcesTest extends TestCase
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

    private function saveDefaults(array $payload)
    {
        // Only CSRF is skipped (token-less POSTs). Other middleware must stay:
        // ShareErrorsFromSession provides $errors to the settings view.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        return $this->actingAs($this->admin)
            ->post(route('settings.applicant-form-defaults.update'), $payload);
    }

    private function savedConfig(): array
    {
        return (array) ($this->agency->fresh()->settings['applicant_form_defaults'] ?? []);
    }

    #[Test]
    public function admin_can_add_a_custom_source_and_it_persists(): void
    {
        $this->saveDefaults([
            'sources'     => ['Facebook', 'Referral'],
            'new_sources' => 'TikTok',
        ])->assertRedirect(route('settings.applicant-form-defaults'));

        $config = $this->savedConfig();

        $this->assertContains('TikTok', $config['sources']);
        $this->assertContains('TikTok', $config['custom_sources']);
        $this->assertSame(['Facebook', 'Referral'], array_values(array_intersect(['Facebook', 'Referral'], $config['sources'])));
    }

    #[Test]
    public function the_settings_page_lists_custom_sources(): void
    {
        $this->saveDefaults(['sources' => ['Facebook'], 'new_sources' => 'Job Fair']);

        $response = $this->actingAs($this->admin)->get(route('settings.applicant-form-defaults'));

        $response->assertOk();
        $response->assertSee('Job Fair');
        $response->assertSee('Add source');
    }

    #[Test]
    public function a_custom_source_appears_on_the_add_applicant_form(): void
    {
        $this->saveDefaults(['sources' => ['Facebook'], 'new_sources' => 'TikTok']);

        $response = $this->actingAs($this->admin)->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('TikTok');
    }

    #[Test]
    public function a_custom_source_can_be_used_on_a_new_applicant(): void
    {
        $this->saveDefaults(['sources' => ['Facebook'], 'new_sources' => 'TikTok']);

        $resp = $this->actingAs($this->admin)->post(route('applicants.store'), [
            'first_name' => 'Source',
            'last_name'  => 'Probe',
            'source'     => 'TikTok',
            'contact'    => '09171234567',
        ]);

        $resp->assertRedirect(route('applicants.index'));

        $this->assertDatabaseHas('applicants', [
            'agency_id' => $this->agency->id,
            'source'    => 'TikTok',
        ]);
    }

    #[Test]
    public function duplicates_blanks_and_markup_are_ignored_when_adding_sources(): void
    {
        $this->saveDefaults([
            'sources'     => ['Facebook'],
            // facebook = case-duplicate of a built-in, "" = blank, tags = markup
            'new_sources' => 'facebook, , <b>Flyer</b>, Flyer,  WALK-IN ',
        ]);

        $config = $this->savedConfig();
        $customs = $config['custom_sources'];

        $this->assertSame(['Flyer'], array_values($customs));
        // Built-ins are not duplicated into the list.
        $this->assertSame(1, count(array_keys($config['sources'], 'Facebook')));
        $this->assertSame(1, count(array_keys($config['sources'], 'Walk-in')));
        $this->assertNotContains('', $config['sources']);
    }

    #[Test]
    public function unticking_a_custom_source_removes_it(): void
    {
        $this->saveDefaults(['sources' => ['Facebook'], 'new_sources' => 'TikTok']);
        $this->assertContains('TikTok', $this->savedConfig()['sources']);

        // Second save keeps Facebook only — TikTok gets unticked.
        $this->saveDefaults(['sources' => ['Facebook'], 'new_sources' => '']);

        $config = $this->savedConfig();
        $this->assertNotContains('TikTok', $config['sources']);
        $this->assertNotContains('TikTok', $config['custom_sources']);
    }
}
