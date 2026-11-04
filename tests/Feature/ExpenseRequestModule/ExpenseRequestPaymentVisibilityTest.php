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
 * Toybits 2026-11-04 — Tab 2 "Payments" field is only displayed when the line is
 * charged to an Agent account of type Partial / Full / Fit to Work / Contract / Deployed.
 * Office charges and the other agent accounts (Return, Cash advance) hide it.
 */
class ExpenseRequestPaymentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $admin;

    private Branch $branch;

    private Account $officeMain;

    private Account $officeSub;

    private Account $agentMain;

    /** @var array<string, Account> */
    private array $agentSubs = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

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

        $this->agentMain = Account::create([
            'agency_id' => $this->agency->id,
            'name' => 'AGENT',
            'type' => 'expense',
            'charge_type' => 'agent',
        ]);
        foreach (['Partial', 'Full', 'Fit to Work', 'Contract', 'Deployed', 'Return', 'Cash advance'] as $name) {
            $this->agentSubs[$name] = Account::create([
                'agency_id' => $this->agency->id,
                'parent_id' => $this->agentMain->id,
                'name' => $name,
                'type' => 'expense',
                'charge_type' => 'agent',
            ]);
        }
    }

    private function storeLine(array $line): ExpenseRequest
    {
        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), [
                'date' => now()->toDateString(),
                'branch_id' => $this->branch->id,
                'lines' => [array_merge([
                    'charge' => 'office',
                    'currency' => 'PHP',
                    'amount' => 1000.00,
                ], $line)],
            ])
            ->assertRedirect();

        return ExpenseRequest::query()->latest('id')->firstOrFail();
    }

    private function editHtml(ExpenseRequest $record): string
    {
        return $this->actingAs($this->admin)
            ->get(route('expense_request.edit', $record->id))
            ->assertOk()
            ->getContent();
    }

    /** True when the Payments wrapper is rendered visible (no inline display:none). */
    private function paymentFieldVisible(string $html): bool
    {
        return (bool) preg_match('/class="form-control payment-field"(?![^>]*display:\s*none)/', $html);
    }

    #[Test]
    public function create_page_renders_the_payments_field_hidden_by_default(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('lines[0][payment]', $html);
        $this->assertStringContainsString('payment-field', $html);
        $this->assertFalse($this->paymentFieldVisible($html));
    }

    #[Test]
    public function create_page_script_gates_payments_on_agent_partial_full_fit_to_work_contract_deployed(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('PAYMENT_ACCOUNTS', $html);
        $this->assertStringContainsString("'partial', 'full', 'fit to work', 'contract', 'deployed'", $html);
        $this->assertStringContainsString('updatePaymentField', $html);
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function accountCases(): array
    {
        return [
            'Partial' => ['Partial', true],
            'Full' => ['Full', true],
            'Fit to Work' => ['Fit to Work', true],
            'Contract' => ['Contract', true],
            'Deployed' => ['Deployed', true],
            'Return' => ['Return', false],
            'Cash advance' => ['Cash advance', false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('accountCases')]
    #[Test]
    public function edit_page_shows_payments_only_for_the_allowed_agent_accounts(string $accountName, bool $expected): void
    {
        $record = $this->storeLine([
            'charge' => 'agent',
            'sub_account_id' => $this->agentSubs[$accountName]->id,
        ]);

        $this->assertSame($expected, $this->paymentFieldVisible($this->editHtml($record)), $accountName);
    }

    #[Test]
    public function edit_page_hides_payments_for_an_office_charge(): void
    {
        $record = $this->storeLine([
            'charge' => 'office',
            'sub_account_id' => $this->officeSub->id,
        ]);

        $this->assertFalse($this->paymentFieldVisible($this->editHtml($record)));
    }

    #[Test]
    public function stored_payment_is_preserved_on_an_allowed_agent_line(): void
    {
        $record = $this->storeLine([
            'charge' => 'agent',
            'sub_account_id' => $this->agentSubs['Partial']->id,
            'payment' => 250.00,
        ]);

        $this->assertMatchesRegularExpression('/payment-input[^>]*value="250/', $this->editHtml($record));
    }
}
