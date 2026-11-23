<?php

namespace Tests\Feature\GodMode;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Services\GodMode\GodAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0383d516e52b9c418a9136 — the god-plane audit trail.
 *
 * What these tests are actually for:
 *
 *   - The screen is reachable by a god session (the original bug: 302 to
 *     login from a valid god session), and by NOTHING else. Every refusal
 *     asserts the literal status code.
 *   - `agency_id NULL` rows are visible and labelled `No agency` — that is
 *     where the trail of a hard-deleted agency lives, and it is the whole
 *     reason the screen exists.
 *   - The screen and its CSV export are ONE computation: "matching" on screen
 *     equals CSV data rows for the same filters.
 *   - Viewing writes nothing: no `tenant_bypass` row per page view.
 *
 * Every request runs with `TenantScope::testingAsWebRequest()` so a scoped
 * regression fails here instead of passing on the console fail-open branch
 * (the same trap GodModePaymentsTest documents).
 */
class GodModeAuditTest extends TestCase
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
            'username'  => 'landlord',
            'email'     => 'landlord@godmode.local',
            'user_type' => 'super_admin',
            'status'    => 'active',
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

    private function agency(string $name, array $attrs = []): Agency
    {
        return Agency::factory()->create(array_merge([
            'name'      => $name,
            'subdomain' => strtolower(str_replace(' ', '-', $name)),
            'status'    => 'active',
        ], $attrs));
    }

    private function event(array $attrs = []): AuditEvent
    {
        return AuditEvent::withoutGlobalScopes()->create(array_merge([
            'agency_id'     => null,
            'actor_user_id' => null,
            'action'        => AuditEvent::ACTION_UPDATED,
            'subject_type'  => 'App\Models\Applicant',
            'subject_id'    => 1,
            'subject_label' => 'REF-0001',
            'after'         => ['status' => 'active'],
            'created_at'    => now(),
        ], $attrs));
    }

    private function log(array $attrs = []): ActivityLog
    {
        return ActivityLog::create(array_merge([
            'agency_id'   => null,
            'user_id'     => null,
            'action'      => 'login_failed',
            'description' => 'Failed login for nobody@example.com',
        ], $attrs));
    }

    /** `data-row-count` of the CSV returned by $url (header line excluded). */
    private function csvRows(string $url): int
    {
        $response = $this->get($url)->assertOk();
        $body = $response->streamedContent();
        $lines = preg_split("/\r\n|\n|\r/", trim($body));

        return max(0, count($lines) - 1);
    }

    // ── 1: reachable by a god session, nav has Audit ────────────────

    #[Test]
    public function a_god_session_reaches_the_audit_screen_and_the_nav_names_it(): void
    {
        $this->event();

        $this->actAsGod();

        $page = $this->get(route('godmode.audit.index'))->assertOk();
        $page->assertSee('Audit trail');
        $page->assertSeeInOrder(['Dashboard', 'Agencies', 'Payments', 'Audit']);
        $page->assertSee(route('godmode.audit.index'), false);

        $this->get(route('godmode.audit.sensitive'))->assertOk();
    }

    // ── 2: NULL-agency rows are visible, labelled and countable ─────

    #[Test]
    public function rows_with_no_agency_are_visible_labelled_and_the_badge_matches_the_filter(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->event(['agency_id' => $gulf->id, 'subject_label' => 'WITH-AGENCY']);
        $this->event(['subject_label' => 'NO-AGENCY-1']);
        $this->event(['subject_label' => 'NO-AGENCY-2']);

        $this->actAsGod();

        $page = $this->get(route('godmode.audit.index'))->assertOk();
        $page->assertSee('NO-AGENCY-1');
        $page->assertSee('NO-AGENCY-2');
        $page->assertSee('WITH-AGENCY');
        $page->assertSee('2 with no agency');

        $none = $this->get(route('godmode.audit.index', ['agency' => 'none']))->assertOk();
        $none->assertSee('NO-AGENCY-1');
        $none->assertSee('NO-AGENCY-2');
        $none->assertDontSee('WITH-AGENCY');
        $none->assertSee('2 matching');

        // Never a blank agency cell: the label is rendered literally.
        $none->assertSee('No agency');
    }

    // ── 3: agency filter narrows to one agency ─────────────────────

    #[Test]
    public function the_agency_filter_lists_only_that_agencys_rows(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $finas = $this->agency('Finas Training');

        $this->event(['agency_id' => $gulf->id, 'subject_label' => 'GULF-ROW']);
        $this->event(['agency_id' => $finas->id, 'subject_label' => 'FINAS-ROW']);

        $this->actAsGod();

        $page = $this->get(route('godmode.audit.index'))->assertOk();
        $page->assertSee('Gulf Horizon');
        $page->assertSee('Finas Training');

        $filtered = $this->get(route('godmode.audit.index', ['agency' => $gulf->id]))->assertOk();
        $filtered->assertSee('GULF-ROW');
        $filtered->assertDontSee('FINAS-ROW');
    }

    // ── 4: screen count, paged rows and CSV agree ───────────────────

    #[Test]
    public function the_matching_count_equals_paged_rows_equals_csv_rows(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        $ids = [];
        for ($i = 0; $i < 60; $i++) {
            $ids[] = $this->event([
                'agency_id'     => $i % 2 === 0 ? $gulf->id : null,
                'subject_label' => 'REF-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            ])->id;
        }

        $this->actAsGod();

        foreach ([['agency' => 'none'], ['agency' => $gulf->id], ['per' => 25, 'agency' => $gulf->id]] as $query) {
            $page = $this->get(route('godmode.audit.index', $query))->assertOk();
            preg_match('/data-audit-matching="(\d+)"/', $page->getContent(), $m);
            $this->assertSame(30, (int) $m[1], 'matching badge for '.json_encode($query));

            $dataRows = substr_count($page->getContent(), 'data-audit-row="');
            $this->assertSame(25, $dataRows, 'page 1 carries 25 rows for '.json_encode($query));

            $this->assertSame(30, $this->csvRows(route('godmode.audit.export', $query)), 'csv rows for '.json_encode($query));
        }

        // Page-through: page 2 carries the remaining 5 of the 30.
        $page2 = $this->get(route('godmode.audit.index', ['agency' => 'none', 'per' => 25, 'page' => 2]))->assertOk();
        $this->assertSame(5, substr_count($page2->getContent(), 'data-audit-row="'));
    }

    // ── 5: per page 25/50/100, coerced, filters kept ───────────────

    #[Test]
    public function per_page_accepts_only_25_50_100_and_keeps_the_filters(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        foreach (range(1, 55) as $i) {
            $this->event(['agency_id' => $gulf->id, 'subject_label' => 'KEEP-'.$i]);
        }

        $this->actAsGod();

        $this->assertSame(25, substr_count($this->get(route('godmode.audit.index', ['per' => 25]))->getContent(), 'data-audit-row="'));
        $this->assertSame(50, substr_count($this->get(route('godmode.audit.index', ['per' => 50]))->getContent(), 'data-audit-row="'));
        $this->assertSame(55, substr_count($this->get(route('godmode.audit.index', ['per' => 100]))->getContent(), 'data-audit-row="'));

        // 37 is coerced to 25, not a 422 and not 37 rows.
        $coerced = $this->get(route('godmode.audit.index', ['per' => 37]))->assertOk();
        $this->assertSame(25, substr_count($coerced->getContent(), 'data-audit-row="'));

        // The per selector keeps the agency filter: every row is Gulf's, and
        // the paginator links carry both.
        $kept = $this->get(route('godmode.audit.index', ['per' => 50, 'agency' => $gulf->id]))->assertOk();
        $this->assertSame(50, substr_count($kept->getContent(), 'data-audit-row="'));
        $kept->assertSee('agency='.$gulf->id.'&amp;per=50', false);
    }

    // ── 6: actor System vs a named actor ───────────────────────────

    #[Test]
    public function the_actor_filter_separates_system_rows_from_a_named_actor(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $actor = User::factory()->create([
            'agency_id' => $gulf->id,
            'user_type' => 'admin',
            'name'      => 'Ana',
            'surname'   => 'Cruz',
        ]);

        $this->event(['actor_user_id' => null, 'subject_label' => 'SYSTEM-ROW']);
        $this->event(['actor_user_id' => $actor->id, 'subject_label' => 'ACTOR-ROW']);

        $this->actAsGod();

        $all = $this->get(route('godmode.audit.index'))->assertOk();
        $all->assertSee('System');

        $system = $this->get(route('godmode.audit.index', ['actor' => 'none']))->assertOk();
        $system->assertSee('SYSTEM-ROW');
        $system->assertDontSee('ACTOR-ROW');
        $system->assertSee('System (no actor)');

        $named = $this->get(route('godmode.audit.index', ['actor' => $actor->id]))->assertOk();
        $named->assertSee('ACTOR-ROW');
        $named->assertDontSee('SYSTEM-ROW');
        // The actor cell is rendered; the exact name comes from the relation.
        $named->assertSee('data-actor="'.$actor->id.'"', false);
    }

    // ── 7: action and record-type filters, empty state ─────────────

    #[Test]
    public function the_action_filter_narrows_and_an_unknown_record_type_renders_the_empty_state(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->event(['agency_id' => $gulf->id, 'action' => 'deleted', 'subject_label' => 'DELETED-ROW']);
        $this->event(['agency_id' => $gulf->id, 'action' => 'updated', 'subject_label' => 'UPDATED-ROW']);

        $this->actAsGod();

        $deleted = $this->get(route('godmode.audit.index', ['action' => 'deleted']))->assertOk();
        $deleted->assertSee('DELETED-ROW');
        $deleted->assertDontSee('UPDATED-ROW');

        $agency = $this->get(route('godmode.audit.index', ['subject_type' => Agency::class]))->assertOk();
        $agency->assertSee('No events match these filters.');
        $agency->assertDontSee('DELETED-ROW');
    }

    // ── 8: subject_label search, LIKE escaped ──────────────────────

    #[Test]
    public function search_matches_the_reference_and_a_percent_returns_zero_rows(): void
    {
        $this->event(['subject_label' => 'INV-12345']);
        $this->event(['subject_label' => 'APPL-999']);

        $this->actAsGod();

        $hit = $this->get(route('godmode.audit.index', ['q' => 'INV-123']))->assertOk();
        $hit->assertSee('INV-12345');
        $hit->assertDontSee('APPL-999');

        foreach (['%', '_', '\\'] as $wildcard) {
            $none = $this->get(route('godmode.audit.index', ['q' => $wildcard]))->assertOk();
            $none->assertSee('No events match these filters.', "q={$wildcard} must match nothing");
            $none->assertSee('0 matching');
            $this->assertSame(0, $this->csvRows(route('godmode.audit.export', ['q' => $wildcard])));
        }
    }

    // ── 9: the agency-deleted case, run through the real command ───

    #[Test]
    public function a_hard_deleted_agency_leaves_its_trail_readable_under_no_agency(): void
    {
        $marked = Agency::factory()->create([
            'name'      => 'Doomed Agency',
            'subdomain' => 'doomed-agency',
            'status'    => 'active',
        ]);

        // Some audit rows that the delete must keep, re-homed to NULL.
        $keep = $this->event([
            'agency_id'     => $marked->id,
            'action'        => 'created',
            'subject_type'  => Agency::class,
            'subject_id'    => $marked->id,
            'subject_label' => 'Doomed Agency',
        ]);

        // Criterion 9 says: run the command, do not fake its output. The test
        // database is the disposable environment (`:memory:`); the live
        // database is never touched by this test.
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', (string) config('database.connections.sqlite.database'));

        $exit = Artisan::call('agencies:remove-empty', [
            'agency' => $marked->id,
            '--force' => 'Doomed Agency',
        ]);

        $this->assertSame(0, $exit, Artisan::output());
        $this->assertNull(Agency::withoutGlobalScopes()->find($marked->id), 'the agency row is gone');

        // The kept row survived with no agency at all.
        $rehomed = AuditEvent::withoutGlobalScopes()->find($keep->id);
        $this->assertNotNull($rehomed);
        $this->assertNull($rehomed->agency_id);

        $this->actAsGod();

        $page = $this->get(route('godmode.audit.index', ['agency' => 'none', 'action' => 'deleted']))->assertOk();
        $page->assertSee('Deleted');
        $page->assertSee('Doomed Agency');
        $page->assertSee('No agency');

        // The detail panel explains the row in words.
        $page->assertSee(GodAuditService::DELETED_AGENCY_NOTE);

        // The delete summary is in `after`.
        $page->assertSee('audit_rows_kept');

        // The re-homed row is visible under No agency and gone from any agency
        // filter — there is no agency id left to filter by.
        $noAgency = $this->get(route('godmode.audit.index', ['agency' => 'none']))->assertOk();
        $noAgency->assertSee('Doomed Agency');

        $byGulf = $this->get(route('godmode.audit.index', ['agency' => 999999]))->assertOk();
        $byGulf->assertDontSee('Doomed Agency');
        $byGulf->assertSee('0 matching');
    }

    // ── 10: sensitive tab and its export ──────────────────────────

    #[Test]
    public function the_sensitive_tab_renders_its_own_columns_and_its_no_agency_filter_and_export_agree(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        foreach (range(1, 3) as $i) {
            $this->log(['agency_id' => $gulf->id, 'description' => 'TENANT-LOG-'.$i]);
        }

        foreach (range(1, 4) as $i) {
            $this->log(['description' => 'GOD-PLANE-LOG-'.$i]);
        }

        $this->actAsGod();

        $page = $this->get(route('godmode.audit.sensitive'))->assertOk();
        $page->assertSee('Sensitive actions');
        $page->assertSee('Description');
        $page->assertSee('GOD-PLANE-LOG-1');
        $page->assertSee('7 matching');

        $none = $this->get(route('godmode.audit.sensitive', ['agency' => 'none']))->assertOk();
        $none->assertSee('GOD-PLANE-LOG-1');
        $none->assertDontSee('TENANT-LOG-1');
        $none->assertSee('4 matching');

        $this->assertSame(
            4,
            $this->csvRows(route('godmode.audit.export', ['tab' => 'sensitive', 'agency' => 'none']))
        );
    }

    // ── 11: fails closed, zero bytes ──────────────────────────────

    #[Test]
    public function an_agency_admin_on_the_web_guard_gets_a_redirect_on_all_three_routes(): void
    {
        $agency = $this->agency('Gulf Horizon');
        $this->event(['agency_id' => $agency->id, 'subject_label' => 'SECRET-TRAIL']);

        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
            'password'  => 'password',
            'email'     => 'admin@gulf.test',
        ]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
        $this->assertTrue(auth('web')->check(), 'sanity: the web login succeeded');

        foreach ([route('godmode.audit.index'), route('godmode.audit.sensitive'), route('godmode.audit.export')] as $url) {
            $response = $this->get($url);
            $response->assertRedirect('/godmode/login');
            $this->assertStringNotContainsString('SECRET-TRAIL', (string) $response->getContent(), $url);
        }
    }

    #[Test]
    public function a_suspended_god_account_is_403_with_no_rows(): void
    {
        $agency = $this->agency('Gulf Horizon');
        $this->event(['agency_id' => $agency->id, 'subject_label' => 'SECRET-TRAIL']);

        $this->actAsGod($this->god(['status' => 'pending']));

        $response = $this->get(route('godmode.audit.index'))->assertForbidden();
        $this->assertStringNotContainsString('SECRET-TRAIL', (string) $response->getContent());

        $this->assertFalse(auth('godmode')->check(), 'the suspended operator is signed out, not merely blocked');

        foreach ([route('godmode.audit.sensitive'), route('godmode.audit.export')] as $url) {
            $this->get($url)->assertRedirect('/godmode/login');
        }
    }

    // ── 12: tenant host is 404 ────────────────────────────────────

    #[Test]
    public function the_audit_screen_is_not_served_on_a_tenant_host(): void
    {
        $this->agency('Gulf Horizon');

        foreach (['/godmode/audit', '/godmode/audit/sensitive', '/godmode/audit/export'] as $path) {
            $this->assertSame(404, $this->call('GET', 'http://gulf-horizon.fixitautoservices.com'.$path)->status(), $path);
        }
    }

    // ── 13: read-only, exactly three GET routes ───────────────────

    #[Test]
    public function the_screen_is_read_only_and_has_exactly_three_get_routes(): void
    {
        $this->event();

        $this->actAsGod();

        foreach ([route('godmode.audit.index'), route('godmode.audit.sensitive')] as $url) {
            $html = $this->get($url)->getContent();

            $this->assertStringNotContainsString('@csrf', $html);
            $this->assertStringNotContainsString('_method', $html);

            // Every form is the GET filter form; the only POST form in the
            // chrome is the god-mode sign-out. Nothing on the page writes.
            preg_match_all('/<form[^>]*method="([^"]*)"/i', $html, $methods);
            foreach ($methods[1] as $method) {
                $this->assertContains(strtoupper($method), ['GET', 'POST'], "{$url}: unexpected form method {$method}");
            }
            $this->assertSame(1, substr_count($html, 'method="GET"'), "{$url}: the filter form is the only GET form");
            $this->assertSame(1, substr_count($html, 'method="POST"'), "{$url}: the sign-out is the only POST form");

            // Rendered controls, not vocabulary: a filter option legitimately
            // names the `deleted` action, and the trail must never hide it.
            // What must be absent is a form action or a button that writes.
            foreach (['/edit', '/destroy', 'forceDelete', 'action="POST"', 'Mark reviewed', 'name="_method"'] as $needle) {
                $this->assertStringNotContainsString($needle, $html, "{$url} must not offer a {$needle} control");
            }
        }

        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), 'godmode/audit'))
            // HEAD is implicit on every GET route; the verb that matters is
            // that there is no POST/PUT/PATCH/DELETE.
            ->map(fn ($r) => implode(',', array_diff($r->methods(), ['HEAD'])).' '.$r->uri())
            ->sort()
            ->values()
            ->all();

        $this->assertSame([
            'GET godmode/audit',
            'GET godmode/audit/export',
            'GET godmode/audit/sensitive',
        ], $routes);

        foreach ($routes as $route) {
            $this->assertStringStartsWith('GET ', $route);
        }
    }

    // ── 14: no-store ─────────────────────────────────────────────

    #[Test]
    public function the_audit_screens_and_export_are_not_cacheable(): void
    {
        $this->event();

        $this->actAsGod();

        foreach ([route('godmode.audit.index'), route('godmode.audit.sensitive'), route('godmode.audit.export')] as $url) {
            $response = $this->get($url);
            $response->assertOk();
            $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'), $url);
        }
    }

    // ── 15: viewing writes nothing ────────────────────────────────

    #[Test]
    public function viewing_the_trail_writes_no_bypass_and_no_event(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        foreach (range(1, 30) as $i) {
            $this->event(['agency_id' => $gulf->id, 'subject_label' => 'ROW-'.$i]);
        }

        $this->actAsGod();

        $events = AuditEvent::withoutGlobalScopes()->count();
        $bypass = AuditEvent::withoutGlobalScopes()->where('action', AuditEvent::ACTION_TENANT_BYPASS)->count();
        $logs = DB::table('activity_logs')->count();

        // Three loads, a page-through and an export.
        $this->get(route('godmode.audit.index'))->assertOk();
        $this->get(route('godmode.audit.index', ['page' => 2]))->assertOk();
        $this->get(route('godmode.audit.index', ['agency' => 'none', 'per' => 50]))->assertOk();
        $this->get(route('godmode.audit.sensitive'))->assertOk();
        $this->get(route('godmode.audit.export', ['agency' => $gulf->id]))->assertOk();
        $this->get(route('godmode.audit.export', ['tab' => 'sensitive']))->assertOk();

        $this->assertSame($events, AuditEvent::withoutGlobalScopes()->count(), 'no audit event written by a page view');
        $this->assertSame($bypass, AuditEvent::withoutGlobalScopes()->where('action', AuditEvent::ACTION_TENANT_BYPASS)->count(), 'no tenant_bypass row per view');
        $this->assertSame($logs, DB::table('activity_logs')->count(), 'no activity row written by a view');
    }

    // ── service-level guards ─────────────────────────────────────

    #[Test]
    public function inverted_dates_are_swapped_and_an_empty_database_renders(): void
    {
        $this->event(['created_at' => now()->subDay()]);

        $this->actAsGod();

        $service = app(GodAuditService::class);
        $filters = $service->filters(['from' => '2026-11-20', 'to' => '2026-11-10']);

        $this->assertSame(['2026-11-10', '2026-11-20'], [$filters['from'], $filters['to']]);

        // No filters at all: a 30-day default window, not "everything".
        $default = $service->filters([]);
        $this->assertSame(now()->toDateString(), $default['to']);
        $this->assertSame(now()->subDays(GodAuditService::DEFAULT_DAYS - 1)->toDateString(), $default['from']);

        $this->get(route('godmode.audit.index', ['from' => '2026-11-20', 'to' => '2026-11-10']))->assertOk();
        $this->get(route('godmode.audit.index', ['from' => '2026-11-10', 'to' => '2026-11-20']))->assertOk();
        $this->get(route('godmode.audit.sensitive'))->assertOk();
    }

    #[Test]
    public function an_empty_audit_table_renders_the_empty_state_not_an_error(): void
    {
        $this->actAsGod();

        $page = $this->get(route('godmode.audit.index'))->assertOk();
        $page->assertSee('No events match these filters.');
        $page->assertSee('0 matching');

        $this->get(route('godmode.audit.sensitive'))->assertOk()->assertSee('No activity rows match these filters.');

        $this->assertSame(0, $this->csvRows(route('godmode.audit.export')));
    }

    #[Test]
    public function the_csv_export_has_the_expected_headers_and_values(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->event([
            'agency_id'     => $gulf->id,
            'action'        => 'updated',
            'subject_label' => 'REF-777',
            'before'        => ['status' => 'pending'],
            'after'         => ['status' => 'active'],
            'user_agent'    => 'Mozilla/5.0 test',
        ]);

        $this->actAsGod();

        $body = $this->get(route('godmode.audit.export'))->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $body, 'UTF-8 BOM for Excel');
        $this->assertStringContainsString('Timestamp,Actor,Action,"Record type","Record ID",Reference,Agency,"Changed fields"', $body);
        $this->assertStringContainsString('REF-777', $body);
        $this->assertStringContainsString('Gulf Horizon', $body);
        $this->assertStringContainsString('status', $body);
        $this->assertStringContainsString('Updated', $body);
    }
}
