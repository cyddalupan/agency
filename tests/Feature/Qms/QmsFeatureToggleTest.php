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

    // ── the switch moved to the operator (card 6b03439e6043673862503f2b) ──
    //
    // The tenant's own /settings/agency screen is gone: QMS is entitlement,
    // not branding, so only the god-plane settings editor writes it. The
    // tests below keep the original intent — "the switch saves and the whole
    // QMS family follows" — and add the half that did not exist before: an
    // agency CANNOT flip its own switch any more.

    #[Test]
    public function the_operator_settings_editor_saves_the_switch_and_the_qms_family_follows(): void
    {
        $gulf = $this->agencyWith('gulf');

        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->id));

        $this->post(route('godmode.agencies.settings.update', $gulf), $this->godPayload($gulf, 'on'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(ModuleAccess::qmsEnabled($gulf->fresh()->id));

        // The god session is still on the god guard; a tenant request needs the
        // web guard and the tenant host, as the original test did.
        $admin = $this->adminFor($gulf);
        $this->actingAs($admin, 'web');
        $this->call('GET', 'http://'.$this->tenantHost('gulf').'/qms')->assertOk();

        // Back to the god guard, on the main host, with a fresh document read.
        // Back to the god guard, reading the FRESH document for the version.
        $gulf->refresh();
        $payload = $this->godPayload($gulf, 'off');
        $this->post('http://localhost'.parse_url(route('godmode.agencies.settings.update', $gulf), PHP_URL_PATH), $payload)
            ->assertSessionHasNoErrors();

        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->fresh()->id));

        $this->actingAs($admin, 'web');
        $this->call('GET', 'http://'.$this->tenantHost('gulf').'/qms')->assertForbidden();
    }

    #[Test]
    public function the_operator_editor_reports_default_without_a_stored_key(): void
    {
        $finas = $this->agencyWith('finas');

        $state = app(\App\Services\GodMode\GodAgencySettingsService::class)->formState($finas);

        $this->assertSame('default', $state['qms_enabled'], 'finas resolves on with no stored key');
        $this->assertTrue(ModuleAccess::qmsEnabled($finas->id));
    }

    #[Test]
    public function saving_other_agency_settings_does_not_clobber_the_qms_switch(): void
    {
        $gulf = $this->agencyWith('gulf');
        $gulf->update(['settings' => ['color_theme' => 'midnight', 'banner_color' => '#220dbf']]);

        // Move an unrelated key (all_caps) and leave QMS on Default.
        $this->post(route('godmode.agencies.settings.update', $gulf), $this->godPayload($gulf, 'default', ['all_caps' => '1']))
            ->assertSessionHasNoErrors();

        $settings = $gulf->fresh()->settings;

        $this->assertEquals('midnight', $settings['color_theme'] ?? null);
        $this->assertEquals('#220dbf', $settings['banner_color'] ?? null);
        $this->assertArrayNotHasKey(ModuleAccess::QMS_SETTINGS_KEY, $settings, 'Default must not write a key');
        $this->assertTrue((bool) ($settings['all_caps'] ?? false));
    }

    #[Test]
    public function a_tenant_admin_can_no_longer_toggle_its_own_qms(): void
    {
        $gulf = $this->agencyWith('gulf');
        $admin = $this->adminFor($gulf);

        // The route is gone: 404 on a tenant host, not a 403 with a form.
        $this->actingAs($admin)
            ->get('/settings/agency')
            ->assertNotFound();

        // And it cannot be smuggled back through the old form action.
        $this->actingAs($admin)
            ->post('/settings/agency', ['qms_enabled' => '1', 'show_company_profile' => '1'])
            ->assertNotFound();

        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->fresh()->id));
    }

    private function tenantHost(string $slug = 'gulf'): string
    {
        return $slug.'.fixitautoservices.com';
    }

    /** The god-plane settings payload, mirroring the rendered form. */
    private function godPayload(Agency $agency, string $qms, array $overrides = []): array
    {
        $svc = app(\App\Services\GodMode\GodAgencySettingsService::class);
        $state = $svc->formState($agency);

        $god = User::factory()->create([
            'agency_id' => null,
            'username'  => 'qms-operator-'.substr(md5(uniqid('', true)), 0, 8),
            'email'     => 'op'.substr(md5(uniqid('', true)), 0, 8).'@godmode.local',
            'user_type' => 'super_admin',
            'status'    => 'active',
            'created_at' => now()->subYear(),
        ]);

        $this->actingAs($god, 'godmode')->withSession([
            'godmode.started_at'       => now(),
            'godmode.last_activity_at' => now(),
        ]);

        return array_merge([
            'settings_version'     => $state['settings_version'],
            'qms_enabled'          => $qms,
            'hide_type_tab_counts' => $state['hide_type_tab_counts'] ? '1' : null,
            'enable_firstimer'     => $state['enable_firstimer'] ? '1' : null,
            'complaint_sla'        => [
                'ack_days'     => $state['ack_days'],
                'resolve_days' => $state['resolve_days'],
            ],
            'color_theme'          => $state['color_theme'],
            'all_caps'             => $state['all_caps'] ? '1' : null,
            'banner_color'         => $state['banner_color'] !== '' ? $state['banner_color'] : null,
            'show_company_profile' => $state['show_company_profile'] ? '1' : null,
        ], $overrides);
    }
}