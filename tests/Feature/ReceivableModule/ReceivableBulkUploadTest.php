<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-09-06 — Bulk CSV upload on the Receivable page (mirrors the
 * ExpenseRequest bulk flow): download a template, fill it in (one row = one
 * receivable), upload, and all rows are created as Pending in one transaction.
 *
 * 1. Valid CSV imports receivables with resolved agent/applicant + auto code.
 * 2. Uploading the downloaded template unchanged imports cleanly (round trip).
 * 3. Blank date defaults to today; fixed enum columns must match Receivable
 *    options (account/debit account/type/mode), not the chart of accounts.
 * 4. All-or-nothing: one bad row blocks the whole file with per-line errors.
 * 5. Isolation: only the caller's own agency's agents/applicants resolve.
 * 6. Branch-locked users may only file for their own branch (or main-office)
 *    agents, matching the New Receivable form.
 */
class ReceivableBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    // Sample-template agent (round-trip guarantee: uploading the downloaded
    // template unchanged must import cleanly, so the test seeds this agent).
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

    private function seedAgent(string $name = self::TEMPLATE_AGENT, ?int $branchId = null): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branchId,
            'name'      => $name,
            'email'     => str_replace(' ', '.', strtolower($name)).'@example.com',
        ]);
    }

    private function uploadCsv(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->post(route('receivable.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('receivables.csv', $content),
            ]);
    }

    // ---------- Page + template ----------

    #[Test]
    public function bulk_upload_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('receivable.bulk'));
        $response->assertOk();
        $response->assertSee('Bulk Upload Receivables');
        $response->assertSee('Download CSV Template');
        $response->assertSee('Agent');
        $response->assertSee('Placement Fee');
    }

    #[Test]
    public function template_download_contains_headers_and_sample_rows(): void
    {
        $response = $this->actingAs($this->user)->get(route('receivable.bulk.template'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $body = $response->streamedContent();
        // UTF-8 BOM
        $this->assertStringStartsWith(chr(0xEF).chr(0xBB).chr(0xBF), $body);
        foreach (['Code #', 'Date', 'User', 'Status', 'Ref#/AR#', 'Agent', 'Applicant', 'Amount', 'Account', 'Particular', 'Mode', 'Type', 'Deposit Bank'] as $header) {
            $this->assertStringContainsString($header, $body);
        }
        $this->assertStringContainsString('Placement Fee', $body);
        $this->assertStringContainsString('Monthly Collection', $body);
    }

    // ---------- Valid imports ----------

    #[Test]
    public function import_creates_receivables_from_valid_csv(): void
    {
        $agent = $this->seedAgent();
        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'agent_id'   => $agent->id,
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
        ]);

        $csv = "Date,Ref#/AR#,Agent,Applicant,Amount,Account,Debit Account,Type,Mode,Particular\n"
            ."2026-09-01,AR-1001,".self::TEMPLATE_AGENT.",,15000.00,Placement Fee,Receivable,Full Payment,GCash,Partial placement fee\n"
            ."2026-09-02,AR-1002,".self::TEMPLATE_AGENT.",Maria Santos,5000.00,Monthly Collection,Dollar Request,Partial,Fund Transfer,Monthly collection\n";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('receivable.index'));
        $response->assertSessionHas('success');

        $this->assertSame(2, Receivable::where('agency_id', $this->agency->id)->count());

        $first = Receivable::where('agency_id', $this->agency->id)
            ->where('ref_ar', 'AR-1001')->firstOrFail();
        $this->assertEquals($agent->id, $first->agent_id);
        $this->assertNull($first->applicant_id);
        $this->assertSame('pending', $first->status);
        $this->assertSame('15000.00', (string) $first->amount);
        $this->assertSame('Placement Fee', $first->account);
        $this->assertSame('Receivable', $first->debit_account);
        $this->assertSame('Full Payment', $first->type);
        $this->assertSame('GCash', $first->mode);
        $this->assertSame($this->user->id, $first->user_id);
        $this->assertSame('2026-09-01', $first->date->format('Y-m-d'));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $first->code);

        $second = Receivable::where('agency_id', $this->agency->id)
            ->where('ref_ar', 'AR-1002')->firstOrFail();
        $this->assertEquals($agent->id, $second->agent_id);
        $this->assertEquals($applicant->id, $second->applicant_id);
        $this->assertSame('Dollar Request', $second->debit_account);
        $this->assertSame('Partial', $second->type);
        $this->assertSame('Fund Transfer', $second->mode);

        // Codes are unique within the agency.
        $this->assertNotSame($first->code, $second->code);
    }

    #[Test]
    public function round_trip_template_uploads_cleanly(): void
    {
        $this->seedAgent();

        $template = $this->actingAs($this->user)->get(route('receivable.bulk.template'));
        $body = $template->streamedContent();
        // Strip the UTF-8 BOM like the parser does when a real upload happens.
        $body = substr($body, 3);

        $response = $this->uploadCsv($body);
        $response->assertRedirect(route('receivable.index'));
        $response->assertSessionHas('success');

        $this->assertSame(2, Receivable::where('agency_id', $this->agency->id)->count());
        $this->assertSame('pending', Receivable::where('agency_id', $this->agency->id)->first()->status);
    }

    #[Test]
    public function agency_sheet_format_maps_code_user_status_and_deposit_bank(): void
    {
        $this->seedAgent();
        $csv = "Code #,Date,User,Status,Ref#/AR#,Applicant,Agent,Amount,Account,Particular,Mode,Type,Deposit Bank\n"
            .",2026-10-01,".$this->user->email.",Received,AR-9001,,".self::TEMPLATE_AGENT.",4200.00,Placement Fee,Partial placement fee,GCash,Full Payment,Dollar Request\n";

        $response = $this->uploadCsv($csv);
        $response->assertRedirect(route('receivable.index'));
        $response->assertSessionHas('success');

        $r = Receivable::where('agency_id', $this->agency->id)->firstOrFail();
        $this->assertSame('received', $r->status);
        $this->assertSame('AR-9001', $r->ref_ar);
        $this->assertSame('Dollar Request', $r->debit_account);
        $this->assertSame($this->user->id, $r->user_id);
        $this->assertNotSame('', (string) $r->code, 'Code # is generated, never taken from the sheet');
    }

    #[Test]
    public function unknown_status_rejects_the_whole_file(): void
    {
        $this->seedAgent();
        $csv = "Agent,Amount,Status\n"
            .self::TEMPLATE_AGENT.",500,Done\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function blank_date_defaults_to_today(): void
    {
        $this->seedAgent();
        $csv = "Date,Agent,Amount,Account\n"
            .",".self::TEMPLATE_AGENT.",500,Placement Fee\n";

        $response = $this->uploadCsv($csv);
        $response->assertRedirect(route('receivable.index'));

        $receivable = Receivable::where('agency_id', $this->agency->id)->firstOrFail();
        $this->assertEquals(now()->toDateString(), $receivable->date->format('Y-m-d'));
    }

    #[Test]
    public function import_matches_agent_case_and_space_insensitively(): void
    {
        $this->seedAgent('John Michael Cruz');
        $csv = "Agent,Amount\njohn   michael   CRUZ,750\n";

        $response = $this->uploadCsv($csv);
        $response->assertRedirect(route('receivable.index'));
        $response->assertSessionHas('success');

        $receivable = Receivable::where('agency_id', $this->agency->id)->firstOrFail();
        $this->assertSame('John Michael Cruz', $receivable->agent->name);
    }

    // ---------- Row-level failures (all-or-nothing) ----------

    #[Test]
    public function import_rejects_unknown_agent_and_imports_nothing(): void
    {
        $csv = "Agent,Amount\nGhost Agent,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString("Agent 'Ghost Agent' not found.", session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_missing_agent_and_imports_nothing(): void
    {
        $csv = "Agent,Amount\n,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('Agent is required.', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_missing_amount_and_imports_nothing(): void
    {
        $agent = $this->seedAgent();
        $csv = "Agent,Amount\n".self::TEMPLATE_AGENT.",\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('Amount is required.', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_non_numeric_amount_and_imports_nothing(): void
    {
        $this->seedAgent();
        $csv = "Agent,Amount\n".self::TEMPLATE_AGENT.",abc\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('must be a number', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_zero_amount_and_imports_nothing(): void
    {
        $this->seedAgent();
        $csv = "Agent,Amount\n".self::TEMPLATE_AGENT.",0\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('greater than 0', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_unknown_account_enum_and_imports_nothing(): void
    {
        $this->seedAgent();
        $csv = "Agent,Amount,Account\n".self::TEMPLATE_AGENT.",100,Nonexistent Account\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString("Account 'Nonexistent Account' not found.", session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_unknown_enum_values_and_imports_nothing(): void
    {
        $this->seedAgent();
        $csv = "Agent,Amount,Debit Account,Type,Mode\n"
            .self::TEMPLATE_AGENT.",100,NotADebit,Bogus,Venmo\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');

        $messages = implode("\n", session('bulk_errors')[0]['errors']);
        $this->assertStringContainsString("Debit Account 'NotADebit' not found.", $messages);
        $this->assertStringContainsString("Type 'Bogus' not found.", $messages);
        $this->assertStringContainsString("Mode 'Venmo' not found.", $messages);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_applicant_not_belonging_to_selected_agent(): void
    {
        $agent = $this->seedAgent();
        $otherAgent = $this->seedAgent('Pedro Reyes');
        Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'agent_id'   => $otherAgent->id,
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
        ]);

        $csv = "Agent,Applicant,Amount\n".self::TEMPLATE_AGENT.",Maria Santos,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('must belong to the selected agent', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_unknown_applicant_and_imports_nothing(): void
    {
        $this->seedAgent();
        $csv = "Agent,Applicant,Amount\n".self::TEMPLATE_AGENT.",Nobody Here,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString("Applicant 'Nobody Here' not found.", session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_rejects_missing_header_and_shows_guidance(): void
    {
        $csv = "Name,Amount\nSome Agent,100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHasErrors('csv_file');
        $this->assertStringContainsString('No header row found', session('errors')->first('csv_file'));
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function import_does_not_resolve_agents_from_other_agencies(): void
    {
        // Another agency has an agent with the exact same name — it must not resolve.
        $otherAgency = Agency::factory()->create();
        Agent::factory()->create([
            'agency_id' => $otherAgency->id,
            'name'      => self::TEMPLATE_AGENT,
        ]);

        $csv = "Agent,Amount\n".self::TEMPLATE_AGENT.",100\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString("Agent '".self::TEMPLATE_AGENT."' not found.", session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function valid_rows_are_not_imported_when_another_row_has_errors(): void
    {
        // All-or-nothing: one bad row blocks the whole file.
        $this->seedAgent();
        $csv = "Agent,Amount\n"
            .self::TEMPLATE_AGENT.",100\n"
            ."Ghost Agent,200\n";

        $response = $this->uploadCsv($csv);
        $response->assertSessionHas('bulk_errors');
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }

    // ---------- Branch-locked users ----------

    #[Test]
    public function branch_locked_user_can_import_for_own_branch_agent(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->seedAgent(self::TEMPLATE_AGENT, $branch->id);

        $lockedUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $branch->id,
        ]);

        $csv = "Agent,Amount\n".self::TEMPLATE_AGENT.",100\n";

        $response = $this->actingAs($lockedUser)->post(route('receivable.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('receivables.csv', $csv),
        ]);

        $response->assertRedirect(route('receivable.index'));
        $response->assertSessionHas('success');
        $this->assertSame(1, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function branch_locked_user_can_import_for_main_office_agent_without_branch(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $this->seedAgent(self::TEMPLATE_AGENT, null); // main-office agent

        $lockedUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $branch->id,
        ]);

        $csv = "Agent,Amount\n".self::TEMPLATE_AGENT.",100\n";

        $response = $this->actingAs($lockedUser)->post(route('receivable.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('receivables.csv', $csv),
        ]);

        $response->assertRedirect(route('receivable.index'));
        $response->assertSessionHas('success');
        $this->assertSame(1, Receivable::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function branch_locked_user_cannot_file_for_other_branch_agent(): void
    {
        $ownBranch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Cebu']);
        $this->seedAgent('Cebu Agent', null);
        // The Cebu agent lives under a branch via a second agent record:
        $cebuBranch = Branch::where('agency_id', $this->agency->id)->where('name', 'Cebu')->firstOrFail();
        $this->seedAgent('Cebu Branch Agent', $cebuBranch->id);

        $lockedUser = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'billing',
            'branch_id' => $ownBranch->id,
        ]);

        $csv = "Agent,Amount\nCebu Branch Agent,100\n";

        $response = $this->actingAs($lockedUser)->post(route('receivable.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('receivables.csv', $csv),
        ]);

        $response->assertSessionHas('bulk_errors');
        $this->assertStringContainsString('only file receivables for agents in your own branch', session('bulk_errors')[0]['errors'][0]);
        $this->assertSame(0, Receivable::where('agency_id', $this->agency->id)->count());
    }
}
