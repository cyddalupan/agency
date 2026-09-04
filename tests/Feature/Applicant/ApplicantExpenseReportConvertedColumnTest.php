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
 * Toybits 2026-08-16 — expense report column upgrades (both sections):
 *  1. New "Converted" column — every amount converted to PHP
 *  2. Bottom total label is "TOTAL", right-aligned, and equals the converted sum
 *  3. All columns smaller; Description wider
 */
class ApplicantExpenseReportConvertedColumnTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    private Applicant $applicant;

    private Agent $agent;

    private Branch $branch;

    private Country $country;

    private Account $officeMain;

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
        // Mirror the controller: build the same view data the report uses.
        $statementItems = ExpenseRequestItem::with(['expenseRequest', 'account', 'agent'])
            ->where('applicant_id', $this->applicant->id)
            ->orderBy('id')
            ->get();

        $agentItems = ExpenseRequestItem::with(['expenseRequest', 'account'])
            ->where('agent_id', $this->applicant->agent_id)
            ->where('applicant_id', $this->applicant->id)
            ->orderBy('id')
            ->get();

        $converter = app(\App\Services\CurrencyConverter::class);
        $statementGrandTotal = (float) $statementItems->sum(
            fn ($i) => $converter->toPhp((float) $i->amount, (string) $i->currency)
        );
        $agentGrandTotal = (float) $agentItems->sum(
            fn ($i) => $converter->toPhp((float) $i->amount, (string) $i->currency)
        );

        return view('reports.applicant_expense_report', [
            'applicant'           => $this->applicant->load('agent', 'branch'),
            'statementItems'      => $statementItems,
            'statementGrandTotal' => $statementGrandTotal,
            'agentItems'          => $agentItems,
            'agentGrandTotal'     => $agentGrandTotal,
        ])->render();
    }

    #[Test]
    public function agent_expenses_only_include_items_of_the_selected_applicant(): void
    {
        // Second applicant under the SAME agent with their own expense.
        $otherApplicant = \App\Models\Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $this->applicant->agent_id,
        ]);
        $this->makeExpense([
            'applicant_id' => $otherApplicant->id,
            'agent_id'     => $this->applicant->agent_id,
            'charge'       => 'agent',
            'account_id'   => $this->agentMain->id,
            'amount'       => 999.00,
            'particular'   => 'Other applicant expense',
        ]);

        $html = $this->renderReport();

        $agent = substr($html, strpos($html, 'Agent Expenses'));
        $this->assertStringNotContainsString('Other applicant expense', $agent);
    }

    #[Test]
    public function agent_expenses_include_items_of_the_selected_applicant(): void
    {
        $this->makeExpense([
            'applicant_id' => $this->applicant->id,
            'agent_id'     => $this->applicant->agent_id,
            'charge'       => 'agent',
            'account_id'   => $this->agentMain->id,
            'amount'       => 200.00,
            'particular'   => 'Selected applicant expense',
        ]);

        $html = $this->renderReport();

        $agent = substr($html, strpos($html, 'Agent Expenses'));
        $this->assertStringContainsString('Selected applicant expense', $agent);
    }

    #[Test]
    public function statement_table_has_a_converted_column_header(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id]);

        $html = $this->renderReport();

        $statement = substr($html, strpos($html, 'Statement of Account'), strpos($html, 'Agent Expenses') - strpos($html, 'Statement of Account'));
        $this->assertStringContainsString('>Converted<', $statement);
    }

    #[Test]
    public function agent_expenses_table_has_a_converted_column_header(): void
    {
        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => $this->applicant->id,
        ]);

        $html = $this->renderReport();

        $agent = substr($html, strpos($html, 'Agent Expenses'));
        $this->assertStringContainsString('>Converted<', $agent);
    }

    #[Test]
    public function statement_rows_show_each_amount_converted_to_php(): void
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

        // PHP row stays 800.00; USD row converts 100 × 60 = 6,000.00
        $this->assertStringContainsString('6,000.00', $html);
        $this->assertStringContainsString('800.00', $html);
    }

    #[Test]
    public function agent_expenses_rows_show_each_amount_converted_to_php(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates'  => ['PHP' => 60.0],
            ]),
        ]);

        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => $this->applicant->id,
            'amount'        => 200.00,
            'currency'      => 'USD',
        ]);

        $html = $this->renderReport();

        // 200 USD × 60 = 12,000.00 in the Converted column
        $this->assertStringContainsString('12,000.00', $html);
    }

    #[Test]
    public function amount_and_converted_headers_are_centered_in_both_tables(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id]);

        $html = $this->renderReport();

        $statement = substr($html, strpos($html, 'Statement of Account'), strpos($html, 'Agent Expenses') - strpos($html, 'Statement of Account'));
        $agent = substr($html, strpos($html, 'Agent Expenses'));

        // Amount + Converted header cells are centered (text-align: center).
        foreach ([$statement, $agent] as $table) {
            $this->assertMatchesRegularExpression('/<th[^>]*text-align:\s*center[^>]*>Amount<\/th>/', $table);
            $this->assertMatchesRegularExpression('/<th[^>]*text-align:\s*center[^>]*>Converted<\/th>/', $table);
        }
    }

    #[Test]
    public function both_totals_are_labeled_total_and_right_aligned(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates'  => ['PHP' => 60.0],
            ]),
        ]);

        $this->makeExpense(['applicant_id' => $this->applicant->id, 'amount' => 500.00]);
        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => $this->applicant->id,
            'amount'        => 300.00,
        ]);

        $html = $this->renderReport();

        // Exactly two TOTAL labels (one per section), each right-aligned.
        $this->assertSame(2, substr_count($html, '>TOTAL<'));
        $this->assertMatchesRegularExpression('/<td[^>]*class="num"[^>]*>TOTAL<\/td>/', $html);
    }

    #[Test]
    public function agent_total_is_the_converted_sum(): void
    {
        Http::fake([
            'open.er-api.com/*' => Http::response([
                'result' => 'success',
                'rates'  => ['PHP' => 60.0],
            ]),
        ]);

        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => $this->applicant->id,
            'amount'        => 200.00,
            'currency'      => 'USD',
        ]);
        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => $this->applicant->id,
            'amount'        => 500.00,
            'currency'      => 'PHP',
        ]);

        $html = $this->renderReport();

        // 200 USD × 60 + 500 PHP = 12,500.00
        $this->assertStringContainsString('12,500.00', $html);
    }

    #[Test]
    public function description_column_is_wider_than_other_columns(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id]);
        $this->makeExpense([
            'agent_id'      => $this->agent->id,
            'charge'        => 'agent',
            'account_id'    => $this->agentMain->id,
            'applicant_id'  => $this->applicant->id,
        ]);

        $html = $this->renderReport();

        // dompdf does not honour <colgroup>/<col> widths, so each section table
        // must declare the widths on its <th> cells instead. Description sits at
        // index 2 in the Statement table and index 3 in the Agent Expenses table
        // (Date | Status | Account Type | Description ...) and must be strictly
        // wider than every other column in its table.
        preg_match_all('/<table class="data">(.*?)<\/table>/s', $html, $tables);
        $this->assertCount(2, $tables[1], 'Both section tables must exist');

        foreach ($tables[1] as $i => $table) {
            preg_match_all('/<th[^>]*width:\s*([\d.]+)%[^>]*>/', $table, $w);
            $widths = array_map('floatval', $w[1]);
            $this->assertCount(7, $widths, 'Each table has 7 column headers with widths');

            $descriptionIndex = $i === 0 ? 2 : 3;
            $others = array_merge(
                array_slice($widths, 0, $descriptionIndex),
                array_slice($widths, $descriptionIndex + 1)
            );
            $this->assertGreaterThan(
                max($others),
                $widths[$descriptionIndex],
                'Description column must be wider than every other column'
            );
            $this->assertGreaterThanOrEqual(
                40.0,
                $widths[$descriptionIndex],
                'Description column must get the bulk of the row (>= 40%)'
            );
        }
    }

    #[Test]
    public function data_table_font_is_slightly_smaller_for_more_room(): void
    {
        $this->makeExpense(['applicant_id' => $this->applicant->id]);

        $html = $this->renderReport();

        $this->assertMatchesRegularExpression('/table\.data \{[^}]*font-size:\s*8pt/', $html);
    }
}
