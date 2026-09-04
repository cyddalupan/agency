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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Applicant Expense Report (requested via Toybits):
 * a printable page like Generate CV, reachable from a new "Expense Report"
 * button on the applicant show page. Format:
 *   Header: Date Applied, Name - Applicant, Agent, Branch
 *   "Statement of Account": expenses where applicant_id = applicant
 *     Date | Account Type (sub account) | Description (Particulars) | Currency | Charge To | Amount
 *     Total per column + grand total
 *   "Agent Expenses": expenses charged to the applicant's agent
 *     Date | Status | Account Type | Description | Currency | Amount
 *     Total all amounts
 */
class ApplicantExpenseReportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;
    private Agent $agent;
    private Branch $branch;
    private Country $country;
    private Account $officeAccount;
    private Account $agentAccount;

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
            'agency_id'     => $this->agency->id,
            'first_name'    => 'Juan',
            'last_name'     => 'Dela Cruz',
            'branch_id'     => $this->branch->id,
            'agent_id'      => $this->agent->id,
            'created_at'    => '2026-07-01 09:00:00',
        ]);

        $this->country = Country::factory()->create(['name' => 'Saudi Arabia']);

        $this->officeAccount = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->agentAccount = Account::factory()->create([
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
            'account_id'         => $this->officeAccount->id,
            'particular'         => 'Medical',
        ], $item));

        return $request;
    }

    private function renderReport(): string
    {
        // Mirror the controller: build the same view data the report uses.
        $statementItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('applicant_id', $this->applicant->id)
            ->orderBy('id')
            ->get();

        $agentItems = ExpenseRequestItem::with(['expenseRequest', 'account'])
            ->where('agent_id', $this->applicant->agent_id)
            ->orderBy('id')
            ->get();

        $statementTotals = $statementItems->groupBy('currency')->map(fn ($group) => (float) $group->sum('amount'));
        $statementGrandTotal = (float) $statementItems->sum(
            fn ($i) => app(\App\Services\CurrencyConverter::class)->toPhp((float) $i->amount, (string) $i->currency)
        );
        $agentGrandTotal = (float) $agentItems->sum(
            fn ($i) => app(\App\Services\CurrencyConverter::class)->toPhp((float) $i->amount, (string) $i->currency)
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
    public function applicant_show_page_has_expense_report_button(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $this->applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Expense Report', $html);
        $this->assertStringContainsString(route('reports.expense-report', $this->applicant), $html);
    }

    #[Test]
    public function expense_report_endpoint_returns_inline_pdf(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id, 'particular' => 'Medical']);

        $response = $this->actingAs($this->user)
            ->get(route('reports.expense-report', $this->applicant));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('expense-report-' . $this->applicant->id . '.pdf', $response->headers->get('Content-Disposition'));
    }

    #[Test]
    public function expense_report_has_header_and_statement_of_account_section(): void
    {
        $this->makeExpense([
            'applicant_id' => $this->applicant->id,
            'charge'       => 'office',
            'account_id'   => $this->officeAccount->id,
            'particular'   => 'Medical',
            'amount'       => 1500.00,
        ]);

        $html = $this->renderReport();

        // Header
        $this->assertStringContainsString('Date Applied', $html);
        $this->assertStringContainsString('2026-07-01', $html);
        $this->assertStringContainsString('Juan', $html);
        $this->assertStringContainsString('Dela Cruz', $html);
        $this->assertStringContainsString('Atty. Maricon Ramos', $html);
        $this->assertStringContainsString('Manila', $html);

        // Section titles
        $this->assertStringContainsString('Statement of Account', $html);
        $this->assertStringContainsString('Agent Expenses', $html);

        // Statement columns + row data
        $this->assertStringContainsString('Account Type', $html);
        $this->assertStringContainsString('Description', $html);
        $this->assertStringContainsString('Currency', $html);
        $this->assertStringContainsString('Charge To', $html);
        $this->assertStringContainsString('Office Expenses', $html);
        $this->assertStringContainsString('Medical', $html);
        $this->assertStringContainsString('1,500.00', $html);

        // Totals
        $this->assertStringContainsString('TOTAL', $html);
    }

    #[Test]
    public function statement_of_account_shows_charge_to_and_single_converted_total(): void
    {
        $this->makeExpense([
            'applicant_id' => $this->applicant->id,
            'charge'       => 'office',
            'account_id'   => $this->officeAccount->id,
            'particular'   => 'Medical',
            'amount'       => 500.00,
        ]);
        $this->makeExpense([
            'applicant_id' => $this->applicant->id,
            'charge'       => 'office',
            'account_id'   => $this->officeAccount->id,
            'particular'   => 'Processing',
            'amount'       => 300.00,
        ]);
        $this->makeExpense([
            'applicant_id' => $this->applicant->id,
            'charge'       => 'office',
            'account_id'   => $this->officeAccount->id,
            'particular'   => 'Visa',
            'amount'       => 100.00,
            'currency'     => 'USD',
        ]);

        $html = $this->renderReport();

        // Charge To shows the charged party (Office for office-charged items).
        $this->assertStringContainsString('Office', $html);

        // Change #2: no per-currency total rows; the statement shows a single
        // converted TOTAL row instead.
        $this->assertStringNotContainsString('Total - PHP', $html);
        $this->assertStringNotContainsString('Total - USD', $html);
        $this->assertStringNotContainsString('Total - per column', $html);
        $this->assertSame(1, substr_count($html, '>TOTAL<'));
    }

    #[Test]
    public function agent_expenses_section_lists_agent_charges_with_status(): void
    {
        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentAccount->id,
            'particular'    => 'Cash Advance',
            'amount'        => 2000.00,
            'applicant_id'  => null,
        ], status: 'received');

        $html = $this->renderReport();

        $this->assertStringContainsString('Agent Expenses', $html);
        $this->assertStringContainsString('Cash Advance', $html);
        $this->assertStringContainsString('received', $html);
        $this->assertStringContainsString('Agent Advances', $html);
        $this->assertStringContainsString('2,000.00', $html);
    }
}
