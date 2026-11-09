<?php

namespace Tests\Feature\AgentReportModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\StartingBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af174a1 — "LANDAS: Starting Balance 'Bulk Upload'".
 *
 * The card asks for a bulk upload with the header:
 *   Date, Account, Agent, Amount, Particular
 *
 * Rules mirrored from the single-entry form: one starting balance per agent,
 * account is fixed to "Starting Balance", agent must exist in the agency, and
 * amount must be > 0. All-or-nothing: one bad row blocks the whole file.
 */
class StartingBalanceBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    private const HEADER = 'Date,Account,Agent,Amount,Particular';

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeAgent(string $name): Agent
    {
        return Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => $name,
        ]);
    }

    private function uploadCsv(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->post(route('agent_report.starting_balance.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('starting-balances.csv', $content),
            ]);
    }

    /** Pull the csv_file error text out of whatever shape the session bag has. */
    private function errorMessage(\Illuminate\Testing\TestResponse $response): string
    {
        $bag = $response->getSession()->get('errors');
        $flat = json_decode(json_encode($bag), true);

        return implode(' ', (array) ($flat['default']['messages']['csv_file'] ?? []));
    }

    // ---------- Page + template ----------

    #[Test]
    public function the_bulk_page_renders_with_a_template_link(): void
    {
        $this->actingAs($this->user)
            ->get(route('agent_report.starting_balance.bulk'))
            ->assertOk()
            ->assertSee('Bulk Upload Starting Balance')
            ->assertSee(route('agent_report.starting_balance.bulk.template'), false);
    }

    #[Test]
    public function the_template_uses_the_header_from_the_card(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('agent_report.starting_balance.bulk.template'));

        $response->assertOk();

        $csv = $response->streamedContent();
        $firstLine = strtok($csv, "\n");

        $this->assertSame('Date,Account,Agent,Amount,Particular', trim($firstLine));
    }

    // ---------- Import ----------

    #[Test]
    public function it_imports_a_row_with_the_documented_header(): void
    {
        $this->makeAgent('Juan Dela Cruz');

        $this->uploadCsv(
            self::HEADER."\n"
            ."2026-11-09,Starting Balance,Juan Dela Cruz,\"5,000.00\",Opening balance\n"
        )->assertRedirect(route('agent_report.index', ['tab' => 'starting-balances']));

        $this->assertSame(1, StartingBalance::count());

        $sb = StartingBalance::first();
        $this->assertSame('Juan Dela Cruz', $sb->agent->name);
        $this->assertSame('Starting Balance', $sb->account);
        $this->assertEquals(5000.00, (float) $sb->amount);
        $this->assertSame('Opening balance', $sb->particular);
        $this->assertSame('2026-11-09', $sb->date->toDateString());
    }

    #[Test]
    public function a_blank_date_defaults_to_today(): void
    {
        $this->makeAgent('Maria Santos');

        $this->uploadCsv(self::HEADER."\n".",Starting Balance,Maria Santos,2500,\n")->assertRedirect();

        $this->assertSame(now()->toDateString(), StartingBalance::first()->date->toDateString());
    }

    #[Test]
    public function a_blank_account_is_accepted_and_forced_to_starting_balance(): void
    {
        $this->makeAgent('Maria Santos');

        $this->uploadCsv("Date,Account,Agent,Amount,Particular\n, ,Maria Santos,2500,\n")->assertRedirect();

        $this->assertSame('Starting Balance', StartingBalance::first()->account);
    }

    #[Test]
    public function a_wrong_account_value_blocks_the_row(): void
    {
        $this->makeAgent('Maria Santos');

        $response = $this->uploadCsv("Date,Account,Agent,Amount,Particular\n2026-11-09,Placement Fee,Maria Santos,2500,\n");

        $response->assertSessionHasErrors('csv_file');
        $this->assertStringContainsString('not allowed here', json_encode($response->getSession()->get('bulk_errors')));
        $this->assertSame(0, StartingBalance::count());
    }

    #[Test]
    public function an_unknown_agent_blocks_the_row(): void
    {
        $response = $this->uploadCsv(self::HEADER."\n2026-11-09,Starting Balance,Nobody Here,2500,\n");

        $response->assertSessionHasErrors('csv_file');
        $this->assertSame(0, StartingBalance::count());
        $this->assertStringContainsString('Nobod', json_encode($response->getSession()->get('bulk_errors')));
    }

    #[Test]
    public function an_agent_that_already_has_a_starting_balance_is_rejected(): void
    {
        $agent = $this->makeAgent('Juan Dela Cruz');
        StartingBalance::create([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->user->id,
            'agent_id'  => $agent->id,
            'date'      => now()->toDateString(),
            'account'   => StartingBalance::ACCOUNT,
            'amount'    => 1000,
        ]);

        $response = $this->uploadCsv(self::HEADER."\n2026-11-09,Starting Balance,Juan Dela Cruz,2500,\n");

        $response->assertSessionHasErrors('csv_file');
        $this->assertStringContainsString('already has a starting balance', json_encode($response->getSession()->get('bulk_errors')));
        $this->assertSame(1, StartingBalance::count());
    }

    #[Test]
    public function the_same_agent_twice_in_one_file_is_rejected(): void
    {
        $this->makeAgent('Juan Dela Cruz');

        $response = $this->uploadCsv(
            self::HEADER."\n"
            ."2026-11-09,Starting Balance,Juan Dela Cruz,2500,\n"
            ."2026-11-09,Starting Balance,Juan Dela Cruz,3000,\n"
        );

        $response->assertSessionHasErrors('csv_file');
        $this->assertStringContainsString('more than once in this file', json_encode($response->getSession()->get('bulk_errors')));
        $this->assertSame(0, StartingBalance::count(), 'All-or-nothing: nothing is saved when a row fails.');
    }

    #[Test]
    public function a_zero_or_negative_amount_is_rejected(): void
    {
        $this->makeAgent('Juan Dela Cruz');

        $this->uploadCsv(self::HEADER."\n2026-11-09,Starting Balance,Juan Dela Cruz,0,\n")
            ->assertSessionHasErrors('csv_file');

        $this->uploadCsv(self::HEADER."\n2026-11-09,Starting Balance,Juan Dela Cruz,-50,\n")
            ->assertSessionHasErrors('csv_file');

        $this->assertSame(0, StartingBalance::count());
    }

    #[Test]
    public function column_order_does_not_matter(): void
    {
        $this->makeAgent('Juan Dela Cruz');

        $this->uploadCsv(
            "Agent,Particular,Amount,Date,Account\n"
            ."Juan Dela Cruz,Reordered columns,5000,2026-11-09,Starting Balance\n"
        )->assertRedirect();

        $this->assertSame('Reordered columns', StartingBalance::first()->particular);
    }

    #[Test]
    public function an_unrecognised_description_column_is_refused(): void
    {
        $this->makeAgent('Juan Dela Cruz');

        $response = $this->uploadCsv(
            "Date,Account,Agent,Amount,Opening Memo\n"
            ."2026-11-09,Starting Balance,Juan Dela Cruz,5000,should not be dropped\n"
        );

        $response->assertSessionHasErrors('csv_file');
        $this->assertSame(0, StartingBalance::count());
    }
}
