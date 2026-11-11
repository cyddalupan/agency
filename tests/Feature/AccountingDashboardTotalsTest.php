<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af3ea66 — "LANDAS: Accounting Dashboards 'TOTAL AMOUNT'".
 *
 * "Expense and Payment module - display only the amount of Released on the
 *  PHP TOTAL.  Receivable module - display only the amount of Received on the
 *  TOTAL AMOUNT."
 *
 * Both dashboards used to sum every status into the headline figure.
 */
class AccountingDashboardTotalsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Branch $branch;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'gulf']);
        $this->branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Manila',
        ]);
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'super_admin',
        ]);

        Cache::put('fx_usd_to_php', 56.0, 3600);

        $this->account = Account::create([
            'agency_id'   => $this->agency->id,
            'name'        => 'Office expense',
            'type'        => 'expense',
            'charge_type' => 'office',
            'parent_id'   => null,
        ]);
    }

    private function expense(string $status, float $amount, string $currency = 'PHP'): void
    {
        $er = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'reference_no' => (string) random_int(100000, 999999),
            'date'         => now()->toDateString(),
            'status'       => $status,
            'branch_id'    => $this->branch->id,
            'notes'        => 'test',
        ]);

        ExpenseRequestItem::create([
            'expense_request_id' => $er->id,
            'charge'             => 'office',
            'currency'           => $currency,
            'amount'             => $amount,
            'payment'            => 0,
            'account_id'         => $this->account->id,
            'particular'         => 'test',
        ]);
    }

    private function receivable(string $status, float $amount): void
    {
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $this->branch->id,
        ]);

        $applicant = Applicant::factory()->withStatus(0)->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
        ]);

        Receivable::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->user->id,
            'agent_id'     => $agent->id,
            'applicant_id' => $applicant->id,
            'code'         => str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'date'         => now()->toDateString(),
            'status'       => $status,
            'amount'       => $amount,
        ]);
    }

    #[Test]
    public function expense_php_total_shows_released_only(): void
    {
        $this->expense(ExpenseRequest::STATUS_RELEASED, 50000.00);
        $this->expense(ExpenseRequest::STATUS_PENDING, 12345.00);
        $this->expense(ExpenseRequest::STATUS_APPROVED, 777.00);

        $html = $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // Assert the HEADLINE figure, not the whole page: the "🏢 Office" line
        // below still sums every office-charged amount (63,122 here), which is
        // correct and must not be mistaken for the total.
        $this->assertSame(
            '50,000.00',
            $this->headlineFigure($html, 'PHP Total'),
            'The PHP Total headline must show the RELEASED amount only, not the summed 63,122.00.'
        );
    }

    #[Test]
    public function expense_php_total_labels_itself_as_released(): void
    {
        $this->expense(ExpenseRequest::STATUS_RELEASED, 1000.00);

        $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('PHP Total', false)
            ->assertSee('(Released)', false);
    }

    #[Test]
    public function receivable_total_amount_shows_received_only(): void
    {
        $this->receivable(Receivable::STATUS_RECEIVED, 8000.00);
        $this->receivable(Receivable::STATUS_PENDING, 2000.00);

        $html = $this->actingAs($this->user)
            ->get(route('receivable.index'))
            ->assertOk()
            ->getContent();

        // Headline = received (8,000). The Pending line below still shows 2,000
        // and is not part of the headline, so match the headline element only.
        $this->assertSame(
            '8,000.00',
            $this->headlineFigure($html, 'Total Amount'),
            'The Total Amount headline must show the RECEIVED amount only, not the combined 10,000.00.'
        );
    }

    #[Test]
    public function receivable_total_amount_labels_itself_as_received(): void
    {
        $this->receivable(Receivable::STATUS_RECEIVED, 500.00);

        $this->actingAs($this->user)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('Total Amount', false)
            ->assertSee('(Received)', false);
    }

    #[Test]
    public function receivable_still_shows_pending_in_its_own_line(): void
    {
        $this->receivable(Receivable::STATUS_RECEIVED, 8000.00);
        $this->receivable(Receivable::STATUS_PENDING, 2000.00);

        $this->actingAs($this->user)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee('Pending', false)
            ->assertSee('2,000.00', false);
    }

    #[Test]
    public function expense_status_breakdown_still_shows_every_status(): void
    {
        $this->expense(ExpenseRequest::STATUS_RELEASED, 50000.00);
        $this->expense(ExpenseRequest::STATUS_PENDING, 12345.00);

        $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('Released', false)
            ->assertSee('Pending', false)
            ->assertSee('12,345.00', false);
    }

    /** Pulls the peso figure rendered in the big headline right after a label. */
    private function headlineFigure(string $html, string $label): ?string
    {
        if (! preg_match('/'.preg_quote($label, '/').'.*?₱\s*([\d,\.]+)/s', $html, $m)) {
            return null;
        }

        return trim($m[1]);
    }
}
