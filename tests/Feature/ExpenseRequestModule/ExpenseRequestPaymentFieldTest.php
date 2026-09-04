<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Payments field on the expense-request create form (Toybits 2026-08-29):
 * each line can carry a "Payments" amount (already paid to them); the form
 * shows real-time totals (Total Amount, Total Payments, Net = Amount − Payments).
 */
class ExpenseRequestPaymentFieldTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Branch $branch;
    private Account $agentMain;
    private Account $agentSub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->agentMain = Account::create([
            'agency_id'   => $this->agency->id,
            'name'        => 'AGENT',
            'type'        => 'income',
            'charge_type' => 'agent',
        ]);
        $this->agentSub = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->agentMain->id,
            'name'        => 'Placement Fee',
            'type'        => 'income',
            'charge_type' => 'agent',
        ]);
    }

    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'date'      => now()->toDateString(),
            'branch_id' => $this->branch->id,
            'lines'     => [[
                'charge'          => 'agent',
                'currency'        => 'PHP',
                'amount'          => 10000.00,
                'payment'         => 4000.00,
                'sub_account_id'  => $this->agentSub->id,
                'particular'      => 'Placement fee less advance',
            ]],
        ], $overrides);
    }

    #[Test]
    public function create_page_has_the_payments_input_next_to_particular(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Payments', $html);
        $this->assertStringContainsString('lines[0][payment]', $html);
        $this->assertStringContainsString('Particular', $html);
    }

    #[Test]
    public function create_page_has_real_time_totals_calculator(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Total Amount', $html);
        $this->assertStringContainsString('Total Payments', $html);
        $this->assertStringContainsString('Net (Amount − Payments)', $html);
        $this->assertStringContainsString('totalAmount', $html);
        $this->assertStringContainsString('totalPayment', $html);
        $this->assertStringContainsString('totalNet', $html);
    }

    #[Test]
    public function store_saves_the_payment_amount(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->storePayload())
            ->assertRedirect();

        $this->assertDatabaseHas('expense_request_items', [
            'amount'  => 10000.00,
            'payment' => 4000.00,
        ]);
    }

    #[Test]
    public function store_defaults_payment_to_zero_when_omitted(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->storePayload([
                'lines' => [[
                    'charge'         => 'agent',
                    'currency'       => 'PHP',
                    'amount'         => 5000.00,
                    'sub_account_id' => $this->agentSub->id,
                ]],
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('expense_request_items', [
            'amount'  => 5000.00,
            'payment' => 0,
        ]);
    }

    #[Test]
    public function store_rejects_negative_payment(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->storePayload([
                'lines' => [[
                    'charge'         => 'agent',
                    'currency'       => 'PHP',
                    'amount'         => 5000.00,
                    'payment'        => -100,
                    'sub_account_id' => $this->agentSub->id,
                ]],
            ]))
            ->assertSessionHasErrors('lines.0.payment');

        $this->assertDatabaseCount('expense_request_items', 0);
    }
}
