<?php

namespace Tests\Feature\Routing;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Country;
use App\Models\CustomFieldDefinition;
use App\Models\Language;
use App\Models\Position;
use App\Models\Skill;
use App\Models\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * CYD 2026-11-17 card — "resource routes point at controller show() methods
 * that do not exist — 500 on direct URL".
 *
 * `Route::resource()` registers a show route by default, but none of these
 * nine controllers implements show(). Before the fix, a direct GET to any of
 * these URLs raised "Call to undefined method ...::show()" — a 500. The routes
 * are now excluded, so the path is rejected as Method Not Allowed.
 *
 * The expected answer is 405, not 404: except(['show']) removes only the GET
 * route, so `/{resource}/{id}` still matches update (PUT|PATCH) and destroy
 * (DELETE). Requesting it with GET is therefore Method Not Allowed — the right
 * answer, and a better one than 404, because the response carries
 * `Allow: PUT, PATCH, DELETE` and tells the caller the resource exists.
 *
 * Asserting only "not 500" would happily pass on a 302 to login or a 200 that
 * rendered the wrong page, so the status is asserted exactly, plus the Allow
 * header, which is what distinguishes 405-for-the-right-reason from
 * 405-because-something-else-went-wrong.
 *
 * URL paths are written literally rather than via route(): once the routes are
 * excluded, route() throws RouteNotFoundException, so a test built on it could
 * never observe the 405 it is meant to assert.
 *
 * Two similarly named things, easy to confuse — and confusing them is exactly
 * how `custom-fields` was missed on the first pass:
 *  - `custom-fields` IS a registered resource (routes/web.php) whose show()
 *    does not exist. It is the ninth dead route and IS covered below.
 *  - `custom-field-definitions` is only a lowercase alias route-name block
 *    that hand-registers index/store/update/destroy. It registers no show
 *    route, so there is nothing to exclude and nothing broken there.
 * Not covered: agent_report.show, the canonical agent detail page, which is
 * exercised by AgentReportModule\AgentReportTest.
 */
class ResourceShowRouteTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // agent_report is gated to Gulf/CSDI/FINAS agencies; an arbitrary
        // subdomain would 403 assertions that touch the ledger.
        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    /** route name => [literal URL path, bound model used to build it]. */
    private function cases(): array
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
        ]);
        $commission = Commission::factory()->create();
        $payment = CommissionPayment::factory()->create([
            'commission_id' => $commission->id,
        ]);
        $customField = CustomFieldDefinition::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        return [
            'agents.show'       => ['/agents/'.$agent->id, null],
            'branches.show'     => ['/branches/'.$branch->id, null],
            'countries.show'    => ['/countries/'.Country::factory()->create()->id, null],
            'positions.show'    => ['/positions/'.Position::factory()->create()->id, null],
            'skills.show'       => ['/skills/'.Skill::factory()->create()->id, null],
            'languages.show'    => ['/languages/'.Language::factory()->create()->id, null],
            // StatusCode's primary key is `code`, not `id` (incrementing = false),
            // so $model->id is null and would collapse the path to /status-codes/.
            'status-codes.show' => ['/status-codes/'.StatusCode::factory()->create()->code, null],
            'custom-fields.show' => ['/custom-fields/'.$customField->id, null],
            'commissions.commission-payments.show' => [
                "/commissions/{$commission->id}/commission-payments/{$payment->id}",
                null,
            ],
        ];
    }

    #[Test]
    public function the_nine_show_routes_are_not_registered(): void
    {
        // Without this, a re-registered route could still fail the URL test
        // below for an unrelated reason and pass by accident.
        foreach (array_keys($this->cases()) as $name) {
            $this->assertNull(
                Route::getRoutes()->getByName($name),
                "Route [{$name}] is still registered, but no controller defines "
                .'show() — requesting it would 500.'
            );
        }
    }

    #[Test]
    public function the_nine_show_urls_return_405_not_500(): void
    {
        foreach ($this->cases() as $name => [$path, $_]) {
            $response = $this->actingAs($this->admin)->get($path);

            $this->assertSame(
                405,
                $response->getStatusCode(),
                "GET {$path} ({$name}) returned {$response->getStatusCode()}, expected 405. "
                .'500 means a show() route exists with no show() method; 200 means show() was implemented here.'
            );

            // 405 for the right reason: the write verbs are what remain.
            $allow = $response->headers->get('Allow') ?? '';
            foreach (['PUT', 'PATCH', 'DELETE'] as $verb) {
                $this->assertStringContainsString(
                    $verb,
                    $allow,
                    "GET {$path} ({$name}) is 405 but Allow is '{$allow}' — expected {$verb} to be offered."
                );
            }

            $this->assertStringNotContainsString(
                'GET',
                $allow,
                "GET {$path} ({$name}) is offered in Allow — the show route is still registered."
            );
        }
    }

    #[Test]
    public function unauthenticated_visits_get_405_rather_than_500(): void
    {
        // These paths are resolved by the router before auth runs, so a guest
        // sees the same 405 — and crucially not a 500 or a login bounce.
        foreach ($this->cases() as $name => [$path, $_]) {
            $response = $this->get($path);

            $this->assertSame(
                405,
                $response->getStatusCode(),
                "GET {$path} ({$name}) returned {$response->getStatusCode()} for a guest, expected 405."
            );
        }
    }

    #[Test]
    public function the_crud_routes_beside_show_are_still_registered(): void
    {
        // except(['show']) must not have removed anything else.
        $resources = [
            'agents', 'branches', 'countries', 'positions', 'skills', 'languages',
            'status-codes', 'custom-fields',
        ];

        foreach ($resources as $resource) {
            foreach (['index', 'create', 'store', 'edit', 'update', 'destroy'] as $action) {
                $this->assertNotNull(
                    Route::getRoutes()->getByName("{$resource}.{$action}"),
                    "Route [{$resource}.{$action}] disappeared — except(['show']) removed too much."
                );
            }
        }

        foreach (['index', 'create', 'store', 'edit', 'update', 'destroy'] as $action) {
            $this->assertNotNull(
                Route::getRoutes()->getByName("commissions.commission-payments.{$action}"),
                "Route [commissions.commission-payments.{$action}] disappeared."
            );
        }
    }

    #[Test]
    public function the_alias_block_still_has_its_four_routes(): void
    {
        // custom-field-definitions registers no show route by design.
        foreach (['index', 'store', 'update', 'destroy'] as $action) {
            $this->assertNotNull(
                Route::getRoutes()->getByName("custom-field-definitions.{$action}"),
                "Route [custom-field-definitions.{$action}] disappeared."
            );
        }

        $this->assertNull(
            Route::getRoutes()->getByName('custom-field-definitions.show'),
            'custom-field-definitions should never gain a show route.'
        );
    }

    #[Test]
    public function the_index_pages_still_render(): void
    {
        // The useful half of the fix: list screens untouched.
        $paths = [
            '/agents', '/branches', '/countries', '/positions', '/skills',
            '/languages', '/status-codes', '/custom-fields',
        ];

        foreach ($paths as $path) {
            $this->actingAs($this->admin)
                ->get($path)
                ->assertOk();
        }
    }
}
