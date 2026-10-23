<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\ExpenseRequestStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Re-uploading the same sheet must not double the transactions.
 *
 * (Cyd 2026-10-23) Jerry uploaded a 62-row sheet, uploaded it again, and ended
 * up with 124 rows — a "double entry". The importer now count-matches each row
 * against what is already on file for the agency.
 */
class ExpenseRequestBulkUploadDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => 'Rent',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
    }

    private function uploadCsv(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)->post(route('expense_request.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('expenses.csv', $content),
        ]);
    }

    private function csvWith(array $rows): string
    {
        $csv = "Date,Charge,Account,Currency,Amount,Payment,Particular/Description,Request Status\n";
        foreach ($rows as $row) {
            $csv .= implode(',', $row)."\n";
        }

        return $csv;
    }

    private function itemCount(): int
    {
        return ExpenseRequestItem::whereHas('expenseRequest', fn ($q) => $q->where('agency_id', $this->agency->id))->count();
    }

    #[Test]
    public function uploading_the_same_file_twice_does_not_double_the_rows(): void
    {
        $csv = $this->csvWith([
            ['2026-03-11', 'Office', 'Rent', 'PHP', '3000.00', '0', 'March gas', 'Released'],
            ['2026-03-02', 'Office', 'Rent', 'PHP', '5500.00', '0', 'Feb gas', 'Released'],
        ]);

        $this->uploadCsv($csv)->assertRedirect(route('expense_request.index'));
        $this->assertSame(2, $this->itemCount());

        // Second upload of the exact same sheet — nothing new, so nothing
        // should be created and the user is told why.
        $second = $this->uploadCsv($csv);
        $second->assertRedirect(route('expense_request.index'));
        $second->assertSessionHas('error');
        $this->assertStringContainsString('all 2 row(s)', session('error'));

        $this->assertSame(2, $this->itemCount(), 'Re-upload must not create a second copy.');
        $this->assertSame(2, ExpenseRequest::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function re_upload_of_a_fully_duplicate_file_imports_nothing_and_says_so(): void
    {
        $csv = $this->csvWith([
            ['2026-03-11', 'Office', 'Rent', 'PHP', '3000.00', '0', 'March gas', 'Released'],
        ]);

        $this->uploadCsv($csv);
        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('already exist', session('error'));
        $this->assertSame(1, $this->itemCount());
        $this->assertSame(1, ExpenseRequestStatusHistory::where('agency_id', $this->agency->id)->count());
    }

    #[Test]
    public function only_the_new_rows_of_a_partly_duplicate_file_are_imported(): void
    {
        $first = $this->csvWith([
            ['2026-03-11', 'Office', 'Rent', 'PHP', '3000.00', '0', 'March gas', 'Released'],
        ]);
        $this->uploadCsv($first);

        $second = $this->csvWith([
            ['2026-03-11', 'Office', 'Rent', 'PHP', '3000.00', '0', 'March gas', 'Released'],
            ['2026-04-01', 'Office', 'Rent', 'PHP', '4200.00', '0', 'April gas', 'Released'],
        ]);

        $response = $this->uploadCsv($second);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 expense request(s) imported', session('success'));
        $this->assertStringContainsString('1 duplicate row(s) skipped', session('success'));
        $this->assertSame(2, $this->itemCount());
    }

    #[Test]
    public function genuinely_identical_rows_within_one_file_still_import(): void
    {
        // Two real 500.00 cash advances on the same day, same account.
        $csv = $this->csvWith([
            ['2026-05-04', 'Office', 'Rent', 'PHP', '500.00', '0', 'Cash advance', 'Released'],
            ['2026-05-04', 'Office', 'Rent', 'PHP', '500.00', '0', 'Cash advance', 'Released'],
        ]);

        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('success');
        $this->assertSame(2, $this->itemCount());
    }

    #[Test]
    public function a_deleted_or_cancelled_request_does_not_block_a_re_import(): void
    {
        $csv = $this->csvWith([
            ['2026-03-11', 'Office', 'Rent', 'PHP', '3000.00', '0', 'March gas', 'Released'],
        ]);

        $this->uploadCsv($csv);
        ExpenseRequest::where('agency_id', $this->agency->id)->update(['status' => 'cancelled']);

        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 expense request(s) imported', session('success'));
        $this->assertSame(2, $this->itemCount());
    }

    #[Test]
    public function agency_column_with_slash_in_header_is_read_as_particular(): void
    {
        // The agency's own sheet uses "Particular/Description" — the header must
        // map, otherwise every imported row loses its description.
        $csv = "Date,Charge,Account,Currency,Amount,Particular/Description\n"
            ."2026-03-11,Office,Rent,PHP,3000.00,JEREMIAH G LUNZAGA- GAS/RFID\n";

        $this->uploadCsv($csv)->assertSessionHas('success');

        $item = ExpenseRequestItem::whereHas('expenseRequest', fn ($q) => $q->where('agency_id', $this->agency->id))->firstOrFail();
        $this->assertSame('JEREMIAH G LUNZAGA- GAS/RFID', $item->particular);
    }
}
