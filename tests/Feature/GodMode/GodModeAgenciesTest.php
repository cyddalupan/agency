<?php

namespace Tests\Feature\GodMode;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\AuditEvent;
use App\Models\Branch;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0344184136c21243a7af8b — god-plane Agencies console.
 *
 * What these tests are actually for:
 *
 *   - The list is UNSCOPED. A god operator has `agency_id NULL` and no tenant
 *     bound on the main host, so every one of these requests runs against the
 *     real web behaviour (TenantScope::testingAsWebRequest). Without that flag
 *     PHPUnit's console branch silently fails open and a scoped regression
 *     would pass — the exact green-but-wrong trap GodModeWebHostLoginTest was
 *     written for.
 *   - Archive, not delete. Criterion 15 is asserted against the rendered HTML,
 *     not by "there is no route": a delete *button* is what an operator clicks.
 *   - `godmode.only` is the only gate. An agency `admin` gets 403 here and
 *     would have been let through by AgencyPolicy.
 */
class GodModeAgenciesTest extends TestCase
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

    /**
     * An agency with real counts: $applicants applicants, $users staff,
     * $branches branches. `num_branches` is the DECLARED figure and is set
     * independently so the "declared N" subtext can be asserted.
     */
    private function agencyWith(string $name, array $opts = []): Agency
    {
        $agency = Agency::factory()->create(array_merge([
            'name'         => $name,
            'subdomain'    => strtolower(str_replace(' ', '-', $name)),
            'status'       => 'active',
            'num_branches' => $opts['declared'] ?? ($opts['branches'] ?? 0) ?: 1,
        ], array_diff_key($opts, array_flip(['applicants', 'users', 'branches', 'declared']))));

        Applicant::factory()->count($opts['applicants'] ?? 0)->create(['agency_id' => $agency->id]);
        Branch::factory()->count($opts['branches'] ?? 0)->create(['agency_id' => $agency->id]);

        User::factory()->count($opts['users'] ?? 0)->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);

        return $agency->fresh();
    }

    private function assertNoDeleteControl(string $html, string $context): void
    {
        foreach (['Delete', 'delete', 'destroy', 'forceDelete'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, "{$context} must not offer {$needle}");
        }
    }

    // ── listing ──────────────────────────────────────────────────────

    #[Test]
    public function the_dashboard_links_to_the_agencies_screen(): void
    {
        $this->actAsGod();

        $this->get(route('godmode.dashboard'))
            ->assertOk()
            ->assertSee('Agencies')
            ->assertSee(route('godmode.agencies.index'), false);
    }

    #[Test]
    public function the_list_is_unscoped_and_shows_both_agencies_with_live_counts(): void
    {
        $gulf = $this->agencyWith('Gulf Horizon', ['applicants' => 3, 'users' => 2, 'branches' => 2, 'declared' => 3]);
        $csdi = $this->agencyWith('CSDI', ['applicants' => 1, 'users' => 1, 'branches' => 1]);

        $this->actAsGod();

        $response = $this->get(route('godmode.agencies.index'))->assertOk();

        // Both tenants appear on ONE screen: this is the whole point of the
        // control plane, and a TenantScope leak would drop one of them.
        $response->assertSee('Gulf Horizon')->assertSee('CSDI');
        $response->assertSee('Agencies');

        // Counts come from the subqueries, not blanks and not lazy loads.
        $response->assertSeeInOrder(['Gulf Horizon', '>3<', '>2<', '>2<'], false);
        $response->assertSee('declared 3');

        // Subdomain is rendered as a usable host, not a bare label.
        $response->assertSee('gulf-horizon.landas.fixitautoservices.com');
    }

    #[Test]
    public function an_agency_with_no_subdomain_gets_a_badge_not_an_empty_cell(): void
    {
        $this->agencyWith('Orphan', ['subdomain' => null]);

        $this->actAsGod();

        $this->get(route('godmode.agencies.index'))
            ->assertOk()
            ->assertSee('no subdomain')
            ->assertSee('data-badge="no-subdomain"', false);
    }

    #[Test]
    public function the_branch_cell_shows_declared_only_when_it_differs_from_the_real_count(): void
    {
        $this->agencyWith('Matches', ['branches' => 2, 'declared' => 2]);
        $this->agencyWith('Mismatch', ['branches' => 6, 'declared' => 7]);

        $this->actAsGod();

        $html = $this->get(route('godmode.agencies.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'declared 7'), 'only the mismatched agency carries the subtext');
        $this->assertStringNotContainsString('declared 2', $html);
    }

    #[Test]
    public function no_suspended_status_is_renderable(): void
    {
        $this->agencyWith('Live', ['status' => 'active']);
        $this->agencyWith('Waiting', ['status' => 'pending']);
        $this->agencyWith('Gone', ['status' => 'inactive']);

        $this->actAsGod();

        $this->get(route('godmode.agencies.index'))
            ->assertOk()
            ->assertSee('>Active</span>', false)
            ->assertSee('>Pending</span>', false)
            ->assertSee('>Inactive</span>', false)
            ->assertDontSee('Suspended');
    }

    // ── pagination ───────────────────────────────────────────────────

    #[Test]
    public function the_list_paginates_25_per_page_and_honours_per(): void
    {
        Agency::factory()->count(30)->sequence(fn ($s) => ['name' => sprintf('Agency %02d', $s->index)])
            ->create(['subdomain' => null]);

        $this->actAsGod();

        $first = $this->get(route('godmode.agencies.index'))->assertOk();
        $this->assertCount(25, $first->viewData('agencies')->items());
        $this->assertSame(30, $first->viewData('agencies')->total());

        // The second page differs from the first and page 2 is reachable.
        $second = $this->get(route('godmode.agencies.index', ['page' => 2]))->assertOk();
        $this->assertCount(5, $second->viewData('agencies')->items());

        $fifty = $this->get(route('godmode.agencies.index', ['per' => 50]))->assertOk();
        $this->assertCount(30, $fifty->viewData('agencies')->items());

        // An unknown `per` falls back to 25 instead of trusting the input.
        $bogus = $this->get(route('godmode.agencies.index', ['per' => 100000]))->assertOk();
        $this->assertCount(25, $bogus->viewData('agencies')->items());
    }

    #[Test]
    public function pagination_links_keep_the_active_filters(): void
    {
        Agency::factory()->count(30)->sequence(fn ($s) => ['name' => 'Gulf Agency '.$s->index, 'subdomain' => null])
            ->create(['status' => 'active']);

        $this->actAsGod();

        $html = $this->get(route('godmode.agencies.index', ['q' => 'gulf', 'status' => 'active']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('q=gulf', $html);
        $this->assertStringContainsString('status=active', $html);
    }

    // ── search / filter / sort ───────────────────────────────────────

    #[Test]
    public function search_matches_name_subdomain_email_and_contact_person(): void
    {
        $this->agencyWith('Gulf Horizon', ['subdomain' => 'gulf']);
        $this->agencyWith('Other', ['subdomain' => 'other', 'email' => 'findme@example.test', 'contact_person' => 'Maria Cruz']);

        $this->actAsGod();

        $byName = $this->get(route('godmode.agencies.index', ['q' => 'gulf']))->assertOk();
        $this->assertSame(['Gulf Horizon'], $this->names($byName));

        $byEmail = $this->get(route('godmode.agencies.index', ['q' => 'findme']))->assertOk();
        $this->assertSame(['Other'], $this->names($byEmail));

        $byContact = $this->get(route('godmode.agencies.index', ['q' => 'Cruz']))->assertOk();
        $this->assertSame(['Other'], $this->names($byContact));
    }

    #[Test]
    public function the_search_box_round_trips_through_the_query_string(): void
    {
        $this->agencyWith('Gulf Horizon');
        $this->agencyWith('Other');

        $this->actAsGod();

        $html = $this->get(route('godmode.agencies.index', ['q' => 'gulf']))->assertOk()->getContent();

        $this->assertStringContainsString('name="q" value="gulf"', $html);
        $this->assertSame(['Gulf Horizon'], $this->names($this->get(route('godmode.agencies.index', ['q' => 'gulf']))));
    }

    #[Test]
    public function a_wildcard_in_the_search_returns_nothing_instead_of_everything(): void
    {
        $this->agencyWith('Gulf Horizon');
        $this->agencyWith('CSDI');

        $this->actAsGod();

        foreach (['%', '_', '%%', 'g%f'] as $wildcard) {
            $response = $this->get(route('godmode.agencies.index', ['q' => $wildcard]))->assertOk();

            $this->assertSame(
                [],
                $this->names($response),
                'the wildcard "'.$wildcard.'" must be escaped, not interpreted'
            );
        }
    }

    #[Test]
    public function status_tabs_count_the_whole_set_and_filters_do_not_move_the_counts(): void
    {
        $this->agencyWith('Pending One', ['status' => 'pending']);
        $this->agencyWith('Pending Two', ['status' => 'pending']);
        $this->agencyWith('Pending Three', ['status' => 'pending']);
        $this->agencyWith('Live One', ['status' => 'active']);
        $this->agencyWith('Archived One', ['status' => 'inactive']);

        $this->actAsGod();

        $all = $this->get(route('godmode.agencies.index'))->assertOk();
        $this->assertSame(['all' => 5, 'active' => 1, 'pending' => 3, 'inactive' => 1], $all->viewData('statusCounts'));

        $pending = $this->get(route('godmode.agencies.index', ['status' => 'pending', 'q' => 'nope']))->assertOk();
        $this->assertSame(3, $pending->viewData('statusCounts')['pending'], 'search must not shrink the tab count');
        $this->assertSame([], $this->names($pending));

        $html = $this->get(route('godmode.agencies.index'))->getContent();
        $this->assertStringContainsString('Pending <span class="ml-1 opacity-60">3</span>', $html);

        $this->assertCount(3, $this->names($this->get(route('godmode.agencies.index', ['status' => 'pending']))));
    }

    #[Test]
    public function only_empty_agencies_hides_any_agency_with_users_or_applicants(): void
    {
        $this->agencyWith('Gulf Horizon', ['applicants' => 2, 'users' => 1]);
        $this->agencyWith('OnlyUsers', ['users' => 1]);
        $this->agencyWith('OnlyApplicants', ['applicants' => 1]);
        $this->agencyWith('Truly Empty');

        $this->actAsGod();

        $response = $this->get(route('godmode.agencies.index', ['empty' => 1]))->assertOk();

        $this->assertSame(['Truly Empty'], $this->names($response));
    }

    #[Test]
    public function sorting_applicants_desc_puts_the_biggest_agency_first_and_round_trips_in_the_url(): void
    {
        $this->agencyWith('Small', ['applicants' => 1]);
        $this->agencyWith('Gulf Horizon', ['applicants' => 5]);

        $this->actAsGod();

        $response = $this->get(route('godmode.agencies.index', ['sort' => 'applicants', 'dir' => 'desc']))->assertOk();

        $this->assertSame(['Gulf Horizon', 'Small'], $this->names($response));

        $html = $response->getContent();
        $this->assertStringContainsString('sort=applicants', $html);
        $this->assertStringContainsString('dir=desc', $html);
    }

    #[Test]
    public function the_default_order_puts_pending_first(): void
    {
        $this->agencyWith('Aardvark Active', ['status' => 'active']);
        $this->agencyWith('Zulu Pending', ['status' => 'pending']);

        $this->actAsGod();

        $this->assertSame(['Zulu Pending', 'Aardvark Active'], $this->names($this->get(route('godmode.agencies.index'))));
    }

    #[Test]
    public function an_unknown_sort_column_is_ignored_rather_than_500(): void
    {
        $this->agencyWith('Gulf Horizon');

        $this->actAsGod();

        $this->get(route('godmode.agencies.index', ['sort' => 'settings->owner; drop table agencies']))
            ->assertOk()
            ->assertSee('Gulf Horizon');
    }

    // ── create ───────────────────────────────────────────────────────

    #[Test]
    public function create_rejects_reserved_upper_case_duplicate_and_bad_subdomains(): void
    {
        $this->agencyWith('Gulf Horizon', ['subdomain' => 'gulf', 'email' => 'taken@example.test']);

        $this->actAsGod();

        $payload = $this->validPayload();

        // `godmode` — reserved: a tenant there would collide with the console.
        $this->post(route('godmode.agencies.store'), array_merge($payload, ['subdomain' => 'godmode']))
            ->assertSessionHasErrors('subdomain');

        // `WWW` is upper case AND reserved; normalisation must still catch it.
        $this->post(route('godmode.agencies.store'), array_merge($payload, ['subdomain' => 'WWW']))
            ->assertSessionHasErrors('subdomain');

        // Duplicate subdomain.
        $this->post(route('godmode.agencies.store'), array_merge($payload, ['subdomain' => 'gulf']))
            ->assertSessionHasErrors('subdomain');

        // Duplicate email (case-insensitively, after lowercasing).
        $this->post(route('godmode.agencies.store'), array_merge($payload, ['subdomain' => 'fresh', 'email' => 'TAKEN@example.test']))
            ->assertSessionHasErrors('email');

        $this->assertNull(Agency::where('name', 'Spec Check')->first(), 'no partial create may survive a failed validation');
    }

    #[Test]
    public function validation_errors_keep_the_operators_input(): void
    {
        $this->actAsGod();

        $this->post(route('godmode.agencies.store'), [
            'name'           => 'Typed Name',
            'subdomain'      => 'godmode',
            'contact_person' => 'Typed Person',
            'num_branches'   => 2,
            'status'         => 'active',
        ])->assertSessionHasErrors('subdomain');

        $this->get(route('godmode.agencies.create'))
            ->assertOk()
            ->assertSee('Typed Name')
            ->assertSee('Typed Person');
    }

    #[Test]
    public function pending_is_not_selectable_and_required_fields_are_enforced(): void
    {
        $this->actAsGod();

        $this->post(route('godmode.agencies.store'), array_merge($this->validPayload(), ['status' => 'pending']))
            ->assertSessionHasErrors('status');

        $this->post(route('godmode.agencies.store'), array_merge($this->validPayload(), ['status' => 'suspended']))
            ->assertSessionHasErrors('status');

        $this->post(route('godmode.agencies.store'), [])->assertSessionHasErrors([
            'name', 'subdomain', 'contact_person', 'num_branches', 'status',
        ]);

        $this->post(route('godmode.agencies.store'), array_merge($this->validPayload(), ['num_branches' => 0]))
            ->assertSessionHasErrors('num_branches');
        $this->post(route('godmode.agencies.store'), array_merge($this->validPayload(), ['num_branches' => 201]))
            ->assertSessionHasErrors('num_branches');

        $this->get(route('godmode.agencies.create'))
            ->assertDontSee('value="pending"', false)
            ->assertDontSee('Suspended');
    }

    #[Test]
    public function create_writes_the_agency_the_audit_event_and_the_activity_row(): void
    {
        $god = $this->god();
        $this->actAsGod($god);

        $this->post(route('godmode.agencies.store'), array_merge($this->validPayload(), [
            'subdomain' => 'https://spec-check.landas.fixitautoservices.com/some/path',
        ]))->assertRedirect();

        $agency = Agency::where('subdomain', 'spec-check')->firstOrFail();
        $this->assertSame('Spec Check', $agency->name);
        $this->assertSame('active', $agency->status);
        $this->assertSame(2, (int) $agency->num_branches);

        $event = AuditEvent::withoutGlobalScopes()
            ->where('subject_type', Agency::class)
            ->where('subject_id', $agency->id)
            ->where('action', 'created')
            ->firstOrFail();
        $this->assertSame($god->id, $event->actor_user_id);
        $this->assertSame('spec-check', $event->after['subdomain']);

        // Visible in /audit-trail (criterion 13): the row is real and named.
        $this->assertDatabaseHas('activity_logs', [
            'action'  => 'godmode.agency_created',
            'user_id' => $god->id,
        ]);

        $this->get(route('godmode.agencies.index'))->assertOk()->assertSee('Spec Check');
    }

    // ── edit ─────────────────────────────────────────────────────────

    #[Test]
    public function edit_changes_the_identity_and_records_actor_and_both_values(): void
    {
        $agency = $this->agencyWith('Spec Check', ['subdomain' => 'spec-check']);
        $god = $this->god();
        $this->actAsGod($god);

        $this->put(route('godmode.agencies.update', $agency), array_merge($this->validPayload(), [
            'name'      => 'Spec Check Renamed',
            'subdomain' => 'spec-check',
        ]))->assertRedirect(route('godmode.agencies.show', $agency));

        $this->assertSame('Spec Check Renamed', $agency->fresh()->name);

        $this->get(route('godmode.agencies.index'))->assertOk()->assertSee('Spec Check Renamed');

        $event = AuditEvent::withoutGlobalScopes()
            ->where('subject_id', $agency->id)
            ->where('action', 'updated')
            ->firstOrFail();

        $this->assertSame($god->id, $event->actor_user_id);
        $this->assertSame('Spec Check', $event->before['name']);
        $this->assertSame('Spec Check Renamed', $event->after['name']);

        // The drawer's history names the actor and the change.
        $this->get(route('godmode.agencies.show', $agency))
            ->assertOk()
            ->assertSee('Spec Check Renamed')
            ->assertSee('landlord')
            ->assertSee('Spec Check Renamed')
            ->assertSee('name:');
    }

    #[Test]
    public function an_untouched_edit_form_writes_no_audit_row(): void
    {
        $agency = $this->agencyWith('Spec Check', ['subdomain' => 'spec-check']);
        $this->actAsGod();

        $payload = array_merge($this->validPayload(), array_filter([
            'name'           => $agency->name,
            'subdomain'      => $agency->subdomain,
            'email'          => $agency->email,
            'contact_person' => $agency->contact_person,
            'city'           => $agency->city,
            'address'        => $agency->address,
            'num_branches'   => $agency->num_branches,
            'status'         => $agency->status,
        ], fn ($v) => $v !== null));

        $this->put(route('godmode.agencies.update', $agency), $payload)->assertRedirect();

        $this->assertSame(0, AuditEvent::withoutGlobalScopes()
            ->where('subject_id', $agency->id)
            ->where('action', 'updated')
            ->count());
    }

    #[Test]
    public function a_subdomain_cannot_be_blanked_from_the_ui_and_can_be_changed_with_a_warning(): void
    {
        $agency = $this->agencyWith('Spec Check', ['subdomain' => 'spec-check']);
        $this->actAsGod();

        $this->put(route('godmode.agencies.update', $agency), array_merge($this->validPayload(), ['subdomain' => '']))
            ->assertSessionHasErrors('subdomain');

        $this->get(route('godmode.agencies.edit', $agency))
            ->assertOk()
            ->assertSee('changes the agency');

        // Re-submitting the edit shows the error on the form itself.
        $this->from(route('godmode.agencies.edit', $agency))
            ->put(route('godmode.agencies.update', $agency), array_merge($this->validPayload(), ['subdomain' => '']));
        $this->get(route('godmode.agencies.edit', $agency))
            ->assertOk()
            ->assertSee('A subdomain is required');

        $this->put(route('godmode.agencies.update', $agency), array_merge($this->validPayload(), ['subdomain' => 'spec-check-2']))
            ->assertRedirect();

        $this->assertSame('spec-check-2', $agency->fresh()->subdomain);
    }

    // ── archive ──────────────────────────────────────────────────────

    #[Test]
    public function archive_sets_inactive_keeps_the_row_and_names_the_counts_in_the_confirmation(): void
    {
        $agency = $this->agencyWith('Gulf Horizon', ['applicants' => 4776, 'users' => 42, 'branches' => 6, 'declared' => 7]);
        $god = $this->god();
        $this->actAsGod($god);

        $html = $this->get(route('godmode.agencies.index'))->assertOk()->getContent();
        $this->assertStringContainsString('4,776 applicants and 42 users', $html);

        $this->post(route('godmode.agencies.archive', $agency), ['_return' => 'q=gulf&status=active'])
            ->assertRedirect(route('godmode.agencies.index', ['q' => 'gulf', 'status' => 'active']));

        $this->assertSame('inactive', $agency->fresh()->status);
        $this->assertSame(1, Agency::count(), 'archive must not remove the row');

        $this->assertDatabaseHas('audit_events', [
            'subject_id'    => $agency->id,
            'action'        => 'archived',
            'actor_user_id' => $god->id,
        ]);

        $this->get(route('godmode.agencies.index', ['q' => 'gulf']))
            ->assertOk()
            ->assertSee('Inactive');
    }

    #[Test]
    public function an_archived_agency_is_excluded_from_the_active_tab_and_identifyagency_stops_serving_it(): void
    {
        $agency = $this->agencyWith('Gulf Horizon', ['subdomain' => 'gulf', 'status' => 'active']);

        $this->actAsGod();

        $this->post(route('godmode.agencies.archive', $agency))->assertRedirect();

        // List: still present under All, gone from Active.
        $this->assertSame(['Gulf Horizon'], $this->names($this->get(route('godmode.agencies.index'))));
        $this->assertSame([], $this->names($this->get(route('godmode.agencies.index', ['status' => 'active']))));

        // Tenant host: IdentifyAgency only binds `active`, so the subdomain 404s.
        $this->assertSame(
            404,
            $this->call('GET', 'http://gulf.fixitautoservices.com/login')->status()
        );
    }

    #[Test]
    public function activate_restores_the_badge_and_the_tenant_serves_again(): void
    {
        $agency = $this->agencyWith('Gulf Horizon', ['subdomain' => 'gulf', 'status' => 'inactive']);

        $this->actAsGod();

        $this->post(route('godmode.agencies.activate', $agency))->assertRedirect();

        $this->assertSame('active', $agency->fresh()->status);
        $this->assertDatabaseHas('audit_events', ['subject_id' => $agency->id, 'action' => 'approved']);

        $this->get(route('godmode.agencies.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Gulf Horizon')
            ->assertSee('>Active</span>', false);

        $this->assertNotSame(
            404,
            $this->call('GET', 'http://gulf.fixitautoservices.com/login')->status()
        );
    }

    #[Test]
    public function archiving_an_already_archived_agency_writes_no_second_event(): void
    {
        $agency = $this->agencyWith('Gulf Horizon', ['status' => 'inactive']);
        $this->actAsGod();

        $this->post(route('godmode.agencies.archive', $agency))->assertRedirect();

        $this->assertSame(0, AuditEvent::withoutGlobalScopes()
            ->where('subject_id', $agency->id)
            ->where('action', 'archived')
            ->count());
    }

    #[Test]
    public function the_return_target_cannot_be_an_open_redirect(): void
    {
        $agency = $this->agencyWith('Gulf Horizon');
        $this->actAsGod();

        $this->post(route('godmode.agencies.archive', $agency), ['_return' => 'https://evil.example/phish'])
            ->assertRedirect(route('godmode.agencies.index'));
    }

    // ── no delete ────────────────────────────────────────────────────

    #[Test]
    public function no_screen_offers_a_delete_action_and_no_delete_route_exists(): void
    {
        $agency = $this->agencyWith('Gulf Horizon');

        $this->actAsGod();

        $this->assertNoDeleteControl($this->get(route('godmode.agencies.index'))->getContent(), 'the list');
        $this->assertNoDeleteControl($this->get(route('godmode.agencies.show', $agency))->getContent(), 'the drawer');
        $this->assertNoDeleteControl($this->get(route('godmode.agencies.edit', $agency))->getContent(), 'the edit form');
        $this->assertNoDeleteControl($this->get(route('godmode.agencies.create'))->getContent(), 'the create form');

        // A delete route would also be reachable by guess; it must not exist.
        $this->delete(route('godmode.agencies.show', $agency))->assertStatus(405);
        $this->post('/godmode/agencies/'.$agency->id.'/delete')->assertStatus(404);

        $this->assertSame(1, Agency::count());
    }

    // ── authorisation ────────────────────────────────────────────────

    #[Test]
    public function a_non_super_admin_never_reaches_the_console(): void
    {
        $agency = $this->agencyWith('Gulf Horizon');

        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'username'  => 'agencyadmin',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->actAsGod($admin);

        $this->get(route('godmode.agencies.index'))->assertForbidden();
        // `{agency}` is bound by SubstituteBindings, which runs before route
        // middleware in this Laravel version: an admin on the god guard must
        // not get a rendered screen out of a bound route either. 403 or a
        // redirect to the god login both fail closed; asserted as "not 200".
        $this->assertNothingRendered($this->get(route('godmode.agencies.show', $agency)));
        $this->assertNothingRendered($this->get(route('godmode.agencies.edit', $agency)));
        $this->assertNothingRendered($this->get(route('godmode.agencies.create')));
        $this->assertNothingRendered($this->post(route('godmode.agencies.archive', $agency)));
        $this->assertNothingRendered($this->post(route('godmode.agencies.store'), $this->validPayload()));

        // Nothing leaked and nothing changed.
        $this->assertSame('active', $agency->fresh()->status);
        $this->assertNull(Agency::where('name', 'Spec Check')->first());
    }

    #[Test]
    public function an_agency_admin_on_the_web_guard_cannot_reach_the_console(): void
    {
        $agency = $this->agencyWith('Gulf Horizon');
        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
            'password'  => 'password',
        ]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
        $this->assertTrue(auth('web')->check(), 'sanity: the web login succeeded');

        $this->get(route('godmode.agencies.index'))->assertRedirect('/godmode/login');
    }

    #[Test]
    public function the_console_is_not_served_on_a_tenant_host(): void
    {
        $this->agencyWith('Gulf Horizon', ['subdomain' => 'gulf']);

        $this->assertSame(
            404,
            $this->call('GET', 'http://gulf.fixitautoservices.com/godmode/agencies')->status()
        );
    }

    #[Test]
    public function god_screens_are_not_cacheable(): void
    {
        $this->actAsGod();

        $response = $this->get(route('godmode.agencies.index'))->assertOk();

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    // ── helpers ──────────────────────────────────────────────────────

    /** `/godmode/agencies/create` is shadowed by `{agency}`: see routes/web.php. */
    private function assertNothingRendered($response): void
    {
        $this->assertNotSame(200, $response->status());
    }

    /** @return array<int, string> */
    private function names($response): array
    {
        return array_values($response->viewData('agencies')->getCollection()->pluck('name')->all());
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Spec Check',
            'subdomain'      => 'spec-check',
            'email'          => 'spec@example.test',
            'contact_person' => 'Spec Person',
            'city'           => 'Manila',
            'address'        => '1 Test Street',
            'num_branches'   => 2,
            'status'         => 'active',
        ], $overrides);
    }
}
