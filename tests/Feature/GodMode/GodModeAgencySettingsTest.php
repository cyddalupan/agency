<?php

namespace Tests\Feature\GodMode;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Services\GodMode\GodAgencySettingsService;
use App\Services\GodMode\VersionMismatchException;
use App\Support\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b03439e6043673862503f2b — God Mode per-agency settings.
 *
 * Ordered by what fails SILENTLY when it is wrong (the card's §8):
 *
 *   6, 7, 8, 14  first — a dropped key, a replaced document, a key that was
 *                not deleted all leave a page that still renders "saved".
 *   15, 12-13    then — stale version and validation, both loud failures.
 *   16-19        then — the gate, 404s, no-store and the audit trail.
 *   21           last — the query budget.
 *
 * The unit tests feed `stdClass` on purpose: that is what Eloquent returns
 * from the `json` cast, and an array fixture would pass while production
 * blanks the column.
 */
class GodModeAgencySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TenantScope::testingAsWebRequest();
    }

    protected function tearDown(): void
    {
        TenantScope::testingAsWebRequest(false);

        parent::tearDown();
    }

    // ── fixtures ─────────────────────────────────────────────────────

    private function god(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => null,
            'username'  => 'landlord'.substr(md5(uniqid('', true)), 0, 8),
            'email'     => 'landlord'.substr(md5(uniqid('', true)), 0, 8).'@godmode.local',
            'user_type' => 'super_admin',
            'status'    => 'active',
            // The god session gate reads created_at for its absolute cap; a
            // null one makes every god request 403. Real operators have one.
            'created_at' => now()->subYear(),
        ], $attrs));
    }

    private function actAsGod(?User $user = null): self
    {
        $this->actingAs($user ?? $this->god(), 'godmode');

        return $this->withSession([
            'godmode.started_at'       => now(),
            'godmode.last_activity_at' => now(),
        ]);
    }

    /** An agency with an explicit settings value; NULL is passed as $settings = null. */
    private function agency(string $subdomain = 'gulf', ?array $settings = null, array $attrs = []): Agency
    {
        return Agency::factory()->create(array_merge([
            'name'       => ucfirst(str_replace('-', ' ', $subdomain)).' Agency',
            'subdomain'  => $subdomain,
            'status'       => 'active',
            'settings'     => $settings,
        ], $attrs));
    }

    /**
     * The payload a real form submit sends: every owned field as the rendered
     * form would post it, so a no-op submit is genuinely a no-op. `$overrides`
     * is applied ON TOP, and a null value explicitly UNSETS that key (the
     * unchecked-box / empty-colour case) rather than letting array_merge keep
     * the rendered value.
     */
    private function payload(Agency $agency, array $overrides = []): array
    {
        $state = app(GodAgencySettingsService::class)->formState($agency);

        $base = [
            'settings_version'     => $state['settings_version'],
            'qms_enabled'          => $state['qms_enabled'],
            'hide_type_tab_counts' => $state['hide_type_tab_counts'] ? '1' : null,
            'enable_firstimer'     => $state['enable_firstimer'] ? '1' : null,
            'complaint_sla'        => [
                'ack_days'     => $state['ack_days'],
                'resolve_days' => $state['resolve_days'],
            ],
            'color_theme'          => $state['color_theme'],
            'all_caps'             => $state['all_caps'] ? '1' : null,
            'banner_color'         => filled($state['banner_color']) ? $state['banner_color'] : null,
            'show_company_profile' => $state['show_company_profile'] ? '1' : null,
        ];

        foreach ($overrides as $key => $value) {
            if ($value === null) {
                unset($base[$key]);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    private function stored(Agency $agency): array
    {
        return app(GodAgencySettingsService::class)->document($agency->fresh());
    }

    private function auditCount(Agency $agency): int
    {
        return AuditEvent::withoutGlobalScopes()
            ->where('agency_id', $agency->id)
            ->where('action', GodAgencySettingsService::AUDIT_ACTION_UPDATED)
            ->count();
    }

    // ── 6: no-op writes nothing ──────────────────────────────────────

    #[Test]
    public function saving_with_no_changes_writes_no_audit_no_activity_and_leaves_the_json_identical(): void
    {
        $settings = [
            'qms_enabled'   => false,
            'banner_color'  => '#220dbf',
            'color_theme'   => 'default',
            'applicants'    => ['hide_type_tab_counts' => true],
        ];
        $agency = $this->agency('gulf', $settings);
        $beforeRaw = $agency->fresh()->getRawOriginal('settings');

        $activityBefore = ActivityLog::where('subject_id', $agency->id)->count();

        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency))
            ->assertRedirect(route('godmode.agencies.settings', $agency))
            ->assertSessionHas('status', 'No changes to save.');

        $this->assertSame(0, $this->auditCount($agency), 'a no-op submit must not write an audit row');
        $this->assertSame($activityBefore, ActivityLog::where('subject_id', $agency->id)->count());
        $this->assertSame($beforeRaw, $agency->fresh()->getRawOriginal('settings'), 'the column must be byte-identical');

        // The only bypass row a no-op could write is the settings write itself.
        $this->assertSame(0, AuditEvent::withoutGlobalScopes()
            ->where('agency_id', $agency->id)
            ->where('action', AuditEvent::ACTION_TENANT_BYPASS)
            ->count());
    }

    #[Test]
    public function a_null_settings_agency_renders_defaults_and_a_no_op_keeps_it_null(): void
    {
        $agency = $this->agency('freshco', null);
        $this->assertNull($agency->fresh()->getRawOriginal('settings'));

        $this->actAsGod()
            ->get(route('godmode.agencies.settings', $agency))
            ->assertOk()
            ->assertSee('Freshco Agency');

        // Defaults: QMS default, tab counters off, SLA 2 / 15, profile shown.
        $state = app(GodAgencySettingsService::class)->formState($agency);
        $this->assertSame('default', $state['qms_enabled']);
        $this->assertSame(2, $state['ack_days']);
        $this->assertSame(15, $state['resolve_days']);

        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency))
            ->assertSessionHas('status', 'No changes to save.');

        $this->assertNull($agency->fresh()->getRawOriginal('settings'));
        $this->assertSame(0, $this->auditCount($agency));
    }

    // ── 7: merge preserves unknown keys, audit holds only the change ─

    #[Test]
    public function ticking_tab_counters_writes_only_that_key_and_preserves_every_other_key(): void
    {
        $agency = $this->agency('finas', [
            'banner_color' => '#220dbf',
            'applicant_qr_token' => '2CxYyABCDEFGHIJKLMNOPQRSTUVWXYZnV',
            'applicants' => ['hide_type_tab_counts' => false],
            'status_codes' => [0],
        ]);

        $payload = $this->payload($agency, ['hide_type_tab_counts' => '1']);

        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $payload)
            ->assertRedirect(route('godmode.agencies.settings', $agency));

        $stored = $this->stored($agency);

        $this->assertTrue($stored['applicants']['hide_type_tab_counts']);
        $this->assertSame('#220dbf', $stored['banner_color']);
        $this->assertSame('2CxYyABCDEFGHIJKLMNOPQRSTUVWXYZnV', $stored['applicant_qr_token']);
        $this->assertSame([0], $stored['status_codes'], 'a [0] list must survive as-is');
        // finas had no qms_enabled key and the form said Default: still none.
        $this->assertArrayNotHasKey('qms_enabled', $stored);

        $this->assertSame(1, $this->auditCount($agency));

        $audit = AuditEvent::withoutGlobalScopes()
            ->where('agency_id', $agency->id)
            ->where('action', GodAgencySettingsService::AUDIT_ACTION_UPDATED)
            ->first();

        $this->assertSame(
            ['applicants.hide_type_tab_counts' => true],
            $audit->after,
            'the audit row must list only the changed key'
        );
        $this->assertSame(['applicants.hide_type_tab_counts' => false], $audit->before);
    }

    // ── 8: QMS tri-state. Default DELETES, Off stores false ──────────

    #[Test]
    public function qms_off_stores_false_and_default_removes_the_key(): void
    {
        $gulf = $this->agency('gulf', ['banner_color' => '#220dbf']);

        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $gulf), $this->payload($gulf, ['qms_enabled' => 'off']))
            ->assertSessionHasNoErrors();

        $stored = $this->stored($gulf);
        $this->assertArrayHasKey('qms_enabled', $stored);
        $this->assertFalse($stored['qms_enabled']);
        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->id), 'QMS must now be off for gulf');
        $this->assertSame('#220dbf', $stored['banner_color']);

        $gulf->refresh();

        // Back to Default: the key must be GONE, and gulf stays off (default is finas only).
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $gulf), $this->payload($gulf, ['qms_enabled' => 'default']))
            ->assertSessionHasNoErrors();

        $stored = $this->stored($gulf);
        $this->assertArrayNotHasKey('qms_enabled', $stored);
        $this->assertFalse(ModuleAccess::qmsEnabled($gulf->id));
        $this->assertSame('#220dbf', $stored['banner_color'], 'the other key must survive the delete');

        $this->assertSame(2, $this->auditCount($gulf), 'two real changes, two audit rows');
    }

    #[Test]
    public function finas_with_no_stored_key_reports_default_and_an_explicit_on_is_recorded(): void
    {
        $finas = $this->agency('finas', null);

        $this->assertArrayNotHasKey('qms_enabled', $this->stored($finas));
        $this->assertTrue(ModuleAccess::qmsEnabled($finas->id), 'finas is on by the code list');

        $state = app(GodAgencySettingsService::class)->formState($finas);
        $this->assertSame('default', $state['qms_enabled'], 'no stored key must render as Default');

        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $finas), $this->payload($finas, ['qms_enabled' => 'on']))
            ->assertRedirect();

        $stored = $this->stored($finas);
        $this->assertTrue($stored['qms_enabled'], 'an explicit On must be stored even though it resolved on');
        $this->assertSame(1, $this->auditCount($finas), 'an explicit value is a decision worth recording');
    }

    // ── 14: keys the form does not own survive a save ────────────────

    #[Test]
    public function read_only_and_unknown_keys_are_preserved_and_shown_read_only(): void
    {
        $settings = [
            'primary_color'      => '#123456',
            'applicant_qr_token' => '2CxYyABCDEFGHIJKLMNOPQRSTUVWXYZnV',
            'applicants_table_columns' => ['name', 'position', 'status'],
            'some_future_key'    => ['nested' => 'kept'],
        ];
        $agency = $this->agency('csdi', $settings);

        $html = $this->actAsGod()
            ->get(route('godmode.agencies.settings', $agency))
            ->assertOk()
            ->getContent();

        // Masked QR: first 6 … last 4.
        $this->assertStringContainsString($this->masked($settings['applicant_qr_token']), $html, 'QR token must be masked as first 6 … last 4');
        $this->assertStringNotContainsString('2CxYyABCDEFGHIJKLMNOPQRSTUVWXYZnV', $html, 'the full token must not be rendered');
        $this->assertStringContainsString('primary_color', $html);
        $this->assertStringContainsString('some_future_key', $html);

        // A real change to an unrelated owned key.
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency, ['all_caps' => '1']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $stored = $this->stored($agency);
        $this->assertSame('#123456', $stored['primary_color']);
        $this->assertSame('2CxYyABCDEFGHIJKLMNOPQRSTUVWXYZnV', $stored['applicant_qr_token']);
        $this->assertSame(['name', 'position', 'status'], $stored['applicants_table_columns']);
        $this->assertSame(['nested' => 'kept'], $stored['some_future_key']);
        $this->assertTrue($stored['all_caps']);
    }

    private function masked(string $token): string
    {
        return mb_substr($token, 0, 6).'…'.mb_substr($token, -4);
    }

    // ── 15: stale version refuses, and changes nothing ───────────────

    #[Test]
    public function a_stale_settings_version_is_refused_and_applies_nothing(): void
    {
        $agency = $this->agency('gulf', ['color_theme' => 'default']);

        // Session B renders the form and keeps both the hash and the values.
        $stale = $this->payload($agency);
        $staleVersion = $stale['settings_version'];

        // Session A saves first.
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency, ['all_caps' => '1']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $agency->refresh();

        $auditsAfterA = $this->auditCount($agency);

        // Session B still holds the hash from before A's save.
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), array_merge($stale, [
                'settings_version' => $staleVersion,
                'banner_color'     => '#00ff00',
            ]))
            ->assertSessionHasErrors('settings_version');

        $stored = $this->stored($agency);
        $this->assertTrue($stored['all_caps'], "A's change stays");
        $this->assertArrayNotHasKey('banner_color', $stored, "B's change must not be applied");
        $this->assertSame($auditsAfterA, $this->auditCount($agency), 'a refused save writes no audit');

        $agency->refresh();

        // Reload + re-apply succeeds.
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency, ['banner_color' => '#00ff00']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('#00ff00', $this->stored($agency)['banner_color']);
    }

    #[Test]
    public function the_service_throws_version_mismatch_inside_the_transaction_and_writes_nothing(): void
    {
        $agency = $this->agency('gulf', ['color_theme' => 'default']);
        $service = app(GodAgencySettingsService::class);
        $beforeRaw = $agency->fresh()->getRawOriginal('settings');

        $this->expectException(VersionMismatchException::class);

        try {
            $service->save(
                $agency,
                ['all_caps' => true],
                'deadbeefdeadbeefdeadbeefdeadbeef',
                $this->god(),
                app(\App\Services\GodMode\GodAgencyService::class),
            );
        } finally {
            $this->assertSame($beforeRaw, $agency->fresh()->getRawOriginal('settings'));
            $this->assertSame(0, $this->auditCount($agency));
        }
    }

    // ── 12 / 13: validation, loud ────────────────────────────────────

    #[Test]
    public function out_of_bounds_sla_and_bad_colours_are_rejected_and_nothing_is_written(): void
    {
        $agency = $this->agency('gulf', ['color_theme' => 'default']);
        $beforeRaw = $agency->fresh()->getRawOriginal('settings');

        $cases = [
            'zero ack'      => ['complaint_sla' => ['ack_days' => 0, 'resolve_days' => 15]],
            'ack 31'        => ['complaint_sla' => ['ack_days' => 31, 'resolve_days' => 15]],
            'resolve 91'    => ['complaint_sla' => ['ack_days' => 2, 'resolve_days' => 91]],
            'resolve < ack' => ['complaint_sla' => ['ack_days' => 10, 'resolve_days' => 5]],
            'short hex'     => ['banner_color' => '#220db'],
            'no hash'       => ['banner_color' => '220dbf'],
            'unknown theme' => ['color_theme' => 'not-a-theme'],
            'bad qms'       => ['qms_enabled' => 'maybe'],
        ];

        foreach ($cases as $label => $override) {
            $this->actAsGod()
                ->post(route('godmode.agencies.settings.update', $agency), array_merge($this->payload($agency), $override))
                ->assertSessionHasErrors([], null, $label);

            $this->assertSame($beforeRaw, $agency->fresh()->getRawOriginal('settings'), "{$label} must not write");
        }

        $this->assertSame(0, $this->auditCount($agency));

        // The valid edge saves.
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency, [
                'complaint_sla' => ['ack_days' => 30, 'resolve_days' => 30],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(30, $this->stored($agency)['complaint_sla']['ack_days']);
    }

    #[Test]
    public function resolve_days_below_a_stored_ack_days_is_rejected_when_only_resolve_is_moved(): void
    {
        $agency = $this->agency('gulf', ['complaint_sla' => ['ack_days' => 20, 'resolve_days' => 40]]);

        // The form as rendered shows ack 20; post resolve 5 — still a violation,
        // and a payload that omitted ack must not slip past it.
        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency, [
                'complaint_sla' => ['ack_days' => 20, 'resolve_days' => 5],
            ]))
            ->assertSessionHasErrors('complaint_sla.resolve_days');

        $this->assertSame(40, $this->stored($agency)['complaint_sla']['resolve_days']);
    }

    // ── 16: the gate is godmode.only, fail closed ───────────────────

    #[Test]
    public function a_super_admin_with_pending_status_gets_403_on_both_routes(): void
    {
        $agency = $this->agency('gulf');
        $pending = $this->god(['status' => 'pending']);

        // Each request gets a fresh god session: the guard's 403 calls
        // forgetSession(), which is exactly what it should do — a suspended
        // operator's other tabs die on their next request.
        $this->actAsGod($pending);
        $this->get(route('godmode.agencies.settings', $agency))->assertForbidden();

        $this->actAsGod($pending);
        $this->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency))->assertForbidden();

        $this->assertSame(0, $this->auditCount($agency));
    }

    #[Test]
    public function an_agency_admin_is_not_a_god_operator_and_gets_403(): void
    {
        $agency = $this->agency('gulf');
        $admin = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        $this->actingAs($admin, 'godmode')
            ->withSession(['godmode.started_at' => now(), 'godmode.last_activity_at' => now()]);

        $this->get(route('godmode.agencies.settings', $agency))->assertForbidden();
    }

    #[Test]
    public function a_guest_is_redirected_to_the_god_login(): void
    {
        $agency = $this->agency('gulf');

        $this->get(route('godmode.agencies.settings', $agency))->assertRedirect(route('godmode.login'));
    }

    // ── 17: unknown agency is 404, and the tenant has no such route ──

    #[Test]
    public function an_unknown_agency_id_is_404_not_500(): void
    {
        $this->actAsGod();

        $this->get('/godmode/agencies/999999/settings')->assertNotFound();
        $this->post('/godmode/agencies/999999/settings', [])->assertNotFound();
    }

    #[Test]
    public function the_settings_page_links_from_the_agency_page_and_the_nav_is_unchanged(): void
    {
        $agency = $this->agency('gulf');

        $this->actAsGod()
            ->get(route('godmode.agencies.show', $agency))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee(route('godmode.agencies.settings', $agency), false);
    }

    #[Test]
    public function a_tenant_host_has_no_agency_settings_route(): void
    {
        $agency = $this->agency('gulf');
        $admin = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        $this->actingAs($admin, 'web');

        // Absolute URL is the only form that actually rehosts the request.
        $response = $this->call('GET', 'http://gulf.'.trim(config('app.tenant_domain', 'landas.fixitautoservices.com'), '.').'/settings/agency');

        $this->assertSame(404, $response->status(), '/settings/agency must not exist on a tenant host');
    }

    // ── 18: one tenant_bypass row naming the write ──────────────────

    #[Test]
    public function a_settings_write_records_one_tenant_bypass_row_naming_the_action(): void
    {
        $agency = $this->agency('gulf', ['color_theme' => 'default']);

        $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency, ['all_caps' => '1']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $bypass = AuditEvent::withoutGlobalScopes()
            // A god operator has no agency, so the bypass row carries a NULL
            // agency_id; the agency it names lives in the label and the
            // context. Match on the reason.
            ->where('action', AuditEvent::ACTION_TENANT_BYPASS)
            ->where('subject_label', 'like', 'godmode: update per-agency settings%')
            ->get();

        $this->assertCount(1, $bypass, 'exactly one bypass row for one write');
        $this->assertSame(
            'godmode: update per-agency settings',
            $bypass->first()->after['reason'] ?? null
        );
        $this->assertSame($agency->id, (int) ($bypass->first()->after['agency_id'] ?? 0));
    }

    // ── 19: no-store on both routes ─────────────────────────────────

    #[Test]
    public function both_routes_send_no_store(): void
    {
        $agency = $this->agency('gulf');

        $response = $this->actAsGod()->get(route('godmode.agencies.settings', $agency))->assertOk();
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $response = $this->actAsGod()
            ->post(route('godmode.agencies.settings.update', $agency), $this->payload($agency));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    // ── 21: query budget ────────────────────────────────────────────

    #[Test]
    public function the_form_loads_within_a_small_query_budget_and_not_per_key(): void
    {
        $agency = $this->agency('gulf', [
            'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6,
        ]);

        $queries = 0;
        \DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->actAsGod()
            ->get(route('godmode.agencies.settings', $agency))
            ->assertOk();

        $this->assertLessThanOrEqual(8, $queries, "the form made {$queries} queries");

        // Adding keys must not add queries: the budget is structure, not size.
        $agency->update(['settings' => array_merge($agency->settings, ['x1' => 1, 'x2' => 2, 'x3' => 3, 'x4' => 4, 'x5' => 5])]);

        $queries = 0;
        $this->actAsGod()->get(route('godmode.agencies.settings', $agency->fresh()))->assertOk();

        $this->assertLessThanOrEqual(8, $queries, "the form made {$queries} queries with more keys");
    }

    // ── unit: stdClass in, array out ────────────────────────────────

    #[Test]
    public function the_service_normalises_stdclass_and_null_without_losing_keys(): void
    {
        $service = app(GodAgencySettingsService::class);

        $this->assertSame([], $service->normalise(null));
        $this->assertSame([], $service->normalise([]));

        $raw = (object) [
            'qms_enabled' => false,
            'applicants'  => (object) ['hide_type_tab_counts' => true],
            'status_codes' => [0],
        ];

        $doc = $service->normalise($raw);

        $this->assertSame(false, $doc['qms_enabled']);
        $this->assertTrue($doc['applicants']['hide_type_tab_counts']);
        $this->assertSame([0], $doc['status_codes']);
    }

    #[Test]
    public function merge_preserves_unknown_keys_and_only_default_deletes_qms(): void
    {
        $service = app(GodAgencySettingsService::class);

        $stored = [
            'qms_enabled' => true,
            'banner_color' => '#220dbf',
            'unknown' => ['deep' => 1],
        ];

        $on = $service->merge($stored, ['qms_enabled' => 'default']);
        $this->assertArrayNotHasKey('qms_enabled', $on);
        $this->assertSame('#220dbf', $on['banner_color']);
        $this->assertSame(['deep' => 1], $on['unknown']);

        $off = $service->merge($stored, ['qms_enabled' => 'off']);
        $this->assertFalse($off['qms_enabled']);

        // An empty banner colour unsets, it does not store ''.
        $noBanner = $service->merge($stored, ['banner_color' => '']);
        $this->assertArrayNotHasKey('banner_color', $noBanner);
        $this->assertArrayHasKey('unknown', $noBanner);
    }

    #[Test]
    public function a_list_value_is_not_flattened_into_paths(): void
    {
        $service = app(GodAgencySettingsService::class);

        $flat = $service->flatten([
            'status_codes' => [0],
            'applicants'   => ['hide_type_tab_counts' => true],
        ]);

        $this->assertSame([0], $flat['status_codes'], 'a list stays one leaf');
        $this->assertTrue($flat['applicants.hide_type_tab_counts']);
    }

    #[Test]
    public function diff_reports_deleted_and_false_versus_absent(): void
    {
        $service = app(GodAgencySettingsService::class);

        $this->assertSame(
            ['qms_enabled' => true],
            $service->diff(['qms_enabled' => false], [])
        );

        $this->assertSame(
            ['qms_enabled' => true],
            $service->diff([], ['qms_enabled' => false]),
            'appearing false is a change'
        );

        $this->assertSame([], $service->diff(['a' => 1], ['a' => 1]));
        $this->assertArrayNotHasKey(0, $service->diff(['a' => 1], ['a' => '1']), 'strict: 1 is not "1"');
    }

    #[Test]
    public function the_hash_is_stable_across_key_order(): void
    {
        $service = app(GodAgencySettingsService::class);

        $this->assertSame(
            $service->hash(['a' => 1, 'b' => ['c' => 2, 'd' => 3]]),
            $service->hash(['b' => ['d' => 3, 'c' => 2], 'a' => 1])
        );
        $this->assertNotSame($service->hash(['a' => 1]), $service->hash(['a' => 2]));
    }
}
