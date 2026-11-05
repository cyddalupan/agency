<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mjolnir card "LANDAS: New Module 'General Ledger'".
 *
 * Main accounts (AGENT / APPLICANT / OFFICE) list their sub accounts with the
 * transaction total per sub account, a Total row per main account, a grand
 * total, a Review listing per sub account and CSV exports.
 */
class GeneralLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Account $main;
    private Account $salary;
    private Account $pltd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user   = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->main = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'OFFICE',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $this->salary = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->main->id,
            'name'        => 'Salary',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $this->pltd = Account::create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->main->id,
            'name'        => 'PLDT',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
    }

    private function item(Account $account, float $amount, string $status = 'released', string $ref = 'REF-0001'): ExpenseRequestItem
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => $ref,
            'date'         => now()->subDays(2)->toDateString(),
            'status'       => $status,
        ]);

        return ExpenseRequestItem::create([
            'expense_request_id' => $request->id,
            'charge'             => 'office',
            'currency'           => 'PHP',
            'amount'             => $amount,
            'status'             => $status,
            'account_id'         => $account->id,
            'particular'         => 'Probe line for ' . $account->name,
        ]);
    }

    public function test_sidebar_shows_general_ledger_under_agents_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('general_ledger.index'));

        $response->assertOk();
        $response->assertSee('General Ledger');
        $response->assertSee(route('general_ledger.index'));
    }

    public function test_index_lists_sub_accounts_with_totals_and_grand_total(): void
    {
        $this->item($this->salary, 1500.50, 'released', 'REF-A');
        $this->item($this->salary, 2500.50, 'pending', 'REF-B');
        $this->item($this->pltd, 900.00, 'released', 'REF-C');

        $response = $this->actingAs($this->user)->get(route('general_ledger.index'));

        $response->assertOk();
        $response->assertSee('OFFICE');
        $response->assertSee('Salary');
        $response->assertSee('PLDT');
        $response->assertSee('4,001.00');   // Salary subtotal
        $response->assertSee('900.00');     // PLDT subtotal
        $response->assertSee('4,901.00');   // Group total / grand total
        $response->assertSee('Review');
    }

    public function test_index_excludes_cancelled_transactions(): void
    {
        $this->item($this->salary, 1000.00, 'released', 'REF-OK');
        $this->item($this->salary, 5000.00, 'cancelled', 'REF-CANCELLED');

        $response = $this->actingAs($this->user)->get(route('general_ledger.index'));

        $response->assertOk();
        $response->assertSee('1,000.00');
        $response->assertDontSee('6,000.00');
    }

    public function test_review_lists_transactions_with_the_required_columns(): void
    {
        $this->item($this->salary, 1234.56, 'released', 'REF-REVIEW');

        $response = $this->actingAs($this->user)->get(route('general_ledger.review', $this->salary));

        $response->assertOk();
        $response->assertSee('REF-REVIEW');
        $response->assertSee('Probe line for Salary');
        $response->assertSee('1,234.56');
        foreach (['Ref#', 'Date', 'User', 'Status', 'Offices', 'Applicant', 'Agent', 'Currency', 'Amount', 'Account', 'Country', 'Particular'] as $header) {
            $response->assertSee($header);
        }
    }

    public function test_ledger_csv_export_streams_rows(): void
    {
        $this->item($this->salary, 750.25, 'released', 'REF-CSV');

        $response = $this->actingAs($this->user)->get(route('general_ledger.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Main Account', $csv);
        $this->assertStringContainsString('Sub Accounts', $csv);
        $this->assertStringContainsString('Transactions', $csv);
        $this->assertStringContainsString('OFFICE,Expense,Salary,1,750.25', $csv);
        $this->assertStringContainsString('TOTAL AMOUNT', $csv);
    }

    public function test_review_rejects_an_account_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $foreign = Account::create([
            'agency_id'   => $otherAgency->id,
            'parent_id'   => null,
            'name'        => 'FOREIGN',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $this->actingAs($this->user)
            ->get(route('general_ledger.review', $foreign))
            ->assertNotFound();
    }
}
