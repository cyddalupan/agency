<?php

namespace Tests\Feature\Applicant;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-08-16 — 5 expense-report changes:
 *  1. Header labels get colons (Date Applied:, Name - Applicant:, ...)
 *  2. Statement of Account: no per-currency "Total - PHP/USD" rows; the total
 *     is converted by currency (USD→PHP via free open.er-api.com, fallback 1.0)
 *  3. Statement columns: Charge To before Currency
 *  4. "Total - Total of all column" → "Total"; "Total - All amounts" → "Total"
 *  5. Account Type shows the sub-account value
 */
class ApplicantExpenseReportChangesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    private Applicant $applicant;

    private Agent $agent;

    private Branch $branch;

    private Country $country;

    private Account $officeMain;

    private Account $officeSub;

    private Account $agentMain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'Atty. Maricon Ramos',
        ]);

        $this->applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'branch_id'  => $this->branch->id,
            'agent_id'   => $this->agent->id,
            'created_at' => '2026-07-01 09:00:00',
        ]);

        $this->country = Country::factory()->create(['name' => 'Saudi Arabia']);

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

        app()->instance('tenant_agency', $this->agency);
    }

    private function makeExpense(array $item, string $status = 'pending', string $date = '2026-07-10'): ExpenseRequest
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => (string) random_int(2000, 99999),
            'date'         => $date,
            'status'       => $status,
        ]);

        ExpenseRequestItem::create(array_merge([
            'expense_request_id' => $request->id,
            'charge'             => 'office',
            'country_id'         => $this->country->id,
            'currency'           => 'PHP',
            'amount'             => 1000.00,
            'account_id'         => $this->officeMain->id,
            'particular'         => 'Medical',
        ], $item));

        return $request;
    }

    private function renderReport(): string
    {
        $statementItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('applicant_id', $this->applicant->id)
            ->orderBy('id')
            ->get();

        $agentItems = ExpenseRequestItem::with(['expenseRequest', 'account'])
            ->where('agent_id', $this->applicant->agent_id)
            ->orderBy('id')
            ->get();

        $converter = app(\App\Services\CurrencyConverter::class);
        $statementTotals = $statementItems->groupBy('currency')->map(fn ($group) => (float) $group->sum('amount'));
        $statementGrandTotal = (float) $statementItems->sum(
            fn ($i) => $converter->toPhp((float) $i->amount, (string) $i->currency)
        );
        $agentGrandTotal = (float) $agentItems->sum(
            fn ($i) => $converter->toPhp((float) $i->amount, (string) $i->currency)
        );

        return view('reports.applicant_expense_report', [
            'applicant'           => $this->applicant->load('agent', 'branch'),
            'statementItems'      => $statementItems,
            'statementTotals'     => $statementTotals,
            'statementGrandTotal' => $statementGrandTotal,
            'agentItems'          => $agentItems,
            'agentGrandTotal'     => $agentGrandTotal,
        ])->render();
    }

    #[Test]
    public function header_labels_have_colons(): void
    {
        $html = $this->renderReport();

        $this->assertStringContainsString('Date Applied:', $html);
        $this->assertStringContainsString('Name - Applicant:', $html);
        $this->assertStringContainsString('Agent:', $html);
        $this->assertStringContainsString('Branch:', $html);
    }

    #[Test]
    public function statement_of_account_has_no_per_currency_total_rows(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id, 'amount' => 500.00]);
        $this->makeExpense(['applicant_id' => $this->applicant->id, 'amount' => 300.00]);
        $this->makeExpense(['applicant_id' => $this->applicant->id, 'amount' => 100.00, 'currency' => 'USD']);

        $html = $this->renderReport();

        $this->assertStringNotContainsString('Total - PHP', $html);
        $this->assertStringNotContainsString('Total - USD', $html);
        $this->assertStringNotContainsString('Total - per column', $html);
    }

    #[Test]
    public function statement_of_account_total_is_converted_to_php(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates'  => ['PHP' => 60.0],
            ]),
        ]);

        $this->makeExpense(['applicant_id' => $this->applicant->id, 'amount' => 800.00]);
        $this->makeExpense(['applicant_id' => $this->applicant->id, 'amount' => 100.00, 'currency' => 'USD']);

        $html = $this->renderReport();

        // 800 PHP + (100 USD × 60) = 6,800.00 PHP — the single converted total
        $this->assertStringContainsString('6,800.00', $html);
    }

    #[Test]
    public function statement_of_account_charge_to_column_comes_before_currency(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id]);

        $html = $this->renderReport();

        $statement = substr($html, strpos($html, 'Statement of Account'), strpos($html, 'Agent Expenses') - strpos($html, 'Statement of Account'));
        $chargeToPos = strpos($statement, 'Charge To');
        $currencyPos = strpos($statement, 'Currency');

        $this->assertNotFalse($chargeToPos, 'Statement must contain a Charge To column');
        $this->assertNotFalse($currencyPos, 'Statement must contain a Currency column');
        $this->assertLessThan($currencyPos, $chargeToPos, 'Charge To must come before Currency');
    }

    #[Test]
    public function statement_grand_total_label_is_plain_total(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id]);

        $html = $this->renderReport();

        $this->assertStringNotContainsString('Total - Total of all column', $html);
    }

    #[Test]
    public function agent_expenses_total_label_is_plain_total(): void
    {
        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => null,
            'amount'        => 2000.00,
        ]);

        $html = $this->renderReport();

        $this->assertStringNotContainsString('Total - All amounts', $html);
        $this->assertStringContainsString('>TOTAL<', $html);
    }

    #[Test]
    public function account_type_shows_sub_account_value(): void
    {
        $this->makeExpense([
            'applicant_id' => $this->applicant->id,
            'account_id'   => $this->officeSub->id,
            'particular'   => 'Payroll',
        ]);

        $html = $this->renderReport();

        // Sub-account name (Salaries) must appear in the Account Type column
        $this->assertStringContainsString('Salaries', $html);
    }
}
