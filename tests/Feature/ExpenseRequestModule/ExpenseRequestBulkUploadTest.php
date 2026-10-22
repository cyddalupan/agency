<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\ExpenseRequestStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseRequestBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    // Sample-template account names (round-trip guarantee: uploading the
    // downloaded template unchanged must import cleanly).
    private const OFFICE_CHILD = 'Rent';
    private const AGENT_CHILD = 'Commission';
    private const TEMPLATE_AGENT = 'Juan Dela Cruz';

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function seedChartOfAccounts(): void
    {
        $officeMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $officeMain->id,
            'name'        => self::OFFICE_CHILD,
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $agentMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Expenses',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);
        Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $agentMain->id,
            'name'        => self::AGENT_CHILD,
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);

        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => null,
            'name'      => self::TEMPLATE_AGENT,
            'email'     => 'juan.delacruz@example.com',
        ]);
    }

    private function seedBranch(string $name = 'Manila Office'): Branch
    {
        return Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => $name,
        ]);
    }

    private function uploadCsv(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->post(route('expense_request.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('expenses.csv', $content),
            ]);
    }

    // ---------- Page + template ----------

    #[Test]
    public function bulk_upload_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.bulk'));
        $response->assertOk();
        $response->assertSee('Bulk Upload Expense Requests');
        $response->assertSee('Download CSV Template');
        $response->assertSee('account names', false);
    }

    #[Test]
    public function template_download_contains_headers_and_sample_rows(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.bulk.template'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $body = $response->streamedContent();
        // UTF-8 BOM
        $this->assertStringStartsWith(chr(0xEF).chr(0xBB).chr(0xBF), $body);
        foreach (['Ref #', 'User', 'Date', 'Charge', 'Account', 'Applicant', 'Agent', 'Country', 'Currency', 'Amount', 'Payment', 'Particular/Description', 'Office', 'Request Status'] as $header) {
            $this->assertStringContainsString($header, $body);
        }
        $this->assertStringContainsString(self::OFFICE_CHILD, $body);
        $this->assertStringContainsString(self::AGENT_CHILD, $body);
    }

    // ---------- Valid imports ----------

    #[Test]
    public function import_creates_expense_requests_from_valid_csv(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Date,Charge,Account,Applicant,Agent,Country,Currency,Amount,Payment,Particular,Branch,Notes\n"
            ."2026-09-01,Office,".self::OFFICE_CHILD.",,,,PHP,1000.00,0,Monthly office rent,,Bulk test\n"
            ."2026-09-02,Agent,".self::AGENT_CHILD.",,".self::TEMPLATE_AGENT.",,PHP,2500.00,,Agent payout,,\n";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('expense_request.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expense_requests', [
            'agency_id' => $this->agency->id,
            'status'    => 'pending',
            'notes'     => 'Bulk test',
        ]);
        $this->assertSame(2, ExpenseRequest::where('agency_id', $this->agency->id)->count());
        $this->assertSame(2, ExpenseRequestItem::whereHas('expenseRequest', fn ($q) => $q->where('agency_id', $this->agency->id))->count());

        // Second row: agent-charged item carries the resolved agent.
        $agent = Agent::where('agency_id', $this->agency->id)->where('name', self::TEMPLATE_AGENT)->firstOrFail();
        $item = ExpenseRequestItem::whereHas('expenseRequest', fn ($q) => $q->where('agency_id', $this->agency->id))
            ->where('charge', 'agent')
            ->firstOrFail();
        $this->assertEquals($agent->id, $item->agent_id);

        // Each parent got its status history entry (from null -> pending).
        $historyCount = ExpenseRequestStatusHistory::where('to_status', 'pending')
            ->where('agency_id', $this->agency->id)
            ->count();
        $this->assertSame(2, $historyCount);
    }

    #[Test]
    public function round_trip_template_uploads_cleanly(): void
    {
        $this->seedChartOfAccounts();

        $template = $this->actingAs($this->user)->get(route('expense_request.bulk.template'));
        $body = $template->streamedContent();
        // Strip the UTF-8 BOM like the parser does when a real upload happens
        // (fgetcsv on an in-memory handle still sees the BOM bytes).
        $body = substr($body, 3);

        $response = $this->uploadCsv($body);
        $response->assertRedirect(route('expense_request.index'));
        $response->assertSessionHas('success');

        $this->assertSame(2, ExpenseRequest::where('agency_id', $this->agency->id)->count());
        $this->assertSame('pending', ExpenseRequest::where('agency_id', $this->agency->id)->first()->status);
    }

    #[Test]
    public function blank_date_defaults_to_today(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Date,Charge,Account,Currency,Amount,Particular\n"
            .",Office,".self::OFFICE_CHILD.",PHP,500,Office supplies\n";

        $response = $this->uploadCsv($csv);
        $response->assertRedirect(route('expense_request.index'));

        $request = ExpenseRequest::where('agency_id', $this->agency->id)->firstOrFail();
        $this->assertEquals(now()->toDateString(), $request->date->format('Y-m-d'));
    }

    // ---------- Row-level failures (all-or-nothing) ----------

    #[Test]
    public function import_rejects_invalid_currency_and_imports_nothing(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Charge,Account,Currency,Amount\n"
            ."Office,".self::OFFICE_CHILD.",JPY,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertRedirect();
        $response->assertSessionHas('bulk_errors');

        $errors = session('bulk_errors');
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('Currency', $errors[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_missing_amount_and_imports_nothing(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Charge,Account,Currency,Amount\n"
            ."Office,".self::OFFICE_CHILD.",PHP,\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('Amount is required.', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_unknown_account_and_imports_nothing(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Charge,Account,Currency,Amount\n"
            ."Office,Nonexistent Account,PHP,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('not found', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_account_type_mismatch(): void
    {
        $this->seedChartOfAccounts();
        // Agent account used on an Office-charged row (no applicant).
        $csv = "Charge,Account,Currency,Amount\n"
            ."Office,".self::AGENT_CHILD.",PHP,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('must match', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_unknown_applicant_and_imports_nothing(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Charge,Account,Applicant,Currency,Amount\n"
            ."Office,".self::OFFICE_CHILD.",Nobody Here,PHP,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString("Applicant 'Nobody Here' not found.", session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_unknown_agent_and_imports_nothing(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Charge,Account,Agent,Currency,Amount\n"
            ."Agent,".self::AGENT_CHILD.",Ghost Agent,PHP,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString("Agent 'Ghost Agent' not found.", session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_missing_header_and_shows_guidance(): void
    {
        $this->seedChartOfAccounts();
        $csv = "Name,Amount\nOffice stuff,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHasErrors('csv_file');
        $this->assertStringContainsString('No header row found', session('errors')->first('csv_file'));
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    // ---------- Branch-locked users ----------

    #[Test]
    public function branch_locked_user_defaults_blank_branch_to_own_branch(): void
    {
        $this->seedChartOfAccounts();
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $lockedUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $branch->id,
        ]);
        // isBranchLocked(): non-super_admin with a branch_id is branch-locked.

        $csv = "Charge,Account,Currency,Amount\n"
            ."Office,".self::OFFICE_CHILD.",PHP,100\n";

        $response = $this->actingAs($lockedUser)->post(route('expense_request.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('expenses.csv', $csv),
        ]);

        $response->assertRedirect(route('expense_request.index'));
        $this->assertDatabaseHas('expense_requests', [
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
        ]);
    }

    #[Test]
    public function branch_locked_user_cannot_file_for_other_branch(): void
    {
        $this->seedChartOfAccounts();
        $ownBranch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $otherBranch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Cebu']);

        $lockedUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $ownBranch->id,
        ]);

        $csv = "Charge,Account,Currency,Amount,Branch\n"
            ."Office,".self::OFFICE_CHILD.",PHP,100,Cebu\n";

        $response = $this->actingAs($lockedUser)->post(route('expense_request.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('expenses.csv', $csv),
        ]);

        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('only file expense requests for your own branch', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function valid_rows_import_even_when_other_rows_have_errors(): void
    {
        // Sanity: all-or-nothing applies per file — one bad row blocks the whole file.
        $this->seedChartOfAccounts();
        $csv = "Charge,Account,Currency,Amount\n"
            ."Office,".self::OFFICE_CHILD.",PHP,100\n"
            ."Office,".self::OFFICE_CHILD.",BAD,200\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function agency_sheet_format_maps_office_and_request_status(): void
    {
        $this->seedChartOfAccounts();
        $branch = $this->seedBranch();

        $csv = "Ref #,User,Date,Charge,Account,Applicant,Agent,Country,Currency,Amount,Payment,Particular/Description,Office,Request Status\n"
            .",,2026-10-01,Office,Rent,,,,PHP,1000.00,0,Monthly office rent,{$branch->name},Approved\n";

        $response = $this->uploadCsv($csv);
        $response->assertRedirect(route('expense_request.index'));
        $response->assertSessionHas('success');

        $request = ExpenseRequest::where('agency_id', $this->agency->id)->firstOrFail();
        $this->assertSame('approved', $request->status);
        $this->assertSame($branch->id, $request->branch_id);
        $this->assertSame($this->user->id, $request->user_id);
        $this->assertSame('approved', ExpenseRequestStatusHistory::where('expense_request_id', $request->id)->firstOrFail()->to_status);
    }

    #[Test]
    public function unknown_request_status_rejects_the_whole_file(): void
    {
        $this->seedChartOfAccounts();

        $csv = "Charge,Account,Currency,Amount,Request Status\n"
            ."Office,Rent,PHP,1000.00,Archived\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertSame(0, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }
}
