<?php

namespace Tests\Feature\ReceivableModule;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-11-04 card — Search bar on the Receivable module.
 *
 * One free-text box on the index must return every transaction related to what
 * the user typed: code / AR#, agent, applicant, particular, account, mode,
 * type, deposit account, status, encoder, date and amount. A blank box (or no
 * ?q at all) keeps the old behaviour — every visible row.
 */
class ReceivableSearchTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    private function makeReceivable(array $overrides = []): Receivable
    {
        return Receivable::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'user_id'   => $this->admin->id,
        ], $overrides));
    }

    #[Test]
    public function blank_search_shows_every_row(): void
    {
        $a = $this->makeReceivable(['code' => 'QA0001', 'particular' => 'Alpha placement']);
        $b = $this->makeReceivable(['code' => 'QB0002', 'particular' => 'Bravo placement']);

        $this->actingAs($this->admin)
            ->get(route('receivable.index'))
            ->assertOk()
            ->assertSee($a->code)
            ->assertSee($b->code)
            ->assertSee('name="q"', false);
    }

    #[Test]
    public function search_matches_the_agent_name(): void
    {
        $agentA = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'ZARA AGENT']);
        $agentB = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'QUENTIN AGENT']);

        $a = $this->makeReceivable(['code' => 'QA0001', 'agent_id' => $agentA->id]);
        $b = $this->makeReceivable(['code' => 'QB0002', 'agent_id' => $agentB->id]);

        $this->actingAs($this->admin)
            ->get(route('receivable.index', ['q' => 'ZARA']))
            ->assertOk()
            ->assertSee($a->code)
            ->assertDontSee($b->code);
    }

    #[Test]
    public function search_matches_the_applicant_name(): void
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id]);
        $appA = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
            'first_name' => 'Xiomara',
            'last_name'  => 'Villanueva',
        ]);
        $appB = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $agent->id,
            'first_name' => 'Bruno',
            'last_name'  => 'Santos',
        ]);

        $a = $this->makeReceivable(['code' => 'QA0001', 'agent_id' => $agent->id, 'applicant_id' => $appA->id]);
        $b = $this->makeReceivable(['code' => 'QB0002', 'agent_id' => $agent->id, 'applicant_id' => $appB->id]);

        $this->actingAs($this->admin)
            ->get(route('receivable.index', ['q' => 'Xiomara']))
            ->assertOk()
            ->assertSee($a->code)
            ->assertDontSee($b->code);
    }

    #[Test]
    public function search_matches_reference_particular_account_and_amount(): void
    {
        $a = $this->makeReceivable([
            'code'        => 'QA0001',
            'ref_ar'      => 'AR-778899',
            'particular'  => 'Visa processing fee',
            'account'     => 'Processing',
            'amount'      => 4321.50,
        ]);
        $b = $this->makeReceivable([
            'code'        => 'QB0002',
            'ref_ar'      => 'AR-112233',
            'particular'  => 'Medical exam',
            'account'     => 'Medical',
            'amount'      => 999.00,
        ]);

        foreach (['AR-778899', 'Visa processing', 'Processing', '4321'] as $term) {
            $this->actingAs($this->admin)
                ->get(route('receivable.index', ['q' => $term]))
                ->assertOk()
                ->assertSee($a->code)
                ->assertDontSee($b->code);
        }
    }

    #[Test]
    public function search_is_case_insensitive_and_composes_with_the_encoder_filter(): void
    {
        $encoder = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'name'      => 'Encoder Echo',
        ]);

        $mine = $this->makeReceivable([
            'code' => 'QA0001', 'user_id' => $encoder->id, 'particular' => 'Airfare ticket',
        ]);
        $other = $this->makeReceivable(['code' => 'QB0002', 'particular' => 'Airfare ticket']);

        $this->actingAs($this->admin)
            ->get(route('receivable.index', ['q' => 'airFARE', 'encoder' => $encoder->id]))
            ->assertOk()
            ->assertSee($mine->code)
            ->assertDontSee($other->code)
            // The typed term is kept in the box and the encoder choice survives.
            ->assertSee('value="airFARE"', false)
            ->assertSee('name="encoder"', false);
    }

    #[Test]
    public function search_never_leaks_another_agencys_rows(): void
    {
        $otherAgency = Agency::factory()->create();
        $foreign = Receivable::factory()->create([
            'agency_id' => $otherAgency->id,
            'code'      => 'QF9999',
            'particular' => 'Hidden foreign row',
        ]);

        $this->actingAs($this->admin)
            ->get(route('receivable.index', ['q' => 'Hidden foreign']))
            ->assertOk()
            ->assertDontSee($foreign->code)
            ->assertSee('No transactions match', false);
    }
}
