<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-01: "Pagawa ako ng bulk upload pra sa payment" — the Payments tab
 * of the Agent Report should accept a CSV that records the payment amount on
 * existing Expenses & Payments lines (Ref# + optional Applicant / Particular).
 */
class PaymentBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private User $accounting;
    private User $staff;
    private Account $mainAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->accounting = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
        ]);
        $this->staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->mainAccount = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Main',
            'charge_type' => 'agent',
        ]);
    }

    private function csv(array $rows): UploadedFile
    {
        $lines = ['"Ref#","Applicant","Agent","Currency","Payment","Particular"'];
        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $row));
        }

        return UploadedFile::fake()->createWithContent('payments.csv', implode("\n", $lines) . "\n");
    }

    /** New template: a row is keyed by transaction Date instead of Ref#. */
    private function csvByDate(array $rows): UploadedFile
    {
        $lines = ['"Date","Applicant","Agent","Currency","Payment","Particular"'];
        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $row));
        }

        return UploadedFile::fake()->createWithContent('payments.csv', implode("\n", $lines) . "\n");
    }

    private function makeRequest(string $ref, array $lines): ExpenseRequest
    {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'reference_no' => $ref,
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_PENDING,
        ]);

        foreach ($lines as $line) {
            ExpenseRequestItem::create(array_merge([
                'expense_request_id' => $request->id,
                'charge'       => 'agent',
                'currency'     => 'PHP',
                'amount'       => 1000.00,
                'payment'      => 0,
                'status'       => ExpenseRequest::STATUS_PENDING,
                'account_id'   => $this->mainAccount->id,
            ], $line));
        }

        return $request->fresh('items');
    }

    #[Test]
    public function admin_and_accounting_can_open_the_payment_bulk_page(): void
    {
        foreach ([$this->admin, $this->accounting] as $user) {
            $this->actingAs($user)
                ->get(route('agent_report.payment.bulk'))
                ->assertOk()
                ->assertSee('Bulk Upload Payments')
                ->assertSee('Download CSV template');
        }
    }

    #[Test]
    public function staff_cannot_open_or_use_the_payment_bulk_upload(): void
    {
        $this->actingAs($this->staff)->get(route('agent_report.payment.bulk'))->assertForbidden();

        $req = $this->makeRequest('30001', [['particular' => 'FTW Commission']]);

        $this->actingAs($this->staff)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([['30001', '', '', 'PHP', '500.00', 'FTW Commission']]),
            ])
            ->assertForbidden();

        $this->assertSame('0.00', (string) $req->items()->first()->payment);
    }

    #[Test]
    public function the_template_lists_the_expected_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('agent_report.payment.bulk.template'));

        $response->assertOk();
        $body = $response->streamedContent();
        $this->assertStringContainsString('Date', $body);
        $this->assertStringContainsString('Payment', $body);
        // Josh 2026-11-02: the Ref# column is replaced by Date.
        $this->assertStringNotContainsString('Ref#', $body);
    }

    #[Test]
    public function a_payment_can_be_matched_by_transaction_date_instead_of_ref(): void
    {
        $req = $this->makeRequest('30010', [['particular' => 'FTW Commission', 'amount' => 5000.00]]);

        $this->actingAs($this->accounting)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([[now()->toDateString(), '', '', 'PHP', '1,500.00', 'FTW Commission']]),
            ])
            ->assertRedirect(route('agent_report.index', ['tab' => 'payments']))
            ->assertSessionHas('success');

        $this->assertSame('1500.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function two_transactions_on_the_same_date_need_the_particular_to_disambiguate(): void
    {
        $a = $this->makeRequest('30011', [['particular' => 'Line A', 'amount' => 1000.00]]);
        $b = $this->makeRequest('30012', [['particular' => 'Line B', 'amount' => 1000.00]]);

        // Ambiguous: same date, no other hint.
        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([[now()->toDateString(), '', '', 'PHP', '100.00', '']]),
            ])
            ->assertSessionHasErrors('csv_file');

        $this->assertSame(0.0, (float) $a->items()->sum('payment'));
        $this->assertSame(0.0, (float) $b->items()->sum('payment'));

        // Resolved by the Particular.
        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([[now()->toDateString(), '', '', 'PHP', '250.00', 'Line B']]),
            ])
            ->assertRedirect(route('agent_report.index', ['tab' => 'payments']));

        $this->assertSame('250.00', (string) $b->items()->first()->fresh()->payment);
        $this->assertSame(0.0, (float) $a->items()->sum('payment'));
    }

    #[Test]
    public function an_unknown_date_blocks_the_whole_file(): void
    {
        $req = $this->makeRequest('30013', [['particular' => 'FTW Commission']]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([
                    [now()->toDateString(), '', '', 'PHP', '100.00', 'FTW Commission'],
                    ['1999-01-01', '', '', 'PHP', '200.00', ''],
                ]),
            ])
            ->assertSessionHasErrors('csv_file');

        $this->assertSame('0.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function accounting_can_record_a_payment_on_a_single_line_request(): void
    {
        $req = $this->makeRequest('30002', [['particular' => 'FTW Commission', 'amount' => 5000.00]]);

        $this->actingAs($this->accounting)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([['30002', '', '', 'PHP', '1,250.00', 'FTW Commission']]),
            ])
            ->assertRedirect(route('agent_report.index', ['tab' => 'payments']))
            ->assertSessionHas('success');

        $this->assertSame('1250.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function a_multi_line_request_uses_the_applicant_to_pick_the_line(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $applicantA = \App\Models\Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'agent_id'   => $agent->id,
            'first_name' => 'Maria',
            'last_name'  => 'Dela Cruz',
            'middle_name' => null,
        ]);

        $req = $this->makeRequest('30003', [
            ['agent_id' => $agent->id, 'applicant_id' => $applicantA->id, 'particular' => 'Placement'],
            ['agent_id' => $agent->id, 'particular' => 'Commission'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([['30003', 'Dela Cruz, Maria', '', 'PHP', '400.00', '']]),
            ])
            ->assertRedirect(route('agent_report.index', ['tab' => 'payments']));

        $this->assertSame('400.00', (string) $req->items()->where('applicant_id', $applicantA->id)->first()->payment);
        $this->assertSame('0.00', (string) $req->items()->whereNull('applicant_id')->first()->payment);
    }

    #[Test]
    public function an_ambiguous_row_is_rejected_and_nothing_is_saved(): void
    {
        $req = $this->makeRequest('30004', [
            ['particular' => 'Line A'],
            ['particular' => 'Line B'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([['30004', '', '', 'PHP', '100.00', '']]),
            ])
            ->assertSessionHasErrors('csv_file');

        $this->assertSame(0.0, (float) $req->items()->sum('payment'));
    }

    #[Test]
    public function an_unknown_reference_number_blocks_the_whole_file(): void
    {
        $req = $this->makeRequest('30005', [['particular' => 'FTW Commission']]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([
                    ['30005', '', '', 'PHP', '100.00', 'FTW Commission'],
                    ['99999', '', '', 'PHP', '200.00', ''],
                ]),
            ])
            ->assertSessionHasErrors('csv_file');

        $this->assertSame('0.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function a_payment_bigger_than_the_line_amount_is_allowed(): void
    {
        // Cyd 2026-11-14: "Bypass the date and amounts (greater) error."
        // An encoder overpaying a line is real money received, not a typo to block.
        $req = $this->makeRequest('30006', [['particular' => 'FTW Commission', 'amount' => 500.00]]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([['30006', '', '', 'PHP', '900.00', 'FTW Commission']]),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('900.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function a_payment_date_with_no_transaction_still_imports_by_matching_the_other_columns(): void
    {
        // Cyd 2026-11-14: the sheet's date need not exist as a transaction date.
        $req = $this->makeRequest('30007', [['particular' => 'PD FTW Commission']]);
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Mary Jane Morante']);
        $req->items()->first()->update(['agent_id' => $agent->id]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([
                    // A date deliberately far from the transaction date.
                    ['2026-11-05', '', 'Mary Jane Morante', 'PHP', '250.00', 'PD FTW Commission'],
                ]),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('250.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function a_payment_date_with_no_transaction_and_no_matching_line_is_still_rejected(): void
    {
        $this->makeRequest('30008', [['particular' => 'Something Else']]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([
                    ['2026-11-05', 'Nobody At All', '', 'PHP', '250.00', 'Not A Real Particular'],
                ]),
            ])
            ->assertSessionHasErrors('csv_file');
    }

    #[Test]
    public function a_reference_number_from_another_agency_is_not_found(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAdmin = User::factory()->create([
            'agency_id' => $otherAgency->id,
            'user_type' => 'admin',
        ]);
        $foreign = ExpenseRequest::create([
            'agency_id'    => $otherAgency->id,
            'user_id'      => $otherAdmin->id,
            'reference_no' => '50001',
            'date'         => now()->toDateString(),
            'status'       => ExpenseRequest::STATUS_PENDING,
        ]);
        ExpenseRequestItem::create([
            'expense_request_id' => $foreign->id,
            'charge'       => 'agent',
            'currency'     => 'PHP',
            'amount'       => 1000.00,
            'payment'      => 0,
            'status'       => ExpenseRequest::STATUS_PENDING,
            'account_id'   => $this->mainAccount->id,
            'particular'   => 'Foreign line',
        ]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csv([['50001', '', '', 'PHP', '100.00', 'Foreign line']]),
            ])
            ->assertSessionHasErrors('csv_file');

        // Read past the tenant scope on purpose: the line belongs to another
        // agency, and this test is about the import refusing the row, not about
        // the scope hiding it. A scoped relation would return null here and the
        // assertion would crash instead of checking the payment.
        $this->assertSame('0.00', (string) ExpenseRequestItem::withoutGlobalScopes()
            ->where('expense_request_id', $foreign->id)
            ->firstOrFail()
            ->payment);
    }

    #[Test]
    public function a_single_line_date_is_matched_even_when_the_sheet_names_a_different_applicant(): void
    {
        // Josh 2026-11-02: his sheet carries the Applicant/Agent of his own
        // records, but office-charged lines in Expenses & Payments have none.
        // One line on the date is unambiguous — it must not be rejected.
        $req = $this->makeRequest('30021', [['particular' => 'PD FR. DEP COMM', 'amount' => 7500.00]]);

        $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([[
                    now()->toDateString(), 'KRISTITA JANE PALANG-AT', 'ROSENE SEMENTILA', 'PHP', '7500.00', 'PD FR. DEP COMM',
                ]]),
            ])
            ->assertRedirect(route('agent_report.index', ['tab' => 'payments']))
            ->assertSessionHas('success');

        $this->assertSame('7500.00', (string) $req->items()->first()->fresh()->payment);
    }

    #[Test]
    public function a_row_that_matches_nothing_lists_the_lines_the_date_actually_has(): void
    {
        $this->makeRequest('30022', [['particular' => 'Line A', 'amount' => 4500.00]]);
        $this->makeRequest('30023', [['particular' => 'Line B', 'amount' => 800.00]]);

        $response = $this->actingAs($this->admin)
            ->from(route('agent_report.payment.bulk'))
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([[
                    now()->toDateString(), 'KRISTITA JANE PALANG-AT', '', 'PHP', '7500.00', 'PD FR. DEP COMM',
                ]]),
            ]);

        $response->assertSessionHasErrors('csv_file');
        $bulkErrors = session('bulk_errors');
        $message = implode(' ', collect($bulkErrors)->flatten()->all());

        $this->assertStringContainsString('No line on the', $message);
        $this->assertStringContainsString('30022', $message);
        $this->assertStringContainsString('30023', $message);
        $this->assertStringContainsString('PD FR. DEP COMM', $message);
    }

    // ---------- Card 6ae75260 "for fix": diagnosable no-match errors ----------

    /**
     * Josh 2026-11-08 ("for fix"): a row that matches nothing only produced a
     * wall of the date's 20 lines, so there was no way to see WHICH criterion
     * was wrong. The message must now name the failed criterion.
     */
    #[Test]
    public function a_no_match_row_says_which_criteria_failed(): void
    {
        // Two lines on the date: with only one, the row is accepted regardless
        // (a lone line is unambiguous), and the no-match path is never reached.
        $this->makeRequest('31001', [
            ['particular' => 'Deployed comm'],
            ['particular' => 'Office rental'],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([
                    [now()->toDateString(), 'JJOVELYN SISO', 'MARY JANE MORANTE', 'PHP', '500.00', 'PD FR. FTW COMM'],
                ]),
            ]);

        $response->assertSessionHasErrors('csv_file');

        $message = json_encode($response->getSession()->get('bulk_errors'));
        $this->assertStringContainsString('Likely cause', $message);
        $this->assertStringContainsString('JJOVELYN SISO', $message);
        $this->assertStringContainsString('MARY JANE MORANTE', $message);
    }

    #[Test]
    public function the_diagnosis_only_names_the_criteria_that_actually_failed(): void
    {
        // 'Deployed comm' exists on this date, 'Office rental' also exists —
        // so the particular criterion DOES hit, while the applicant does not.
        $this->makeRequest('31002', [
            ['particular' => 'Deployed comm'],
            ['particular' => 'Deployed comm'],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([
                    [now()->toDateString(), 'NOBODY HERE', '', 'PHP', '999.00', 'something else entirely'],
                ]),
            ]);

        $response->assertSessionHasErrors('csv_file');

        $message = json_encode($response->getSession()->get('bulk_errors'));
        $this->assertStringContainsString('NOBODY HERE', $message);
        // Only the applicant failed — the particular must not be named as a cause.
        $this->assertStringNotContainsString("has particular 'Deployed comm'", $message);
        $this->assertStringContainsString("has particular 'something else entirely'", $message);
    }

    #[Test]
    public function the_no_match_error_still_lists_the_dates_lines(): void
    {
        $this->makeRequest('31003', [
            ['particular' => 'Office rental'],
            ['particular' => 'Deployed comm'],
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('agent_report.payment.bulk.import'), [
                'csv_file' => $this->csvByDate([
                    [now()->toDateString(), 'NOBODY HERE', '', 'PHP', '999.00', 'nothing like this'],
                ]),
            ]);

        $response->assertSessionHasErrors('csv_file');

        $message = json_encode($response->getSession()->get('bulk_errors'));
        $this->assertStringContainsString('31003', $message, 'The date\'s lines are still listed so the sheet can be fixed in one pass.');
    }
}
