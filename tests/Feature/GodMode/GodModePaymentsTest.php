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

        // The table is the detail behind the summary: it lists the unpaid row
        // and shows the received one in its own collected state, labelled —
        // collected and outstanding never swap labels or merge into one number.
        $this->assertSame(
            ['pending', 'received'],
            array_values($response->viewData('rows')->pluck('status')->unique()->sort()->values()->all())
        );
        $response->assertSee('data-receivable-status="pending"', false);
        $response->assertSee('data-receivable-status="received"', false);
    }

    #[Test]
    public function a_soft_deleted_receivable_shows_with_a_badge_and_is_absent_from_collected(): void
    {
        $gulf = $this->agency('Gulf Horizon');
        $this->receivable($gulf, ['amount' => 1000]);
        $deleted = $this->receivable($gulf, ['amount' => 9000]);
        $deleted->delete();

        $this->actAsGod();

        $response = $this->get(route('godmode.payments.show', $gulf))->assertOk();

        $this->assertSame('1000.00', $response->viewData('summary')['collected']);
        $this->assertSame(1, $response->viewData('summary')['voided_count']);

        $voided = $response->viewData('voided');
        $this->assertCount(1, $voided);
        $this->assertSame($deleted->id, $voided->first()->id);

        $response->assertSee('data-badge="deleted"', false);
        $response->assertSee('Voided money');

        // The ledger's own KPI strip reports the voided count too.
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

        $response->assertSee('data-voided-status="cancelled"', false);
        $response->assertSee('₱450.00');
    }

    // ── search ───────────────────────────────────────────────────────

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
