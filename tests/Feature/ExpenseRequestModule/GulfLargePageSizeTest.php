<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af43368 — 'LANDAS: GULF ONLY "Page no"'.
 *
 * Josh (2026-11-11): "GULF only - Remove page from Receivable module, Expense
 * and Payment module. Display all transactions on 1 page."
 *
 * Rendering all ~22.8k Gulf requests in one response exhausts PHP's 256 MB
 * limit, so Gulf pages at 1,000 instead of 50: bigger than a round of 50 that the pager is
 * effectively gone for a filtered view, small enough that the page cannot
 * white-screen. Every other agency keeps 50.
 */
class GulfLargePageSizeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::put('fx_usd_to_php', 56.0, 3600);
    }

    private function tenant(string $subdomain, int $count): array
    {
        $agency = Agency::factory()->create(['subdomain' => $subdomain]);
        $branch = Branch::factory()->create(['agency_id' => $agency->id, 'name' => 'Main']);
        $user   = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'super_admin',
        ]);

        for ($i = 1; $i <= $count; $i++) {
            ExpenseRequest::create([
                'agency_id'    => $agency->id,
                'user_id'      => $user->id,
                'reference_no' => 'PG-' . $i,
                'date'         => now()->toDateString(),
                'status'       => ExpenseRequest::STATUS_PENDING,
                'branch_id'    => $branch->id,
                'notes'        => 'test',
            ]);
        }

        return [$agency, $user];
    }

    #[Test]
    public function gulf_pages_at_one_thousand(): void
    {
        [, $user] = $this->tenant('gulf', 60);

        $response = $this->actingAs($user)
            ->get(route('expense_request.index'))
            ->assertOk();

        $this->assertSame(1000, $response->viewData('requests')->perPage());
    }

    #[Test]
    public function another_agency_still_pages_at_fifty(): void
    {
        [, $user] = $this->tenant('omnis', 60);

        $response = $this->actingAs($user)
            ->get(route('expense_request.index'))
            ->assertOk();

        $this->assertSame(50, $response->viewData('requests')->perPage());
    }

    #[Test]
    public function a_sixty_row_gulf_list_fits_on_one_page(): void
    {
        [, $user] = $this->tenant('gulf', 60);

        $response = $this->actingAs($user)
            ->get(route('expense_request.index'))
            ->assertOk();

        $requests = $response->viewData('requests');
        $this->assertCount(60, $requests);
        $this->assertFalse($requests->hasPages());
    }

    #[Test]
    public function the_gulf_list_still_renders_and_respects_a_status_filter(): void
    {
        [$agency, $user] = $this->tenant('gulf', 40);
        $branch = Branch::where('agency_id', $agency->id)->first();

        ExpenseRequest::create([
            'agency_id'    => $agency->id,
            'user_id'      => $user->id,
            'reference_no' => 'PG-REL',
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_RELEASED,
            'branch_id'    => $branch->id,
            'notes'        => 'test',
        ]);

        $response = $this->actingAs($user)
            ->get(route('expense_request.index', ['status' => 'released']))
            ->assertOk();

        $requests = $response->viewData('requests');
        $this->assertCount(1, $requests);
        $this->assertSame('PG-REL', $requests->first()->reference_no);
    }
}
