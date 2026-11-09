<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card VrkCQ97s — Expenses transaction "Wrong post".
 *
 * The Receivable bulk uploader only required Agent + Amount, so an *Expense*
 * sheet (which also has those two columns) was accepted here and silently
 * became receivables — Charge, Payment, Currency, Office and Request Status
 * were all dropped without a word. The user saw a green success message and
 * discovered later that the transactions had been posted as Receivable.
 *
 * The Expense uploader already refuses a receivable sheet (it requires Charge
 * + Account). This test covers the matching guard on the Receivable side.
 */
class ReceivableRejectsExpenseSheetTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    private const EXPENSE_HEADER = 'Ref #,User,Date,Charge,Account,Applicant,Agent,Country,Currency,Amount,Payment,Particular/Description,Office,Request Status';

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Juan Dela Cruz',
            'email'     => 'juan@example.com',
        ]);
    }

    private function uploadCsv(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->post(route('receivable.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('receivables.csv', $content),
            ]);
    }

    /** Pull the csv_file error text out of whatever shape the session bag has. */
    private function errorMessage(\Illuminate\Testing\TestResponse $response): string
    {
        $bag = $response->getSession()->get('errors');

        if (is_object($bag) && method_exists($bag, 'first')) {
            return (string) $bag->first('csv_file');
        }

        // Session errors live as {guard: {messages: {csv_file: [...]}}}.
        $flat = json_decode(json_encode($bag), true);

        return implode(' ', (array) ($flat['default']['messages']['csv_file'] ?? []));
    }

    #[Test]
    public function an_expense_sheet_is_refused_instead_of_becoming_receivables(): void
    {
        $response = $this->uploadCsv(
            self::EXPENSE_HEADER."\n"
            .",,2026-11-08,Office,Rent,,Juan Dela Cruz,,PHP,1000.00,0,Monthly office rent,,Pending\n"
        );

        $message = $this->errorMessage($response);
        $response->assertSessionHasErrors('csv_file');

        $this->assertStringContainsString('should be Expenses', $message);
        $this->assertStringContainsString('Expense sheet', $message);

        $this->assertSame(0, Receivable::count(), 'An expense sheet must never create receivables.');
    }

    #[Test]
    public function the_message_names_the_expense_column_and_the_right_page(): void
    {
        $response = $this->uploadCsv(
            self::EXPENSE_HEADER."\n"
            .",,2026-11-08,Agent,Commission,,Juan Dela Cruz,,PHP,2500.00,,Agent payout,,Pending\n"
        );

        $message = $this->errorMessage($response);

        $this->assertStringContainsString('Charge', $message);
        $this->assertStringContainsString('/expense-requests/bulk', $message);
    }

    #[Test]
    public function a_real_receivable_sheet_still_imports(): void
    {
        $this->uploadCsv(
            "Code #,Date,User,Status,Ref#/AR#,Applicant,Agent,Amount,Account,Particular,Mode,Type,Deposit Bank\n"
            .",2026-11-08,,Pending,AR-1001,,Juan Dela Cruz,15000.00,Placement Fee,Partial fee,GCash,Full Payment,Receivable\n"
        )->assertSessionHasNoErrors();

        $this->assertSame(1, Receivable::count());

        $receivable = Receivable::first();
        $this->assertSame('Placement Fee', $receivable->account);
        $this->assertSame('AR-1001', $receivable->ref_ar);
        $this->assertSame('Receivable', $receivable->debit_account);
    }

    #[Test]
    public function a_plain_agent_amount_sheet_is_still_accepted(): void
    {
        // Not every legitimate upload uses the full template; a bare
        // Date/Agent/Amount sheet must keep working.
        $this->uploadCsv("Date,Agent,Amount\n2026-11-08,Juan Dela Cruz,500\n")
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Receivable::count());
    }
}
