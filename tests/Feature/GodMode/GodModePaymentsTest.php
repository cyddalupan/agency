<?php

namespace Tests\Feature\GodMode;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\AuditEvent;
use App\Models\Receivable;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Services\GodMode\GodReceivableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0342ac4cf7cfdc3e26d6a9 — the god-plane money ledger.
 *
 * What these tests are actually for:
 *
 *   - The ledger is UNSCOPED and READ-ONLY. Every request runs with
 *     `TenantScope::testingAsWebRequest()` so a scoped regression fails here
 *     instead of passing on the console fail-open branch (same trap the
 *     sibling GodModeAgenciesTest documents).
 *   - The numbers are ONE computation. `sum(perAgency) == summary()` is
 *     asserted for the same filters, because two code paths that agree today
 *     stop agreeing on the first refactor (§4.3).
 *   - Voided money is visible and excluded: a cancelled or soft-deleted row
 *     must never silently vanish (§4.4).
 *   - No operator edit. Asserted against the rendered HTML and the router,
 *     because "there is no button" is what an operator actually sees.
 */
class GodModePaymentsTest extends TestCase
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

    /** A receivable in $agency, encoded by a user of that agency. */
    private function receivable(Agency $agency, array $attrs = []): Receivable
    {
        $encoder = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);

        return Receivable::factory()->create(array_merge([
            'agency_id' => $agency->id,
            'user_id'   => $encoder->id,
            'date'      => now()->toDateString(),
            'status'    => 'received',
            'amount'    => 1000,
            'account'   => 'Placement Fee',
        ], $attrs));
    }

    private function filters(array $overrides = []): array
    {
        return app(GodReceivableService::class)->filters($overrides);
    }

    /** How many times a raw marker appears in the rendered HTML. */
    /**
     * A raw attribute without the base-class attribute normalisation, which
     * would otherwise match `data-rows-heading="pending-2"` for a request for
     * `data-rows-heading="pending"`.
     */
    private function rawOccurrences(\Illuminate\Testing\TestResponse $response, string $needle): int
    {
        return substr_count($response->getContent(), $needle);
    }

    private function occurrences(\Illuminate\Testing\TestResponse $response, string $needle): int
    {
        return substr_count($response->getContent(), $needle);
    }

    // ── access ───────────────────────────────────────────────────────

    #[Test]
    public function the_dashboard_payments_screen_and_drill_in_all_render_in_the_god_chrome(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 2500]);

        $this->actAsGod();

        $dashboard = $this->get(route('godmode.dashboard'))->assertOk();
        $dashboard->assertSee('God Mode');
        // Criterion 1: nav names all three rooms, and the old standalone page
        // (Tailwind CDN) is gone.
        $dashboard->assertSeeInOrder(['Dashboard', 'Agencies', 'Payments']);
        $dashboard->assertDontSee('cdn.tailwindcss.com');
        $dashboard->assertSee(route('godmode.payments.index'), false);

        $index = $this->get(route('godmode.payments.index'))->assertOk();
        $index->assertSee('Gulf Horizon');
        $index->assertSee('₱2,500.00');

        $show = $this->get(route('godmode.payments.show', $gulf))->assertOk();
        $show->assertSee('Gulf Horizon');
        $show->assertSee('₱2,500.00');
    }

    #[Test]
    public function god_screens_are_not_cacheable(): void
    {
        $this->actAsGod();

        foreach ([route('godmode.payments.index'), route('godmode.dashboard')] as $url) {
            $response = $this->get($url)->assertOk();

            $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'), $url);
        }
    }

    #[Test]
    public function an_agency_admin_on_the_web_guard_cannot_reach_the_ledger(): void
    {
        $agency = $this->agency('Gulf Horizon');
        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
            'password'  => 'password',
        ]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
        $this->assertTrue(auth('web')->check(), 'sanity: the web login succeeded');

        $this->get(route('godmode.payments.index'))->assertRedirect('/godmode/login');
        $this->get(route('godmode.payments.show', $agency))->assertRedirect('/godmode/login');
    }

    #[Test]
    public function a_super_admin_with_pending_status_is_refused_and_signed_out(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $pending = $this->god(['status' => 'pending']);
        $this->actAsGod($pending);

        $this->get(route('godmode.payments.index'))->assertForbidden();

        // No session survives: the second request is answered by the login
        // guard, not by the god gate, because the first one flushed the session.
        $this->assertFalse(auth('godmode')->check(), 'the suspended operator is signed out, not merely blocked');
        $this->get(route('godmode.dashboard'))->assertRedirect('/godmode/login');
        $this->get(route('godmode.payments.show', $gulf))->assertRedirect('/godmode/login');
    }

    #[Test]
    public function the_ledger_is_not_served_on_a_tenant_host(): void
    {
        $this->agency('Gulf Horizon');

        $this->assertSame(
            404,
            $this->call('GET', 'http://gulf-horizon.fixitautoservices.com/godmode/payments')->status()
        );
    }

    #[Test]
    public function an_unknown_agency_id_is_404_not_500(): void
    {
        $this->actAsGod();

        $this->get('/godmode/payments/999999')->assertNotFound();
    }

    // ── read only ────────────────────────────────────────────────────

    #[Test]
    public function no_screen_offers_an_edit_or_approve_control_on_a_receivable(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $row = $this->receivable($gulf, ['status' => 'pending']);
        $this->receivable($gulf);

        $this->actAsGod();

        $pages = [
            'the ledger'  => $this->get(route('godmode.payments.index'))->getContent(),
            'the drill-in' => $this->get(route('godmode.payments.show', $gulf))->getContent(),
            'the dashboard' => $this->get(route('godmode.dashboard'))->getContent(),
        ];

        foreach ($pages as $context => $html) {
            foreach (['Delete', 'delete', 'destroy', 'forceDelete', 'Approve', 'approve'] as $needle) {
                $this->assertStringNotContainsString($needle, $html, "{$context} must not offer a {$needle} control");
            }

            // No write verb on a receivable: every <form> present is a GET
            // filter or the god-mode sign-out.
            preg_match_all('/<form[^>]*method="([^"]*)"/i', $html, $methods);
            foreach ($methods[1] as $method) {
                $this->assertContains(strtoupper($method), ['GET', 'POST'], "{$context}: unexpected form method {$method}");
            }
            $this->assertStringNotContainsString('receivables/'.$row->id, $html, "{$context} links to a receivable write path");
        }

        // The router: nothing mutating exists under the ledger.
        foreach (['PUT', 'PATCH', 'DELETE'] as $verb) {
            $this->call($verb, '/godmode/payments/'.$gulf->id)->assertStatus(405);
        }
        $this->post('/godmode/payments/'.$gulf->id)->assertStatus(405);
        $this->post('/godmode/payments')->assertStatus(405);

        // And no data moved.
        $this->assertSame('pending', $row->fresh()->status);
        $this->assertSame(2, Receivable::withTrashed()->count());
    }

    // ── one computation ──────────────────────────────────────────────

    #[Test]
    public function per_agency_collected_sums_to_the_summary_collected_to_the_centavo(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $csdi = $this->agency('CSDI');

        $this->receivable($gulf, ['amount' => 1234.56]);
        $this->receivable($gulf, ['amount' => 0.01]);
        $this->receivable($csdi, ['amount' => 99999.99]);
        $this->receivable($csdi, ['amount' => 500, 'status' => 'pending']);
        $this->receivable($csdi, ['amount' => 700, 'status' => 'cancelled']);

        $this->actAsGod();

        $service = app(GodReceivableService::class);

        foreach ([
            [],
            ['account' => 'Placement Fee'],
            ['agency' => (string) $gulf->id],
            ['status' => 'pending'],
            ['q' => 'Gulf'],
        ] as $overrides) {
            $filters = $this->filters($overrides);
            $summary = $service->summary($filters);
            $rows = $service->perAgency($filters);

            $sum = 0;
            foreach ($rows as $row) {
                $sum += $service->toCents($row->collected);
            }

            $this->assertSame(
                $service->toCents($summary['collected']),
                $sum,
                'perAgency collected must equal summary() for filters '.json_encode($overrides)
            );
        }

        // 1,234.56 + 0.01 + 99,999.99 = 101,234.56
        $this->assertSame('101234.56', $service->summary($this->filters([]))['collected']);
    }

    #[Test]
    public function the_index_page_shows_the_same_collected_total_as_the_summary(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 1500.25]);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index'))->assertOk();

        $this->assertSame('1500.25', $response->viewData('summary')['collected']);
        $response->assertSee('₱1,500.25');
    }

    #[Test]
    public function agencies_with_no_collections_are_still_listed(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->agency('Empty Client');

        $this->receivable($gulf, ['amount' => 100]);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index'))->assertOk();

        $response->assertSee('Empty Client')->assertSee('₱0.00');

        $rows = collect($response->viewData('perAgency'))->keyBy('name');
        $this->assertSame('0.00', $rows['Empty Client']->collected);
        $this->assertSame('0.00', $rows['Empty Client']->outstanding);
        $this->assertNull($rows['Empty Client']->last_payment_date);
        $this->assertSame(0, $rows['Empty Client']->received_count);
    }

    /**
     * B4 (reconciliation honesty): the agency table is an inner join on
     * `agencies`, so a receivable whose agency id no longer resolves would
     * drop out of the table while still counting in the KPI above it — a
     * list that silently fails to add up to its own total. Either the money
     * appears under a named bucket or the screen says it is missing. Silent
     * is not an option.
     */
    #[Test]
    public function deleting_an_agency_removes_its_receivables_so_the_table_still_matches_the_kpi(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $orphan = $this->agency('Orphan Incoming');
        $engineer = User::factory()->create(['agency_id' => $orphan->id, 'user_type' => 'admin']);

        $this->receivable($gulf, ['amount' => 1000]);
        $this->receivable($orphan, ['amount' => 4242.42]);

        // The agency row goes away underneath the money (a hard delete or a
        // cross-agency move that did not carry its receivables). The receivable
        // row survives; nothing joins to it any more.
        DB::table('agencies')->where('id', $orphan->id)->delete();
        $this->assertSame(0, DB::table('receivables')->where('agency_id', $orphan->id)->count(), 'FK cascades: the row goes too');
        $unjoined = DB::table('receivables')->whereNotIn('agency_id', DB::table('agencies')->select('id'))->count();
        $this->assertSame(0, $unjoined, 'so no receivable can be lost between the KPI and the table');

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index'))->assertOk();

        $summary = $response->viewData('summary');
        $this->assertSame('1000.00', $summary['collected'], 'the deleted agency took its money with it, consistently everywhere');

        $listed = collect($response->viewData('perAgency'))->sum(fn ($row) => app(GodReceivableService::class)->toCents($row->collected));
        $this->assertSame(app(GodReceivableService::class)->toCents($summary['collected']), $listed, 'the agency table must add up to the KPI');

        // The screen does not pretend the money exists under a nameless row.
        $response->assertDontSee('₱4,242.42');
        unset($engineer);
    }

    // ── window ───────────────────────────────────────────────────────

    #[Test]
    public function from_after_to_is_swapped_not_rejected(): void
    {
        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index', ['from' => '2026-06', 'to' => '2026-01']))
            ->assertOk();

        $this->assertSame('2026-01', $response->viewData('filters')['from']);
        $this->assertSame('2026-06', $response->viewData('filters')['to']);
    }

    #[Test]
    public function an_unparseable_month_falls_back_instead_of_500(): void
    {
        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index', ['from' => 'yesterday', 'to' => '2026-13']))
            ->assertOk();

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $response->viewData('filters')['from']);
        $this->assertSame(now()->format('Y-m'), $response->viewData('filters')['to']);
    }

    #[Test]
    public function changing_the_window_moves_totals_and_series_together(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        $this->receivable($gulf, ['amount' => 1000, 'date' => '2026-02-10']);
        $this->receivable($gulf, ['amount' => 4000, 'date' => '2026-08-10']);

        $this->actAsGod();

        $narrow = $this->get(route('godmode.payments.index', ['from' => '2026-01', 'to' => '2026-03']))->assertOk();
        $this->assertSame('1000.00', $narrow->viewData('summary')['collected']);
        $this->assertSame(1000, array_sum(array_map(
            fn ($v) => app(GodReceivableService::class)->toCents($v),
            $narrow->viewData('series')
        )) / 100);

        $wide = $this->get(route('godmode.payments.index', ['from' => '2026-01', 'to' => '2026-12']))->assertOk();
        $this->assertSame('5000.00', $wide->viewData('summary')['collected']);
    }

    #[Test]
    public function the_series_is_twelve_zero_filled_months_ending_at_to(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 1234.56, 'date' => '2025-12-31']);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index', ['from' => '2025-01', 'to' => '2026-11']))->assertOk();

        $series = $response->viewData('series');

        $this->assertCount(12, $series);
        $this->assertSame('2026-11', array_key_last($series));
        $this->assertSame('2025-12', array_key_first($series));
        $this->assertSame('1234.56', $series['2025-12']);
        $this->assertSame('0.00', $series['2026-01']);

        // One bar per month, with a label under each, no NaN in the markup.
        $html = $response->getContent();
        $this->assertSame(12, substr_count($html, 'data-month="'));
        $this->assertSame(12, substr_count($html, 'data-label="'));
        $this->assertStringNotContainsString('NaN', $html);
        $this->assertStringContainsString('data-amount="0.00"', $html);
    }

    // ── accounts ─────────────────────────────────────────────────────

    #[Test]
    public function a_single_account_filter_totals_only_its_rows(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 700, 'account' => 'Placement Fee']);
        $this->receivable($gulf, ['amount' => 300, 'account' => 'Monthly Collection']);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.index', ['account' => 'Placement Fee']))->assertOk();

        $this->assertSame('700.00', $response->viewData('summary')['collected']);
    }

    #[Test]
    public function null_accounts_render_as_unspecified_and_are_counted_when_filtered(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 250, 'account' => null]);
        $this->receivable($gulf, ['amount' => 750, 'account' => 'Placement Fee']);

        $this->actAsGod();

        $all = $this->get(route('godmode.payments.index'))->assertOk();
        $this->assertSame('1000.00', $all->viewData('summary')['collected']);

        $mix = collect($all->viewData('byAccount'))->firstWhere('account', '(unspecified)');
        $this->assertNotNull($mix, 'the null-account rows must not be dropped from the mix');
        $this->assertSame('250.00', $mix['collected']);
        $all->assertSee('(unspecified)');

        $only = $this->get(route('godmode.payments.index', ['account' => '(unspecified)']))->assertOk();
        $this->assertSame('250.00', $only->viewData('summary')['collected']);
    }

    // ── status / voided ──────────────────────────────────────────────

    #[Test]
    public function pending_shows_outstanding_under_an_outstanding_heading(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 500, 'status' => 'received']);
        $this->receivable($gulf, ['amount' => 800, 'status' => 'pending']);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.show', [$gulf, 'status' => 'pending']))->assertOk();

        $this->assertSame('800.00', $response->viewData('summary')['outstanding']);
        $response->assertSee('Outstanding');

        // B2: the heading and the table agree. `status = pending` used to
        // render an "Outstanding" heading over rows that included received
        // ones — a table that contradicts its own label. The filter means
        // one thing on both screens: pending lists pending rows only.
        $rows = $response->viewData('rows');
        $this->assertSame(['pending'], array_values($rows->pluck('status')->unique()->all()));
        $this->assertSame($rows->count(), $this->occurrences($response, 'data-receivable-status="pending"'));
        $this->assertSame(0, $this->occurrences($response, 'data-receivable-status="received"'));

        // Same meaning on the ledger: `status = pending` is the outstanding
        // table, never the collected one.
        $ledger = $this->get(route('godmode.payments.index', ['status' => 'pending']))->assertOk();
        $this->assertSame('pending', $ledger->viewData('filters')['status']);
        // And the money on that screen is the outstanding money, not collected.
        $this->assertSame('0.00', $ledger->viewData('summary')['collected']);
        $this->assertSame('800.00', $ledger->viewData('summary')['outstanding']);
    }

    #[Test]
    public function a_soft_deleted_receivable_shows_with_a_badge_and_is_absent_from_collected(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 1000]);
        $deleted = $this->receivable($gulf, ['amount' => 9000]);
        $deleted->delete();

        $this->actAsGod();

        // `status=all` is the state that sees voided rows; `received` (the
        // default) legitimately hides them, so ask for the unfiltered view.
        $response = $this->get(route('godmode.payments.show', [$gulf, 'status' => 'all']))->assertOk();

        $this->assertSame('1000.00', $response->viewData('summary')['collected']);
        $this->assertSame(1, $response->viewData('summary')['voided_count']);

        $voided = $response->viewData('voided');
        $this->assertCount(1, $voided);
        $this->assertSame($deleted->id, $voided->first()->id);

        $response->assertSee('data-badge="deleted"', false);
        $response->assertSee('Voided money');

        // The ledger's own KPI strip reports the voided count too.
        // The ledger reports it on every status, including the default one:
        // the row is in the window, and the filter only narrows the money.
        $this->get(route('godmode.payments.index'))->assertOk()->assertSee('1 voided');
    }

    #[Test]
    public function a_cancelled_receivable_shows_with_a_badge_and_is_absent_from_collected(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 1000]);
        $this->receivable($gulf, ['amount' => 450, 'status' => 'cancelled']);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.show', [$gulf, 'status' => 'all']))->assertOk();

        $this->assertSame('1000.00', $response->viewData('summary')['collected']);
        $this->assertSame(1, $response->viewData('summary')['voided_count']);

        $response->assertSee('data-badge="cancelled"', false);
        $response->assertSee('₱450.00');
    }

    /**
     * B3: the voided badge rendered a raw `ucfirst(status)` — "Cancelled" —
     * while the same row's marker said `cancelled`, so the badge an operator
     * reads and the badge a QA script reads disagreed with the rest of the
     * screen's vocabulary (which uses `deleted`, lowercase). One word,
     * lowercase, `voided`, for cancelled-and-deleted alike.
     */
    #[Test]
    public function a_cancelled_row_carries_one_cancelled_badge_not_a_second_status_badge(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 1200]);
        $this->receivable($gulf, ['amount' => 450, 'status' => 'cancelled']);

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.show', [$gulf, 'status' => 'all']))->assertOk();

        // The row's own status badge says what the row is.
        $this->assertSame(1, $this->occurrences($response, 'data-receivable-status="cancelled"'));

        // The void list states the reason once, on the badge beside the
        // amount: "cancelled", lowercase, same vocabulary as `deleted`. The
        // second badge that used to repeat the status next to it is gone.
        $this->assertSame(1, $this->occurrences($response, 'data-badge="cancelled"'));
        $this->assertSame(0, $this->occurrences($response, 'data-badge="deleted"'));
        $this->assertSame(0, $this->occurrences($response, 'data-voided-status='));
        $this->assertSame(1, $this->occurrences($response, '>Cancelled</span>'));

        $voided = $response->viewData('voided');
        $this->assertCount(1, $voided);

        // The section says what it excludes, in words, not a cryptic heading.
        $response->assertSee('Excluded from totals');
        $response->assertSee('Voided money');
    }

    // ── search ───────────────────────────────────────────────────────

    // ── ageing (B1) ──────────────────────────────────────────────────

    /**
     * B1: `ageing()` re-converted an already-decimal accumulator with
     * `toCents()` on every bucket row, so the FIRST row in a bucket threw
     * `TypeError` (declared return `int` = "10.00" * 100 = 1000.0 float) and
     * the default 12-month window — the one every operator lands on —
     * returned 500. Passing row amounts through unchanged would have fixed
     * the 500 and left a 100x money error, so the assertion is on the
     * centavo value, not just the status code.
     */
    #[Test]
    public function ageing_renders_the_default_twelve_month_window_above_a_thousand_pesos(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        // Three buckets, several rows each, whole pesos and centavos, at
        // amounts that cross ₱999.99 so a 100x shift is unmistakable.
        $fixtures = [
            [0, '1500.50'], [5, '1500.50'], [29, '999.99'],
            [31, '2500.25'], [60, '0.01'],
            [61, '1000.00'],
            [120, '4000.40'], [200, '1234.56'],
        ];

        // Amounts are asserted as written-out decimal strings below, not
        // computed through `toCents()`/`decimal()`: re-deriving the expected
        // value with the same helper the bug lived in would pass whatever
        // the helper did.
        $counts = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        $service = app(GodReceivableService::class);

        foreach ($fixtures as [$daysAgo, $amount]) {
            $this->receivable($gulf, [
                'amount'   => $amount,
                'status'   => 'pending',
                'date'     => now()->subDays($daysAgo)->toDateString(),
            ]);

            $bucket = match (true) {
                $daysAgo <= 30 => '0-30',
                $daysAgo <= 60 => '31-60',
                $daysAgo <= 90 => '61-90',
                default        => '90+',
            };
            $counts[$bucket]++;
        }

        $this->actAsGod();

        // Default filters: no from/to, i.e. the 12-month window the operator
        // gets on first load. This is the request that used to 500.
        $response = $this->get(route('godmode.payments.index'))->assertOk();

        $ageing = $response->viewData('ageing');
        $this->assertSame(GodReceivableService::AGEING_BUCKETS, array_keys($ageing));

        // Bucket by bucket: the count AND the centavo amount, written out.
        // `assertOk()` above catches the 500; these catch a 100x shift.
        // 0-30 = 1500.50 + 1500.50 + 999.99; 31-60 = 2500.25 + 0.01.
        $this->assertSame($counts['0-30'], $ageing['0-30']['count']);
        $this->assertSame('4000.99', $ageing['0-30']['amount']);
        $this->assertSame('2500.26', $ageing['31-60']['amount']);
        $this->assertSame($counts['61-90'], $ageing['61-90']['count']);
        $this->assertSame('1000.00', $ageing['61-90']['amount']);
        $this->assertSame($counts['90+'], $ageing['90+']['count']);

        // Oldest bucket: 4000.40 + 1234.56 = 5234.96 — above ₱999.99, and not
        // 100x that, and not ₱52.35 from a float round-trip.
        $this->assertSame('5234.96', $ageing['90+']['amount']);
        $response->assertSee('₱5,234.96');

        // Every pending centavo in the window is in exactly one bucket. Ageing
        // is outstanding money, so the comparison is against the pending view
        // (B2: collected/pending are two different questions).
        $bucketed = 0;
        foreach ($ageing as $row) {
            $bucketed += $service->toCents($row['amount']);
        }
        $pending = app(GodReceivableService::class)->summary(app(GodReceivableService::class)->filters(['status' => 'pending']));
        $this->assertSame($service->toCents($pending['outstanding']), $bucketed);
    }

    /**
     * The money bug behind B1, isolated: a bucket's amount must be the sum of
     * its rows, at any row count and any centavo. A 100x conversion is the
     * only way "₱10.00" becomes "1000.00".
     */
    #[Test]
    public function ageing_bucket_amounts_are_the_exact_centavo_sum_of_their_rows(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        foreach (['10.00', '999.99', '0.01', '1500.05'] as $amount) {
            $this->receivable($gulf, ['amount' => $amount, 'status' => 'pending', 'date' => now()->subDays(10)->toDateString()]);
        }

        // One received row and one voided row must not appear in any bucket.
        $this->receivable($gulf, ['amount' => '5000.00', 'status' => 'received', 'date' => now()->subDays(10)->toDateString()]);
        $this->receivable($gulf, ['amount' => '7000.00', 'status' => 'pending', 'date' => now()->subDays(10)->toDateString()])->delete();

        $this->actAsGod();

        $ageing = $this->get(route('godmode.payments.index'))->assertOk()->viewData('ageing');

        // 10.00 + 999.99 + 0.01 + 1500.05 = 2510.05
        $this->assertSame('2510.05', $ageing['0-30']['amount']);
        $this->assertSame(4, $ageing['0-30']['count']);
        $this->assertSame('0.00', $ageing['90+']['amount']);
        $this->assertSame(0, $ageing['90+']['count']);
    }

    #[Test]
    public function a_percent_in_the_search_returns_zero_rows_not_the_whole_ledger(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['ref_ar' => 'AR-1234']);
        $this->receivable($gulf, ['ref_ar' => 'AR-9999']);

        $this->actAsGod();

        foreach (['%', '_', '%%', 'A%9'] as $wildcard) {
            $response = $this->get(route('godmode.payments.index', ['q' => $wildcard]))->assertOk();

            $this->assertSame(0, $response->viewData('summary')['row_count'], '"'.$wildcard.'" must be escaped');
            $this->assertSame('0.00', $response->viewData('summary')['collected']);
        }
    }

    #[Test]
    public function search_matches_reference_particular_and_applicant_name_on_the_drill_in(): void
    {
        $gulf = $this->agency('Gulf Horizon');

        $applicant = Applicant::factory()->create([
            'agency_id'  => $gulf->id,
            'first_name' => 'Maria',
            'last_name'  => 'Cruz',
        ]);

        $this->receivable($gulf, ['ref_ar' => 'AR-7788', 'particular' => 'First instalment']);
        $this->receivable($gulf, ['ref_ar' => 'ZZ-0001', 'particular' => 'Second instalment', 'applicant_id' => $applicant->id]);

        $this->actAsGod();

        foreach (['7788', 'First instalment', 'Cruz'] as $term) {
            $response = $this->get(route('godmode.payments.show', [$gulf, 'q' => $term, 'status' => 'all']))->assertOk();

            $this->assertSame(1, $response->viewData('summary')['row_count'], '"'.$term.'" must match exactly one row');
        }

        $byName = $this->get(route('godmode.payments.show', [$gulf, 'q' => 'Cruz', 'status' => 'all']))->assertOk();
        $byName->assertSee('Maria Cruz');
    }

    #[Test]
    public function the_applicant_name_is_shown_on_the_drill_in_and_never_on_the_aggregates(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $applicant = Applicant::factory()->create([
            'agency_id'  => $gulf->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
        ]);
        $this->receivable($gulf, ['applicant_id' => $applicant->id]);

        $this->actAsGod();

        $this->get(route('godmode.payments.show', $gulf))->assertOk()->assertSee('Juan Dela Cruz');
        $this->get(route('godmode.payments.index'))->assertOk()->assertDontSee('Juan Dela Cruz');
        $this->get(route('godmode.dashboard'))->assertOk()->assertDontSee('Juan Dela Cruz');
    }

    // ── dashboard ────────────────────────────────────────────────────

    #[Test]
    public function the_dashboard_kpis_and_top_agencies_agree_with_the_ledger(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $csdi = $this->agency('CSDI');
        $quiet = $this->agency('Quiet Client');

        $this->receivable($gulf, ['amount' => 5000]);
        $this->receivable($csdi, ['amount' => 3000]);
        $this->receivable($csdi, ['amount' => 200, 'status' => 'pending']);
        $this->receivable($quiet, ['amount' => 10, 'date' => now()->subYears(2)->toDateString()]);

        $this->actAsGod();

        $dashboard = $this->get(route('godmode.dashboard'))->assertOk();
        $ledger = $this->get(route('godmode.payments.index'))->assertOk();

        $this->assertSame(
            $ledger->viewData('summary')['collected'],
            $dashboard->viewData('summaryRolling')['collected'],
            'the dashboard tile and the ledger KPI are one computation'
        );

        $top = collect($dashboard->viewData('top'));
        $this->assertSame('Gulf Horizon', $top->first()->name, 'highest collected first');
        $this->assertSame('CSDI', $top->get(1)->name);
        $this->assertLessThanOrEqual(5, $top->count());

        // Each top agency links to its drill-in.
        foreach ($top as $row) {
            $dashboard->assertSee(route('godmode.payments.show', $row->id), false);
        }

        $dashboard->assertSee('Top 5 agencies by collected');
    }

    #[Test]
    public function an_empty_database_renders_both_screens_with_zeroes_not_nan(): void
    {
        $this->actAsGod();

        $index = $this->get(route('godmode.payments.index'))->assertOk();
        $index->assertSee('₱0.00');
        $this->assertStringNotContainsString('NaN', $index->getContent());
        $this->assertSame('0.00', $index->viewData('summary')['collected']);

        $this->get(route('godmode.dashboard'))->assertOk()->assertSee('₱0.00');
    }

    // ── query budget ─────────────────────────────────────────────────

    #[Test]
    public function the_ledger_does_not_scale_query_count_with_rows(): void
    {
        $agencies = [];

        for ($i = 1; $i <= 3; $i++) {
            $agencies[] = $this->agency('Agency '.$i);
        }

        // Fetching an existing user keeps the request a WEB request (the flag
        // stays on, so a scoped regression still fails here) while giving the
        // rows a real encoder: a factory create would need a tenant bound.
        $encoderId = DB::table('users')->where('agency_id', $agencies[0]->id)->value('id');

        foreach ($agencies as $agency) {
            $rows = [];
            for ($j = 0; $j < 60; $j++) {
                $rows[] = [
                    'agency_id' => $agency->id,
                    'user_id'   => $encoderId,
                    'code'      => str_pad((string) ($j + 1), 6, '0', STR_PAD_LEFT),
                    'date'      => now()->subMonths($j % 12)->toDateString(),
                    'status'    => $j % 3 === 0 ? 'pending' : 'received',
                    'amount'    => 100 + $j,
                    'account'   => 'Placement Fee',
                    'type'      => 'Full Payment',
                    'mode'      => 'Cash',
                    'particular' => 'Bulk row '.$j,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('receivables')->insert($rows);
        }

        $this->actAsGod();

        // Warm the route (container, view resolution) then count only the work.
        $this->get(route('godmode.payments.index'))->assertOk();

        $count = 0;
        DB::listen(function () use (&$count) {
            $count++;
        });

        $this->get(route('godmode.payments.index'))->assertOk();

        $this->assertGreaterThan(0, $count, 'the listener must have been exercised');
        $this->assertLessThanOrEqual(12, $count, 'the ledger ran '.$count.' queries for 180 rows; it must not scale per row');

        // And the numbers are still one computation.
        $service = app(GodReceivableService::class);
        $filters = $this->filters([]);
        $sum = 0;
        foreach ($service->perAgency($filters) as $row) {
            $sum += $service->toCents($row->collected);
        }
        $this->assertSame($service->toCents($service->summary($filters)['collected']), $sum);
    }

    // ── money format ─────────────────────────────────────────────────

    #[Test]
    public function money_is_formatted_from_the_decimal_string(): void
    {
        $service = app(GodReceivableService::class);

        $this->assertSame('₱0.00', $service->formatMoney('0.00'));
        $this->assertSame('₱0.00', $service->formatMoney(null));
        $this->assertSame('₱0.05', $service->formatMoney('0.05'));
        $this->assertSame('₱1,234,567.89', $service->formatMoney('1234567.89'));
        $this->assertSame('₱-12.30', $service->formatMoney('-12.3'));

        // Integer centavos round-trip: no float ever touches this.
        $this->assertSame(123456789, $service->toCents('1234567.89'));
        $this->assertSame('1234567.89', $service->decimal('1234567.89'));
        $this->assertSame('0.00', $service->decimal(null));
    }

    /**
     * B2, taken to its end: the status filter must narrow EVERY number on the
     * screen, not only the row list. `status=pending` used to leave a
     * Collected KPI, a collected-only chart and per-agency collected totals
     * above an outstanding-only table — three answers to one question.
     *
     * States: `received` = collected, `pending` = outstanding, `all` = every
     * live row (voided rows are never summed, in any state). For each one:
     * sum(perAgency) == summary() == the rendered KPI, and the listed rows are
     * exactly the rows that were summed.
     */
    #[Test]
    public function the_status_filter_narrows_every_number_on_both_screens(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $csdi = $this->agency('CSDI');
        $this->agency('Empty Client');

        $this->receivable($gulf, ['amount' => 1234.56]);
        $this->receivable($gulf, ['amount' => 800, 'status' => 'pending']);
        $this->receivable($csdi, ['amount' => 99.99]);
        $this->receivable($csdi, ['amount' => 500.50, 'status' => 'pending']);
        // Voided: counted, never summed, never in the live tables.
        $this->receivable($csdi, ['amount' => 700, 'status' => 'cancelled']);
        $this->receivable($gulf, ['amount' => 400])->delete();

        $this->actAsGod();

        // Expected money per state, in centavos. Two live received rows
        // (1,234.56 + 99.99) and two live pending rows (800.00 + 500.50).
        $expected = [
            // `rows` is what the state sums: received rows, pending rows, or
            // every live row. Voided rows are never summed in any state.
            'received' => ['collected' => 133455, 'outstanding' => 0, 'rows' => 2, 'statuses' => ['received'], 'live' => 2],
            'pending'  => ['collected' => 0, 'outstanding' => 130050, 'rows' => 2, 'statuses' => ['pending'], 'live' => 2],
            // `all` is every row in the window, voided included — the state
            // where the voided ones are visible. They are counted, not summed.
            'all'      => ['collected' => 133455, 'outstanding' => 130050, 'rows' => 6, 'statuses' => ['cancelled', 'pending', 'received'], 'live' => 6],
        ];

        foreach ($expected as $status => $want) {
            $filters = app(GodReceivableService::class)->filters(['status' => $status]);
            $summary = app(GodReceivableService::class)->summary($filters);


            $this->assertSame($want['collected'], app(GodReceivableService::class)->toCents($summary['collected']), "summary collected for {$status}");
            $this->assertSame($want['outstanding'], app(GodReceivableService::class)->toCents($summary['outstanding']), "summary outstanding for {$status}");
                    $this->assertSame($want['rows'], $summary['row_count'], "summary row_count for {$status}");

            // A fresh service: nothing below may come from a warm cache.
            $this->app->forgetInstance(GodReceivableService::class);
            $service = app(GodReceivableService::class);

            $sumCollected = $sumOutstanding = 0;
            foreach ($service->perAgency($filters) as $row) {
                $sumCollected += $service->toCents($row->collected);
                $sumOutstanding += $service->toCents($row->outstanding);
            }
            $this->assertSame($want['collected'], $sumCollected, "sum(perAgency collected) for {$status}");
            $this->assertSame($want['outstanding'], $sumOutstanding, "sum(perAgency outstanding) for {$status}");

            $sumByAccount = 0;
            foreach ($service->byAccount($filters) as $row) {
                $sumByAccount += $service->toCents($row['collected']) + $service->toCents($row['outstanding']);
            }
            $this->assertSame($want['collected'] + $want['outstanding'], $sumByAccount, "byAccount total for {$status}");

            $series = 0;
            foreach ($service->monthlySeries($filters) as $amount) {
                $series += $service->toCents($amount);
            }
            $this->assertSame($want['collected'], $series, "monthlySeries collected for {$status}");

            // The ledger screen: the rendered KPI is the service's number.
            $index = $this->get(route('godmode.payments.index', ['status' => $status]))->assertOk();
            $this->assertSame($status, $index->viewData('filters')['status'], "ledger honours status={$status}");
            $this->assertSame($service->formatMoney($summary['collected']), $this->kpi($index, 'collected'), "rendered Collected KPI for {$status}");
            $this->assertSame($service->formatMoney($summary['outstanding']), $this->kpi($index, 'outstanding'), "rendered Outstanding KPI for {$status}");
            $this->assertSame(number_format($want['rows']), $this->kpi($index, 'rows'), "rendered Rows KPI for {$status}");

            // ... and the table lists exactly the rows it counted.
            $page = $service->receivablePage($filters);
            $this->assertSame($want['live'], $page->total(), "ledger table row count for {$status}");
            $this->assertSame($want['statuses'], $this->rowStatuses($page), "ledger table statuses for {$status}");

            // The drill-in agrees with the ledger, row for row.
            $show = $this->get(route('godmode.payments.show', [$gulf, 'status' => $status]))->assertOk();
            $this->assertSame($status, $show->viewData('filters')['status'], "drill-in honours status={$status}");
            $showSummary = $show->viewData('summary');
            // One agency: its own live rows, and its own voided ones on `all`.
            $gulfStatuses = match ($status) {
                'pending' => ['pending'],
                'received' => ['received'],
                default => ['pending', 'received'],
            };
            $this->assertSame(
                $service->formatMoney($status === 'pending' ? '0.00' : $showSummary['collected']),
                $this->kpi($show, 'collected'),
                "drill-in Collected KPI for {$status}"
            );
            $this->assertSame(
                $service->formatMoney($status === 'received' ? '0.00' : $showSummary['outstanding']),
                $this->kpi($show, 'outstanding'),
                "drill-in Outstanding KPI for {$status}"
            );
            $this->assertSame(
                $showSummary['row_count'],
                $show->viewData('rows')->total(),
                "drill-in lists every row its summary counted for {$status}"
            );
            $this->assertSame(
                $gulfStatuses,
                $this->rowStatuses($show->viewData('rows')),
                "drill-in table statuses for {$status}"
            );
        }

        // The dashboard has no status filter: it still means the whole ledger.
        $dashboard = $this->get(route('godmode.dashboard'))->assertOk();
        $this->assertSame(
            133455,
            app(GodReceivableService::class)->toCents($dashboard->viewData('summaryRolling')['collected']),
            'the dashboard still reports all live collected money'
        );
    }

    /** The distinct statuses of the rows a paginator lists, sorted. */
    private function rowStatuses($rows): array
    {
        $statuses = array_values($rows->pluck('status')->unique()->all());
        sort($statuses);

        return $statuses;
    }

    /** The text inside `<span data-kpi="key">value</span>`, tag-free. */
    private function kpi(\Illuminate\Testing\TestResponse $response, string $key): string
    {
        $pattern = '/data-kpi="'.preg_quote($key, '/').'"[^>]*>(.*?)<\/span>/s';
        $this->assertSame(1, preg_match($pattern, $response->getContent(), $m), "KPI {$key} must render exactly once");

        return trim(strip_tags($m[1]));
    }

    // ── no audit storm ───────────────────────────────────────────────

    #[Test]
    public function viewing_the_ledger_writes_no_bypass_audit_row(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf);

        $this->actAsGod();

        $before = AuditEvent::withoutGlobalScopes()->count();

        $this->get(route('godmode.payments.index'))->assertOk();
        $this->get(route('godmode.payments.show', $gulf))->assertOk();
        $this->get(route('godmode.dashboard'))->assertOk();

        $this->assertSame($before, AuditEvent::withoutGlobalScopes()->count(), 'a page view is not an audit event');
    }
}
