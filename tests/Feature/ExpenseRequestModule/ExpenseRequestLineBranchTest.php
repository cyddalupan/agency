<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * CYD 2026-11-04 — "Expense and Payment (Multiple Transaction)":
 * Main Office / Main Branch users can tag each expense LINE with its own Branch.
 * Branch-locked users never see the field (their request branch is fixed).
 */
class ExpenseRequestLineBranchTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $admin;

    private Branch $mainOffice;

    private Branch $davao;

    private Account $officeMain;

    private Account $officeSub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => null,
        ]);

        $this->mainOffice = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Main Office',
        ]);
        $this->davao = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Davao Branch',
        ]);

        $this->officeMain = Account::create([
            'agency_id' => $this->agency->id,
            'name' => 'Office Expenses',
            'type' => 'expense',
            'charge_type' => 'office',
        ]);
        $this->officeSub = Account::create([
            'agency_id' => $this->agency->id,
            'parent_id' => $this->officeMain->id,
            'name' => 'Salaries',
            'type' => 'expense',
            'charge_type' => 'office',
        ]);
    }

    private function payload(array $lineOverrides = [], array $overrides = []): array
    {
        return array_merge([
            'date' => now()->toDateString(),
            'branch_id' => $this->mainOffice->id,
            'lines' => [array_merge([
                'charge' => 'office',
                'currency' => 'PHP',
                'amount' => 1500.00,
                'sub_account_id' => $this->officeSub->id,
            ], $lineOverrides)],
        ], $overrides);
    }

    #[Test]
    public function main_office_user_sees_a_branch_field_on_every_line(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('lines[0][branch_id]', $html);
    }

    #[Test]
    public function branch_locked_user_does_not_see_the_line_branch_field(): void
    {
        $branchUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'branch_id' => $this->davao->id,
        ]);

        $html = $this->actingAs($branchUser)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('lines[0][branch_id]', $html);
    }

    #[Test]
    public function main_office_user_is_not_branch_locked_even_with_a_main_office_branch(): void
    {
        $headOfficeUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'branch_id' => $this->mainOffice->id,
        ]);

        $html = $this->actingAs($headOfficeUser)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('lines[0][branch_id]', $html);
    }

    #[Test]
    public function store_saves_the_per_line_branch(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload(['branch_id' => $this->davao->id]))
            ->assertRedirect();

        $request = ExpenseRequest::query()->latest('id')->firstOrFail();

        $this->assertSame($this->mainOffice->id, (int) $request->branch_id);
        $this->assertDatabaseHas('expense_request_items', [
            'expense_request_id' => $request->id,
            'branch_id' => $this->davao->id,
        ]);
    }

    #[Test]
    public function store_defaults_the_line_branch_to_the_request_branch(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload())
            ->assertRedirect();

        $request = ExpenseRequest::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('expense_request_items', [
            'expense_request_id' => $request->id,
            'branch_id' => $this->mainOffice->id,
        ]);
    }

    #[Test]
    public function store_rejects_a_branch_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $foreignBranch = Branch::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload(['branch_id' => $foreignBranch->id]))
            ->assertSessionHasErrors('lines.0.branch_id');

        $this->assertDatabaseCount('expense_request_items', 0);
    }

    #[Test]
    public function edit_page_preselects_the_saved_line_branch(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload(['branch_id' => $this->davao->id]))
            ->assertRedirect();

        $request = ExpenseRequest::query()->latest('id')->firstOrFail();

        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.edit', $request->id))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<option value="' . $this->davao->id . '"\s+selected>/',
            $html
        );
    }
}
