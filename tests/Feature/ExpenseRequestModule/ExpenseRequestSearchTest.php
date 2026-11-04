<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-04 card — Search bar on the Expenses & Payments module.
 *
 * The index accepts ?q=<text> and returns every transaction related to it:
 * the request header (ref#, date, encoder) and every line (agent, applicant,
 * particular, account, country, currency, amount, payment). It composes with
 * the status tabs and the encoder filter, and a blank box shows everything.
 */
class ExpenseRequestSearchTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Branch $branch;
    private Country $country;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->country = Country::factory()->create();
        $this->account = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'charge_type' => 'agent',
        ]);
    }

    private function makeRequest(
        string $reference,
        string $status = 'pending',
        ?array $line = null,
        ?int $userId = null
    ): ExpenseRequest {
        $request = ExpenseRequest::create([
            'agency_id'    => $this->agency->id,
            'user_id'      => $userId ?? $this->admin->id,
            'branch_id'    => $this->branch->id,
            'reference_no' => $reference,
            'date'         => now()->toDateString(),
            'status'       => $status,
        ]);

        if ($line !== null) {
            ExpenseRequestItem::create(array_merge([
                'expense_request_id' => $request->id,
                'branch_id'          => $this->branch->id,
                'charge'             => 'agent',
                'agent_id'           => Agent::factory()->create(['agency_id' => $this->agency->id])->id,
                'applicant_id'       => null,
                'country_id'         => $this->country->id,
                'currency'           => 'PHP',
                'amount'             => 1000.00,
                'payment'            => 0,
                'status'             => $status,
                'account_id'         => $this->account->id,
                'particular'         => 'Generic line',
            ], $line));
        }

        return $request;
    }

    #[Test]
    public function blank_search_shows_every_request(): void
    {
        $a = $this->makeRequest('EXPA0001');
        $b = $this->makeRequest('EXPB0002');

        $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee($a->reference_no)
            ->assertSee($b->reference_no)
            ->assertSee('name="q"', false);
    }

    #[Test]
    public function search_matches_the_reference_number(): void
    {
        $a = $this->makeRequest('EXPA0001');
        $b = $this->makeRequest('EXPB0002');

        $this->actingAs($this->admin)
            ->get(route('expense_request.index', ['q' => 'EXPA0001']))
            ->assertOk()
            ->assertSee($a->reference_no)
            ->assertDontSee($b->reference_no);
    }

    #[Test]
    public function search_matches_the_agent_and_applicant_on_a_transaction_line(): void
    {
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'ZARA AGENT']);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'QUENTIN AGENT']);
        $appA = Applicant::factory()->create([
            'agency_id' => $this->agency->id, 'agent_id' => $agentA->id,
            'first_name' => 'Xiomara', 'last_name' => 'Villanueva',
        ]);
        $appB = Applicant::factory()->create([
            'agency_id' => $this->agency->id, 'agent_id' => $agentB->id,
            'first_name' => 'Bruno', 'last_name' => 'Santos',
        ]);

        $a = $this->makeRequest('EXPA0001', 'pending', ['agent_id' => $agentA->id, 'applicant_id' => $appA->id]);
        $b = $this->makeRequest('EXPB0002', 'pending', ['agent_id' => $agentB->id, 'applicant_id' => $appB->id]);

        $this->actingAs($this->admin)
            ->get(route('expense_request.index', ['q' => 'Xiomara']))
            ->assertOk()
            ->assertSee($a->reference_no)
            ->assertDontSee($b->reference_no);

        $this->actingAs($this->admin)
            ->get(route('expense_request.index', ['q' => 'QUENTIN']))
            ->assertOk()
            ->assertSee($b->reference_no)
            ->assertDontSee($a->reference_no);
    }

    #[Test]
    public function search_matches_particular_account_amount_and_payment(): void
    {
        $a = $this->makeRequest('EXPA0001', 'released', [
            'particular' => 'Visa processing fee', 'amount' => 4321.50, 'payment' => 321.50,
        ]);
        $b = $this->makeRequest('EXPB0002', 'released', [
            'particular' => 'Medical exam', 'amount' => 999.00, 'payment' => 0,
        ]);

        foreach (['Visa processing', '4321', '321'] as $term) {
            $this->actingAs($this->admin)
                ->get(route('expense_request.index', ['q' => $term]))
                ->assertOk()
                ->assertSee($a->reference_no)
                ->assertDontSee($b->reference_no);
        }
    }

    #[Test]
    public function search_composes_with_the_status_tab_and_encoder_filter(): void
    {
        $encoder = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'name'      => 'Encoder Echo',
        ]);

        $mine = $this->makeRequest('EXPA0001', 'released', ['particular' => 'Airfare ticket'], $encoder->id);
        $pending = $this->makeRequest('EXPB0002', 'pending', ['particular' => 'Airfare ticket'], $encoder->id);
        $other = $this->makeRequest('EXPC0003', 'released', ['particular' => 'Airfare ticket']);

        $this->actingAs($this->admin)
            ->get(route('expense_request.index', [
                'q' => 'airFARE', 'status' => 'released', 'encoder' => $encoder->id,
            ]))
            ->assertOk()
            ->assertSee($mine->reference_no)
            ->assertDontSee($pending->reference_no)
            ->assertDontSee($other->reference_no)
            ->assertSee('value="airFARE"', false)
            ->assertSee('name="status" value="released"', false)
            ->assertSee('name="encoder" value="' . $encoder->id . '"', false);
    }

    #[Test]
    public function search_never_leaks_another_agencys_transactions(): void
    {
        $otherAgency = Agency::factory()->create();
        $foreign = ExpenseRequest::create([
            'agency_id'    => $otherAgency->id,
            'user_id'      => User::factory()->create(['agency_id' => $otherAgency->id])->id,
            'reference_no' => 'EXPF9999',
            'date'         => now()->toDateString(),
            'status'       => 'pending',
        ]);
        ExpenseRequestItem::create([
            'expense_request_id' => $foreign->id,
            'branch_id'          => null,
            'charge'             => 'agent',
            'agent_id'           => Agent::factory()->create(['agency_id' => $otherAgency->id])->id,
            'applicant_id'       => null,
            'country_id'         => $this->country->id,
            'currency'           => 'PHP',
            'amount'             => 777.00,
            'payment'            => 0,
            'status'             => 'pending',
            'account_id'         => Account::factory()->create(['agency_id' => $otherAgency->id])->id,
            'particular'         => 'Zanzibarian foreign line',
        ]);

        $this->actingAs($this->admin)
            ->get(route('expense_request.index', ['q' => 'Zanzibarian']))
            ->assertOk()
            ->assertDontSee($foreign->reference_no, false)
            ->assertSee('No transactions match', false);
    }
}
