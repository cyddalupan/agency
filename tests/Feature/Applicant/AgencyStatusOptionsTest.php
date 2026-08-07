<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "LANDAS PI: 8 item 3" — per-agency settings for FRA + Status dropdown options
 * on the Status tab, reusing the applicant_form_defaults pattern (no hardcoded
 * FRA/Status lists in the Status tab; options come from agencies.settings).
 */
class AgencyStatusOptionsTest extends TestCase
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

    private function applicant(): Applicant
    {
        return Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
        ]);
    }

    private function statusTabHtml(Applicant $a): string
    {
        return $this->actingAs($this->user)
            ->get(route('applicants.show', $a))
            ->assertOk()
            ->getContent();
    }

    private function setDefaults(array $defaults): void
    {
        $settings = $this->agency->settings ?? [];
        $settings['applicant_form_defaults'] = $defaults;
        $this->agency->update(['settings' => $settings]);
    }

    // ---- FRA options ----

    #[Test]
    public function default_config_includes_fra_options(): void
    {
        $config = app_applicant_form_defaults($this->agency);

        $this->assertArrayHasKey('fra_options', $config);
        $this->assertContains('none', $config['fra_options']);
        $this->assertContains('for_fra', $config['fra_options']);
        $this->assertContains('fra_completed', $config['fra_options']);
    }

    #[Test]
    public function status_tab_renders_all_default_fra_options_when_unconfigured(): void
    {
        $html = $this->statusTabHtml($this->applicant());

        $this->assertStringContainsString('No FRA', $html);
        $this->assertStringContainsString('For FRA', $html);
        $this->assertStringContainsString('FRA Completed', $html);
    }

    #[Test]
    public function status_tab_renders_only_agency_configured_fra_options(): void
    {
        $this->setDefaults([
            'fra_options' => ['none', 'for_fra'],
            'status_codes' => [0],
        ]);

        $html = $this->statusTabHtml($this->applicant());

        $this->assertStringContainsString('No FRA', $html);
        $this->assertStringContainsString('For FRA', $html);
        $this->assertStringNotContainsString('FRA Completed', $html);
    }

    #[Test]
    public function status_update_rejects_fra_value_not_in_agency_options(): void
    {
        $this->setDefaults([
            'fra_options' => ['none', 'for_fra'],
            'status_codes' => [0],
        ]);
        $a = $this->applicant();

        $resp = $this->actingAs($this->user)->from(route('applicants.show', $a))->patch(
            route('applicants.status', $a),
            [
                'status_code' => 0,
                'fra'         => 'fra_completed',
            ]
        );

        $resp->assertSessionHasErrors('fra');
        $this->assertDatabaseHas('applicants', [
            'id' => $a->id,
            'fra' => null,
        ]);
    }

    #[Test]
    public function status_update_accepts_fra_value_in_agency_options(): void
    {
        $this->setDefaults([
            'fra_options' => ['none', 'for_fra', 'fra_completed'],
            'status_codes' => [0],
        ]);
        $a = $this->applicant();

        $resp = $this->actingAs($this->user)->from(route('applicants.show', $a))->patch(
            route('applicants.status', $a),
            [
                'status_code' => 0,
                'fra'         => 'for_fra',
            ]
        );

        $resp->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id' => $a->id,
            'fra' => 'for_fra',
        ]);
    }

    // ---- Status options (Status tab, per-agency) ----

    #[Test]
    public function status_tab_renders_all_statuses_when_none_configured(): void
    {
        $this->makeStatus(900, 'Only A');
        $html = $this->statusTabHtml($this->applicant());

        $this->assertStringContainsString('Only A', $html);
    }

    #[Test]
    public function status_tab_renders_only_agency_configured_statuses(): void
    {
        $this->makeStatus(900, 'Only A');
        $this->makeStatus(901, 'Only B');
        $this->setDefaults([
            'status_codes' => [900],
            'fra_options' => ['none', 'for_fra', 'fra_completed'],
        ]);

        $html = $this->statusTabHtml($this->applicant());

        $this->assertStringContainsString('Only A', $html);
        $this->assertStringNotContainsString('Only B', $html);
    }

    // ---- Settings persistence ----

    #[Test]
    public function settings_persist_fra_options(): void
    {
        $resp = $this->actingAs($this->user)->from(route('settings.applicant-form-defaults'))->post(
            route('settings.applicant-form-defaults.update'),
            [
                'sources'    => ['Facebook'],
                'fra_options' => ['none', 'for_fra'],
            ]
        );

        $resp->assertSessionHasNoErrors();

        $this->agency->refresh();
        $config = app_applicant_form_defaults($this->agency);
        $this->assertSame(['none', 'for_fra'], $config['fra_options']);
    }

    private function makeStatus(int $code, string $label): StatusCode
    {
        return StatusCode::create([
            'code' => $code,
            'label' => $label,
            'sort_order' => $code,
        ]);
    }
}
