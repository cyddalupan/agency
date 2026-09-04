<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-08-18 — expense-request create page:
 * 1. Branch + Account Type dropdowns default to "- select -" (no more
 *    "— (all branches) —" / "— (auto from Charge) —").
 * 2. Charge, Account Type and Amount are required — the form cannot be
 *    saved without them (server-side validation + client-side required).
 */
class ExpenseRequestRequiredFieldsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function mainAndSub(string $charge = 'office'): array
    {
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => $charge === 'office' ? 'Office Expenses' : 'Agent Accounts',
            'type'        => 'expense',
            'charge_type' => $charge,
        ]);

        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => $charge === 'office' ? 'Salaries' : 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => $charge,
        ]);

        return [$main, $sub];
    }

    // ---------- Dropdown default labels ----------

    #[Test]
    public function create_page_branch_dropdown_defaults_to_select_placeholder(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.create'));

        $response->assertOk();
        $response->assertSee('- select -', false);
        $response->assertDontSee('— (all branches) —');
    }

    #[Test]
    public function create_page_account_type_dropdown_defaults_to_select_placeholder(): void
    {
        $this->mainAndSub('office');

        $response = $this->actingAs($this->user)->get(route('expense_request.create'));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('<option value="">- select -</option>', $html);
        $this->assertStringNotContainsString('— (auto from Charge) —', $html);
    }

    #[Test]
    public function create_page_marks_charge_account_type_and_amount_as_required(): void
    {
        $this->mainAndSub('office');

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        // Charge select
        $this->assertMatchesRegularExpression('/name="lines\[0\]\[charge\]"[^>]*required/', $html);
        // Account Type select
        $this->assertMatchesRegularExpression('/name="lines\[0\]\[sub_account_id\]"[^>]*required/', $html);
        // Amount input
        $this->assertMatchesRegularExpression('/name="lines\[0\]\[amount\]"[^>]*required/', $html);
    }

    // ---------- Server-side required validation ----------

    #[Test]
    public function store_rejects_a_line_without_charge(): void
    {
        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->linePayload(['charge' => null]))
            ->assertSessionHasErrors('lines.0.charge');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_rejects_a_line_without_account_type(): void
    {
        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->linePayload(['sub_account_id' => null]))
            ->assertSessionHasErrors('lines.0.sub_account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_rejects_a_line_without_amount(): void
    {
        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->linePayload(['amount' => null]))
            ->assertSessionHasErrors('lines.0.amount');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_accepts_a_line_with_all_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->linePayload())
            ->assertRedirect();

        $this->assertDatabaseCount('expense_requests', 1);
    }

    // ---------- Helpers ----------

    private function linePayload(array $overrides = []): array
    {
        [, $sub] = $this->mainAndSub('office');

        return array_merge([
            'branch_id' => Branch::factory()->create(['agency_id' => $this->agency->id])->id,
            'lines'     => [
                array_merge([
                    'charge'         => 'office',
                    'sub_account_id' => $sub->id,
                    'agent_id'       => null,
                    'applicant_id'   => null,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 1000.00,
                    'particular'     => 'Office supplies',
                ], $overrides),
            ],
        ]);
    }
}
