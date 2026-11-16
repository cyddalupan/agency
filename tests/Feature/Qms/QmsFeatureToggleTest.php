<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6afb2e83 (2026-11-16).
 *
 * "All the QMS feature should be toggle in the settings and it should be
 * turned on on Finas and off in gulf for now."
 *
 * The acceptance is about the whole feature, not one screen: with QMS off,
 * every QMS module is closed to everyone including admins, and no QMS link is
 * rendered. So the tests below iterate the real module list and the real
 * sidebar route list rather than naming one screen.
 */
class QmsFeatureToggleTest extends TestCase
{
    use RefreshDatabase;

    private function agencyWith(string $subdomain, ?bool $QmsEnabled = null): Agency
    {
        $agency = Agency::factory()->create(['subdomain' => $subdomain]);

        if ($QmsEnabled !== null) {
            $agency->update(['settings' => [ModuleAccess::QMS_SETTINGS_KEY => $QmsEnabled]]);
        }

        return $agency;
    }

    private function adminFor(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'super_admin',
        ]);
    }

    #[Test]
    public function finas_has_qms_on_by_default_and_gulf_has_it_off(): void
    {
        $finas = $this->agencyWith('finas');
        $gulf = $this->agencyWith('gulf');

        $this->assertTrue(ModuleAccess::qmsEnabled($finas->id));
        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->id));
    }

    #[Test]
    public function an_agency_with_no_setting_falls_back_to_the_default_list(): void
    {
        $other = $this->agencyWith('some-other-agency');

        $this->assertFalse(ModuleAccess::qmsEnabled($other->id));
        $this->assertFalse(ModuleAccess::qmsEnabled(null));
    }

    #[Test]
    public function the_agency_setting_overrides_the_default_either_way(): void
    {
        $finasOff = $this->agencyWith('finas', false);
        $gulfOn = $this->agencyWith('gulf', true);

        $this->assertFalse(ModuleAccess::qmsEnabled($finasOff->id));
        $this->assertTrue(ModuleAccess::qmsEnabled($gulfOn->id));
    }

    #[Test]
    public function every_qms_module_is_unreachable_for_an_admin_when_the_switch_is_off(): void
    {
        $gulf = $this->agencyWith('gulf');
        $admin = $this->adminFor($gulf);

        foreach (ModuleAccess::QMS_MODULES as $module) {
            $this->assertFalse(
                ModuleAccess::allows('super_admin', $module, $gulf->id),
                "{$module} should be closed for an admin on an agency with QMS off."
            );

            $this->assertFalse(
                $admin->canAccessModule($module),
                "{$module} should be hidden from this admin's sidebar."
            );
        }
    }

    #[Test]
    public function turning_the_switch_on_gives_the_admin_the_whole_qms_family_back(): void
    {
        $gulf = $this->agencyWith('gulf', true);
        $admin = $this->adminFor($gulf);

        foreach (ModuleAccess::QMS_MODULES as $module) {
            $this->assertTrue(ModuleAccess::allows('super_admin', $module, $gulf->id));
        }

        // A non-admin QMS role also gets its allowlisted modules back, not just admins.
        $manager = User::factory()->create([
            'agency_id' => $gulf->id,
            'user_type' => 'qms_manager',
        ]);

        $this->assertTrue($manager->canAccessModule('non_conformities'));
        $this->assertTrue($manager->canAccessModule('qms'));
    }

    #[Test]
    public function the_switch_does_not_touch_modules_outside_qms(): void
    {
        $gulf = $this->agencyWith('gulf');

        $this->assertTrue(ModuleAccess::allows('super_admin', 'applicants', $gulf->id));
        $this->assertTrue(ModuleAccess::allows('super_admin', 'settings', $gulf->id));
    }

    #[Test]
    public function the_qms_dashboard_is_a_403_on_an_agency_with_qms_off(): void
    {
        $gulf = $this->agencyWith('gulf');
        $admin = $this->adminFor($gulf);

        $this->actingAs($admin)
            ->get('/qms')
            ->assertForbidden();
    }

    #[Test]
    public function the_qms_dashboard_opens_on_an_agency_with_qms_on(): void
    {
        $finas = $this->agencyWith('finas');
        $admin = $this->adminFor($finas);

        $this->actingAs($admin)
            ->get('/qms')
            ->assertOk();
    }

    #[Test]
    public function every_qms_screen_is_forbidden_when_the_switch_is_off(): void
    {
        $gulf = $this->agencyWith('gulf');
        $admin = $this->adminFor($gulf);

        $paths = [
            '/audit-trail',
            '/controlled-documents',
            '/risks',
            '/complaints',
            '/non-conformities',
            '/internal-audits',
            '/compliance-calendar',
            '/qms',
        ];

        foreach ($paths as $path) {
            $this->actingAs($admin)->get($path)->assertForbidden();
        }
    }

    #[Test]
    public function the_sidebar_renders_no_qms_link_when_the_switch_is_off(): void
    {
        $gulf = $this->agencyWith('gulf');
        $admin = $this->adminFor($gulf);

        $html = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        foreach (['sidebar-qms-dashboard', 'audit_trail.index', 'risks.index',
                  'complaints.index', 'non_conformities.index', 'internal_audits.index',
                  'compliance_calendar.index', 'controlled_documents.index'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, "The sidebar still offers {$needle}.");
        }
    }

    #[Test]
    public function the_sidebar_renders_the_qms_links_when_the_switch_is_on(): void
    {
        $finas = $this->agencyWith('finas');
        $admin = $this->adminFor($finas);

        $html = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('sidebar-qms-dashboard', $html);
        $this->assertStringContainsString('/non-conformities', $html);
    }

    #[Test]
    public function the_settings_toggle_saves_the_switch_and_the_page_reflects_it(): void
    {
        $gulf = $this->agencyWith('gulf');
        $admin = $this->adminFor($gulf);

        // Off by default for Gulf, so the toggle renders unchecked.
        $html = $this->actingAs($admin)->get('/settings/agency')
            ->assertOk()
            ->assertSee('Quality Management (QMS)')
            ->getContent();
        $this->assertStringContainsString('id="toggle-qms-enabled"', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/id="toggle-qms-enabled"[^>]*checked/',
            $html,
            'The QMS toggle must not be checked for an agency that has the feature off.'
        );

        $this->actingAs($admin)
            ->post('/settings/agency', ['show_company_profile' => '1', 'qms_enabled' => '1'])
            ->assertRedirect('/settings/agency');

        $this->assertTrue(ModuleAccess::qmsEnabled($gulf->fresh()->id));

        $this->actingAs($admin)->get('/qms')->assertOk();

        // Saving again with the box cleared turns it back off.
        $this->actingAs($admin)
            ->post('/settings/agency', ['show_company_profile' => '1'])
            ->assertRedirect('/settings/agency');

        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->fresh()->id));
        $this->actingAs($admin)->get('/qms')->assertForbidden();
    }

    #[Test]
    public function the_toggle_renders_checked_for_an_agency_that_has_qms_on(): void
    {
        $finas = $this->agencyWith('finas');
        $admin = $this->adminFor($finas);

        $html = $this->actingAs($admin)->get('/settings/agency')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/id="toggle-qms-enabled"[^>]*checked|checked[^>]*id="toggle-qms-enabled"/',
            $html,
            'The QMS toggle should be checked for an agency that has the feature on.'
        );
    }

    #[Test]
    public function saving_agency_settings_does_not_clobber_other_agency_settings(): void
    {
        $gulf = $this->agencyWith('gulf');
        $gulf->update(['settings' => ['color_theme' => 'midnight']]);
        $admin = $this->adminFor($gulf);

        $this->actingAs($admin)
            ->post('/settings/agency', ['show_company_profile' => '1', 'qms_enabled' => '1'])
            ->assertRedirect('/settings/agency');

        $settings = $gulf->fresh()->settings;

        $this->assertEquals('midnight', $settings['color_theme'] ?? null);
        $this->assertTrue((bool) ($settings[ModuleAccess::QMS_SETTINGS_KEY] ?? false));
    }
}
