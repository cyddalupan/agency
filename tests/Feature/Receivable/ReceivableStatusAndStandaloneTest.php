<?php

namespace Tests\Feature\Receivable;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af8296b: LANDAS Receivable Update.
 *
 * - Add status "Cancelled"
 * - Tabs for Pending / Received / Cancelled
 * - New Receivable may omit Agent and Applicant for Office Returns,
 *   Monthly Collection and Other Collection.
 */
class ReceivableStatusAndStandaloneTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agency = Agency::factory()->create(['subdomain' => 'landas', 'name' => 'LANDAS']);
        app()->instance('tenant_agency', $this->agency);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    private function receivable(string $status, float $amount = 100, ?string $account = null): Receivable
    {
        return Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin()->id,
            'code' => 'R' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'date' => now()->toDateString(),
            'status' => $status,
            'amount' => $amount,
            'account' => $account,
        ]);
    }

    public function test_cancelled_is_a_valid_status(): void
    {
        $this->assertContains(Receivable::STATUS_CANCELLED, Receivable::STATUSES);
        $this->assertSame('Cancelled', Receivable::statusLabels()[Receivable::STATUS_CANCELLED]);
    }

    public function test_an_admin_can_move_a_receivable_to_cancelled(): void
    {
        $r = $this->receivable(Receivable::STATUS_PENDING);

        $this->actingAs($this->admin())
            ->patch(route('receivable.status', $r), ['status' => Receivable::STATUS_CANCELLED])
            ->assertRedirect();

        $this->assertSame(Receivable::STATUS_CANCELLED, $r->fresh()->status);
    }

    public function test_the_index_shows_the_three_status_tabs(): void
    {
        $this->receivable(Receivable::STATUS_PENDING);
        $this->receivable(Receivable::STATUS_RECEIVED);
        $this->receivable(Receivable::STATUS_CANCELLED);

        $html = $this->actingAs($this->admin())
            ->get(route('receivable.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Pending', $html);
        $this->assertStringContainsString('Received', $html);
        $this->assertStringContainsString('Cancelled', $html);
    }

    public function test_each_tab_filters_to_its_own_status(): void
    {
        $this->receivable(Receivable::STATUS_PENDING, 111);
        $this->receivable(Receivable::STATUS_RECEIVED, 222);
        $this->receivable(Receivable::STATUS_CANCELLED, 333);

        $admin = $this->admin();

        $cancelled = $this->actingAs($admin)
            ->get(route('receivable.index', ['status' => Receivable::STATUS_CANCELLED]))
            ->assertOk();
        $cancelledRows = $cancelled->viewData('receivables');
        $this->assertCount(1, $cancelledRows);
        $this->assertSame(Receivable::STATUS_CANCELLED, $cancelledRows->first()->status);

        $pending = $this->actingAs($admin)
            ->get(route('receivable.index', ['status' => Receivable::STATUS_PENDING]))
            ->assertOk();
        $this->assertCount(1, $pending->viewData('receivables'));
        $this->assertSame(Receivable::STATUS_PENDING, $pending->viewData('receivables')->first()->status);
    }

    public function test_the_tab_counts_match_each_status(): void
    {
        $this->receivable(Receivable::STATUS_PENDING);
        $this->receivable(Receivable::STATUS_PENDING);
        $this->receivable(Receivable::STATUS_RECEIVED);
        $this->receivable(Receivable::STATUS_CANCELLED);

        $response = $this->actingAs($this->admin())->get(route('receivable.index'))->assertOk();
        $counts = $response->viewData('statusCounts');

        $this->assertSame(4, $counts['all']);
        $this->assertSame(2, $counts[Receivable::STATUS_PENDING]);
        $this->assertSame(1, $counts[Receivable::STATUS_RECEIVED]);
        $this->assertSame(1, $counts[Receivable::STATUS_CANCELLED]);
    }

    public function test_a_new_receivable_can_be_filed_without_an_agent_for_office_returns(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('receivable.store'), [
                'date' => now()->toDateString(),
                'account' => 'Office Returns',
                'amount' => 2500,
                'particular' => 'Cash returned to office',
            ]);

        $response->assertRedirect();

        $r = Receivable::where('account', 'Office Returns')->first();
        $this->assertNotNull($r);
        $this->assertNull($r->agent_id);
        $this->assertNull($r->applicant_id);
        $this->assertSame(Receivable::STATUS_PENDING, $r->status);
    }

    public function test_a_new_receivable_still_requires_an_agent_for_other_accounts(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('receivable.store'), [
                'date' => now()->toDateString(),
                'account' => 'Placement Fee',
                'amount' => 2500,
            ]);

        $response->assertSessionHasErrors('agent_id');
        $this->assertSame(0, Receivable::where('account', 'Placement Fee')->count());
    }

    public function test_monthly_and_other_collection_are_also_standalone(): void
    {
        $this->assertTrue(Receivable::allowsStandalone('Monthly Collection'));
        $this->assertTrue(Receivable::allowsStandalone('Other Collection'));
        $this->assertFalse(Receivable::allowsStandalone('Placement Fee'));
        $this->assertFalse(Receivable::allowsStandalone(null));
    }

    public function test_an_agent_is_still_accepted_for_a_standalone_account(): void
    {
        $agent = Agent::create([
            'agency_id' => $this->agency->id,
            'name' => 'Agent Smith',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin())
            ->post(route('receivable.store'), [
                'date' => now()->toDateString(),
                'account' => 'Other Collection',
                'agent_id' => $agent->id,
                'amount' => 900,
            ])
            ->assertRedirect();

        $r = Receivable::where('account', 'Other Collection')->first();
        $this->assertSame($agent->id, $r->agent_id);
    }

    public function test_cancelled_amounts_are_excluded_from_the_headline_total(): void
    {
        $this->receivable(Receivable::STATUS_RECEIVED, 1000);
        $this->receivable(Receivable::STATUS_CANCELLED, 5000);

        $response = $this->actingAs($this->admin())->get(route('receivable.index'))->assertOk();

        $this->assertEquals(1000, $response->viewData('totalAmount'));
        $this->assertEquals(1000, $response->viewData('allAmount'));
        $this->assertEquals(5000, $response->viewData('cancelledTotal'));
    }
}
