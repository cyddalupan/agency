<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-08-16 — #5: restore the sub-account picker on the
 * expense-request create page. The MAIN account is no longer user-chosen:
 * it is auto-derived from the Charge (office → office main, agent → agent
 * main). The picker lists that main's sub-accounts (children), and the item
 * stores the sub-account id so the report's Account Type shows the sub value.
 */
class ExpenseRequestSubAccountRestoredTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    private Branch $branch;

    private Account $officeMain;

    private Account $officeSub;

    private Account $agentMain;

    private Account $agentSub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->officeMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->officeSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->officeMain->id,
            'name'        => 'Salaries',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->agentMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);
        $this->agentSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->agentMain->id,
            'name'        => 'Partial',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);
    }

    #[Test]
    public function create_page_has_sub_account_picker_gated_by_charge(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.create'));

        $response->assertOk();
        $response->assertSee('Account Type');

        $html = $response->getContent();

        // Sub-account options carry their charge offset so the picker can be
        // gated by Charge (office/agent).
        $this->assertStringContainsString('lines[0][sub_account_id]', $html);
        $this->assertStringContainsString('data-offset="office"', $html);
        $this->assertStringContainsString('data-offset="agent"', $html);

        // Sub-account names (children of mains) are listed.
        $this->assertStringContainsString('Salaries', $html);
        $this->assertStringContainsString('Partial', $html);

        // The user-facing Main Account picker stays gone — main comes from Charge.
        $this->assertStringNotContainsString('main_account_id', $html);
    }

    #[Test]
    public function store_persists_selected_sub_account(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'notes'     => 'Sub account test',
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $this->officeSub->id,
                    'agent_id'       => null,
                    'applicant_id'   => null,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 250.00,
                    'particular'     => 'Payroll',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $item = ExpenseRequestItem::first();
        $this->assertNotNull($item);
        $this->assertSame($this->officeSub->id, (int) $item->account_id);
    }

    #[Test]
    public function store_rejects_sub_account_that_does_not_match_charge(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $this->agentSub->id, // agent sub under an office charge
                    'agent_id'       => null,
                    'applicant_id'   => null,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 250.00,
                    'particular'     => 'Bad',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.sub_account_id');

        $this->assertSame(0, ExpenseRequestItem::count());
    }

    #[Test]
    public function store_rejects_a_line_when_sub_account_is_omitted(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'         => 'agent',
                    'agent_id'       => null,
                    'applicant_id'   => null,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 150.00,
                    'particular'     => 'Advance',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.sub_account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_still_accepts_explicit_main_account_id_for_backwards_compat(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'          => 'office',
                    'sub_account_id'  => $this->officeSub->id,
                    'main_account_id' => $this->officeMain->id,
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => null,
                    'currency'        => 'PHP',
                    'amount'          => 100.00,
                    'particular'      => 'Legacy',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $item = ExpenseRequestItem::first();
        $this->assertNotNull($item);
        $this->assertSame($this->officeSub->id, (int) $item->account_id);
    }
}
