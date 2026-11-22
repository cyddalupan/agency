<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b0311ba24f888882ebcca5f — the ten remaining Particular/Description
 * cells were still `max-w-[Xrem] truncate` after the deductions tab was fixed
 * in 3a4b8bfb. A truncated particular silently hides text from management; the
 * native title tooltip does not exist on touch.
 *
 * Each in-scope cell must carry `max-w-[26rem] whitespace-normal break-words
 * align-top` and must NOT carry `truncate` / `text-ellipsis` /
 * `overflow-hidden`. The assertion is on the rendered <td> classes, because
 * Blade still emits the whole string into the HTML even when CSS clips it —
 * a text-content assertion alone cannot catch this.
 *
 * The particulars below are 140+ characters each (the longest real one is 145).
 */
class AgentReportParticularWrapTest extends TestCase
{
    use RefreshDatabase;

    /** 141 chars. */
    private const WRAP_CASH_ADVANCE =
        'CASH ADVANCE RELEASED FOR PROCESSING OF SAUDI WORK VISA AND MEDICAL EXAMINATION INCLUDING ALL DOCUMENTATION FEES AND AGENCY CHARGES AND AIRFARE';

    /** 143 chars. */
    private const WRAP_COMMISSION =
        'COMMISSION RELEASE FOR PARTIAL PAYMENT OF APPLICANT UNDER KRISTILE ANNJOY DE SESTO ELIGIBLE FOR THE PARTIAL RELEASE AFTER MEDICAL CLEARANCE AND DEPLOYMENT';

    /** 145 chars — the real long row pattern from agent_deductions.id = 1255. */
    private const WRAP_PARTICULAR =
        'KRISTILE ANNJOY DE SESTO ELITE REPAT | 08-11-2025 TO 20-03-2026 | TOOK MORE THAN 3 MONTHS BEFORE GOING BACK TO PHILIPPINES DUE TO MIDDLE EAST WAR';

    /** 144 chars. */
    private const WRAP_RECEIVABLE =
        'PLACEMENT FEE COLLECTED FROM APPLICANT FOR THE ELITE REPAT PROGRAM WITH ADDITIONAL PROCESSING COSTS FOR DOCUMENTS AND MEDICAL REQUIREMENTS AND ALLIED FEES';

    /** 142 chars. */
    private const WRAP_STARTING =
        'OPENING BALANCE CARRIED FORWARD FROM THE PREVIOUS ACCOUNTING PERIOD REPRESENTING UNLIQUIDATED CASH ADVANCES AND UNPAID STATE CHARGES AND FEES';

