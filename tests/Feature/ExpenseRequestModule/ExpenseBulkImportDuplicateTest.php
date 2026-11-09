<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "Cash Advance — Missing Transaction" (Cyd 2026-11-09):
 * "Missing transactions from Bulk Upload data".
 *
 * Root cause: the bulk-import duplicate fingerprint read the existing row's
 * branch as `null`, while incoming rows carry a real branch. Nothing already on
 * file could ever match, so re-uploading the same sheet created a second full
 * copy instead of being skipped. Cash Advance rows were the ones users noticed:
 * the tab is Pending-only, so a duplicated upload doubled the numbers there.
 */
class ExpenseBulkImportDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $bacolod;
    private Agent $agent;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->bacolod = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'BACOLOD']);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->bacolod->id,
            'name'      => 'ANA MARIA ARSENIO',
        ]);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // Cash advance sub-account under an agent main.
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'name'        => 'AGENT',
            'charge_type' => 'agent',
        ]);
        Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => 'Cash advance',
            'charge_type' => 'agent',
        ]);
    }

    private function csv(string $branch = 'BACOLOD', string $agent = 'ANA MARIA ARSENIO', string $particular = 'CA row'): UploadedFile
    {
        $header = 'Ref #,User,Date,Charge,Account,Applicant,Agent,Country,Currency,Amount,Payment,Particular/Description,Office,Request Status';
        $row = implode(',', [
            '', '', now()->toDateString(), 'Agent', 'AGENT → Cash advance', '', $agent,
            '', 'PHP', '1234.00', '0', $particular, $branch, 'Pending',
        ]);

        return UploadedFile::fake()->createWithContent('bulk.csv', $header."\n".$row."\n");
    }

    private function import(UploadedFile $file)
    {
        return $this->actingAs($this->admin)->post(route('expense_request.bulk.import'), [
            'csv_file' => $file,
        ]);
    }

    #[Test]
    public function importing_a_file_creates_the_row(): void
    {
        $this->import($this->csv());

        $this->assertEquals(1, ExpenseRequestItem::where('particular', 'CA row')->count());

        $item = ExpenseRequestItem::where('particular', 'CA row')->firstOrFail();
        $this->assertEquals('pending', $item->expenseRequest->status);
        $this->assertEquals($this->bacolod->id, $item->expenseRequest->branch_id);
    }

    /** The bug: re-uploading the same sheet used to double every row. */
    #[Test]
    public function re_uploading_the_same_file_is_skipped_as_duplicate(): void
    {
        $this->import($this->csv());
        $this->assertEquals(1, ExpenseRequestItem::where('particular', 'CA row')->count());

        $response = $this->import($this->csv());

        // Still one row, and the user is told nothing new was imported.
        $this->assertEquals(1, ExpenseRequestItem::where('particular', 'CA row')->count());
        $response->assertRedirect();
        $this->assertStringContainsString(
            'already exist',
            (string) session('error'),
            'A repeat upload should report the rows as duplicates.'
        );
    }

    /** A genuinely different row must still import. */
    #[Test]
    public function a_different_row_still_imports(): void
    {
        $this->import($this->csv('BACOLOD', 'ANA MARIA ARSENIO', 'CA row'));
        $this->import($this->csv('BACOLOD', 'ANA MARIA ARSENIO', 'CA row two'));

        $this->assertEquals(1, ExpenseRequestItem::where('particular', 'CA row')->count());
        $this->assertEquals(1, ExpenseRequestItem::where('particular', 'CA row two')->count());
    }

    /** Repeated identical rows inside ONE file stay importable (count matching). */
    #[Test]
    public function identical_rows_within_one_file_are_kept(): void
    {
        $header = 'Ref #,User,Date,Charge,Account,Applicant,Agent,Country,Currency,Amount,Payment,Particular/Description,Office,Request Status';
        $row = implode(',', [
            '', '', now()->toDateString(), 'Agent', 'AGENT → Cash advance', '', 'ANA MARIA ARSENIO',
            '', 'PHP', '500.00', '0', 'twin row', 'BACOLOD', 'Pending',
        ]);

        $this->import(UploadedFile::fake()->createWithContent('bulk.csv', $header."\n".$row."\n".$row."\n"));

        $this->assertEquals(2, ExpenseRequestItem::where('particular', 'twin row')->count());
    }
}