    private Agency $agency;
    private Branch $branch;
    private User $admin;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->admin  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
            'name'      => 'KRISTILE ANNJOY DE SESTO',
        ]);
    }

    private function account(string $name): Account
    {
        return Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => $name,
        ]);
    }

    private function applicant(): Applicant
    {
        return Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $this->agent->id,
        ]);
    }

    private function makeItem(Account $account, string $particular, float $amount = 5000.0, float $payment = 0.0, string $status = ExpenseRequest::STATUS_RELEASED): ExpenseRequestItem
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $status,
            'branch_id'    => $this->branch->id,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'             => 'agent',
            'agent_id'           => $this->agent->id,
            'applicant_id'       => $this->applicant()->id,
            'account_id'         => $account->id,
            'currency'           => 'PHP',
            'amount'             => $amount,
            'payment'            => $payment,
            'status'             => $status,
            'particular'         => $particular,
        ]);
    }

    private function makeDeduction(string $particular, string $account = AgentDeduction::ACCOUNT_DEDUCTION): AgentDeduction
    {
        return AgentDeduction::create([
            'agency_id'  => $this->agency->id,
            'user_id'    => $this->admin->id,
            'agent_id'   => $this->agent->id,
            'date'       => '2026-03-21',
            'account'    => $account,
            'amount'     => 15000.00,
            'particular' => $particular,
        ]);
    }

    private function makeReceivable(string $particular, string $account = 'Placement Fee', string $status = 'received'): Receivable
    {
        return Receivable::create([
            'agency_id'     => $this->agency->id,
            'user_id'       => $this->admin->id,
            'agent_id'      => $this->agent->id,
            'applicant_id'  => $this->applicant()->id,
            'code'          => (string) random_int(100000, 999999),
            'date'          => now()->toDateString(),
            'status'        => $status,
            'amount'        => 7777.00,
            'account'       => $account,
            'debit_account' => 'Receivable',
            'type'          => 'Full Payment',
            'mode'          => 'GCash',
            'particular'    => $particular,
        ]);
    }

    /** Every <td> in the response whose text is exactly the particular. */
    private function particularCells(string $html, string $particular): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_clear_errors();

        $cells = [];
        foreach ($dom->getElementsByTagName('td') as $td) {
            if (trim($td->textContent) === $particular) {
                $cells[] = $td->ownerDocument->saveHTML($td);
            }
        }

        return $cells;
    }

    /**
     * Asserts the complete particular is rendered and every cell carrying it
     * wraps rather than clips.
     */
    private function assertWraps(string $html, string $particular, string $label, int $expectedCells = 1, int $minLength = 140): void
    {
        $this->assertGreaterThanOrEqual($minLength, strlen($particular), "Fixture for {$label} must pass the minimum length.");
        $this->assertStringContainsString($particular, $html, "The complete particular must be rendered on {$label}.");

        $cells = $this->particularCells($html, $particular);
        $this->assertCount($expectedCells, $cells, "Expected {$expectedCells} particular cell(s) on {$label}.");

        foreach ($cells as $i => $cell) {
            $this->assertStringNotContainsString('truncate', $cell, "{$label} cell #{$i} must not truncate.");
            $this->assertStringNotContainsString('text-ellipsis', $cell, "{$label} cell #{$i} must not use text-ellipsis.");
            $this->assertStringNotContainsString('overflow-hidden', $cell, "{$label} cell #{$i} must not clip overflow.");
            $this->assertStringContainsString('whitespace-normal', $cell, "{$label} cell #{$i} must allow wrapping.");
            $this->assertStringContainsString('break-words', $cell, "{$label} cell #{$i} must break long words.");
            $this->assertStringContainsString('align-top', $cell, "{$label} cell #{$i} must align to the top.");
            $this->assertStringContainsString('max-w-[26rem]', $cell, "{$label} cell #{$i} must cap at the shipped 26rem.");
            $this->assertStringContainsString('title=', $cell, "{$label} cell #{$i} must keep its title affordance.");
        }
    }

    private function tab(string $tab): string
    {
        $response = $this->actingAs($this->admin)->get(route('agent_report.index', ['tab' => $tab]));
        $response->assertOk();

        return $response->getContent();
    }

    private function ledger(): string
    {
        $response = $this->actingAs($this->admin)->get(route('agent_report.show', $this->agent));
        $response->assertOk();

        return $response->getContent();
    }

    #[Test]
    public function cash_advance_tab_particular_wraps(): void
    {
        $this->makeItem($this->account('Cash advance'), self::WRAP_CASH_ADVANCE);

        $this->assertWraps($this->tab('cash-advance'), self::WRAP_CASH_ADVANCE, 'cash-advance');
    }

    #[Test]
    public function commission_tab_particulars_wrap_in_both_tables(): void
    {
        // Table 1 lists PENDING expense items, table 2 lists RELEASED items on
        // the Fit to work / Deployed accounts. One fixture per table proves both
        // <td>s wrap.
        // 'Partial' is excluded from table 1 (it routes to Receivables), so use a
        // neutral account name for the pending row.
        $this->makeItem($this->account('Processing'), self::WRAP_COMMISSION, 5000.0, 0.0, ExpenseRequest::STATUS_PENDING);
        $this->makeItem($this->account('Fit to work'), self::WRAP_COMMISSION);

        // Same string in two rows -> two particular cells.
        $this->assertWraps($this->tab('commission'), self::WRAP_COMMISSION, 'commission', 2);
    }

    #[Test]
    public function payments_tab_particular_wraps(): void
    {
        $this->makeItem($this->account('Partial'), self::WRAP_PARTICULAR, 30000.0, 2000.0);

        $this->assertWraps($this->tab('payments'), self::WRAP_PARTICULAR, 'payments');
    }

    #[Test]
    public function receivables_tab_particular_wraps(): void
    {
        $this->makeReceivable(self::WRAP_RECEIVABLE, 'Agents & Applicant Payment');

        $this->assertWraps($this->tab('receivables'), self::WRAP_RECEIVABLE, 'receivables');
    }

    #[Test]
    public function return_payment_tab_particular_wraps(): void
    {
        $this->makeItem($this->account('Return'), self::WRAP_STARTING);

        $this->assertWraps($this->tab('return-payment'), self::WRAP_STARTING, 'return-payment');
    }

    #[Test]
    public function starting_balances_tab_particular_wraps(): void
    {
        \App\Models\StartingBalance::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'agent_id'     => $this->agent->id,
            'applicant_id' => $this->applicant()->id,
            'date'         => '2026-01-01',
            'account'      => \App\Models\StartingBalance::ACCOUNT,
            'amount'       => 5000.00,
            'particular'   => self::WRAP_STARTING,
        ]);

        $this->assertWraps($this->tab('starting-balances'), self::WRAP_STARTING, 'starting-balances');
    }

    #[Test]
    public function agent_ledger_particulars_wrap_in_all_six_tables(): void
    {
        // One long particular per ledger table. Each is unique so a cell is
        // attributable to its own table.
        $commission = 'COMMISSION FOR THE RELEASED PARTIAL PAYMENT OF THE APPLICANT INCLUDING ALL AGENCY SHARE AND DOCUMENTATION HANDLING FEES PAID AND VERIFIED';
        $cash       = 'CASH ADVANCE GIVEN TO THE AGENT FOR THE PROCESSING OF THE SAUDI WORK VISA, MEDICAL EXAMINATION, AND ALL ASSOCIATED DOCUMENTATION';
        $backout    = 'BACKOUT AND REPATRIATION DEDUCTION FOR THE AGENT WHO RETURNED FROM MIDDLE EAST AFTER MORE THAN THREE MONTHS OF WAITING';
        $receivable = 'RECEIVABLE FROM THE AGENT FOR THE AGENTS AND APPLICANT PAYMENT ACCOUNT RECORDED FOR THE PLACEMENT PROCESSING PERIOD';
        $return     = 'RETURN PAYMENT FROM THE AGENT REPRESENTING THE EXCESS AMOUNT REFUNDED AFTER THE FINAL RECONCILIATION OF THE ACCOUNT';
        $payment    = 'PAYMENT RECORDED FOR THE NET AMOUNT OWED TO THE AGENT AFTER ALL DEDUCTIONS AND CHARGES HAVE BEEN APPLIED AND VERIFIED';

        $this->makeItem($this->account('Partial'), $commission);
        $this->makeItem($this->account('Cash advance'), $cash);
        $this->makeDeduction($backout);
        $this->makeReceivable($receivable, 'Agents & Applicant Payment');
        $this->makeItem($this->account('Return'), $return, 4000.0);
        $this->makeDeduction($payment, AgentDeduction::ACCOUNT_PAID);

        $html = $this->ledger();

        foreach ([
            'commission' => $commission,
            'cash advance' => $cash,
            'backout' => $backout,
            'receivables' => $receivable,
            'return payment' => $return,
            'payment' => $payment,
        ] as $label => $particular) {
            // The six table headers/columns force shorter, still-realistic particulars.
            $this->assertWraps($html, $particular, "ledger {$label}", 1, 100);
        }
    }

    #[Test]
    public function expense_sourced_payment_row_on_ledger_wraps(): void
    {
        $expenseParticular = 'PAYMENT FOR THE COMMISSION RELEASE WITH PARTIAL AMOUNT RECORDED ON THE EXPENSE TRANSACTION FOR THIS SAME AGENT';

        // amount > 0 and payment > 0 -> appears in the ledger Payments table.
        $this->makeItem($this->account('Partial'), $expenseParticular, 30000.0, 2000.0);

        $response = $this->actingAs($this->admin)->get(route('agent_report.show', $this->agent));
        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString($expenseParticular, $html, 'The complete expense particular must be rendered.');

        // The cell text is "Expense #<ref>: <particular>" — find the cell by substring.
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_clear_errors();

        $found = 0;
        foreach ($dom->getElementsByTagName('td') as $td) {
            if (! str_contains($td->textContent, 'Expense #') || ! str_contains($td->textContent, $expenseParticular)) {
                continue;
            }
            $found++;
            $cell = $td->ownerDocument->saveHTML($td);
            $this->assertStringNotContainsString('truncate', $cell);
            $this->assertStringNotContainsString('text-ellipsis', $cell);
            $this->assertStringNotContainsString('overflow-hidden', $cell);
            $this->assertStringContainsString('whitespace-normal', $cell);
            $this->assertStringContainsString('break-words', $cell);
        }

        $this->assertSame(1, $found, 'Expected exactly one expense-sourced payment particular cell.');
    }

    #[Test]
    public function receivable_index_particular_wraps(): void
    {
        $particular = 'PLACEMENT FEE FOR THE APPLICANT UNDER THE ELITE REPAT PROGRAM WITH ADDITIONAL PROCESSING COSTS FOR DOCUMENTS AND MEDICAL EXAMINATION AND FEES';

        $this->makeReceivable($particular);

        $response = $this->actingAs($this->admin)->get(route('receivable.index'));
        $response->assertOk();

        $this->assertWraps($response->getContent(), $particular, 'receivable index');
    }
}
