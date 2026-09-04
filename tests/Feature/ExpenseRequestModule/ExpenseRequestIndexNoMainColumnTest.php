<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Index table simplification (requested via Toybits):
 * the "Main" column was a leftover duplicate of "Charge"
 * (it rendered Office/Agent charge text) — remove it entirely.
 */
class ExpenseRequestIndexNoMainColumnTest extends TestCase
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

    #[Test]
    public function index_table_has_no_main_column(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => 'Supplies',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), [
                'branch_id' => $branch->id,
                'notes'     => null,
                'lines'     => [
                    [
                        'charge'          => 'office',
                        'sub_account_id'  => $sub->id,
                        'main_account_id' => $main->id,
                        'agent_id'        => null,
                        'applicant_id'    => null,
                        'country_id'      => $country->id,
                        'currency'        => 'PHP',
                        'amount'          => 1000.00,
                        'particular'      => 'Supplies',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('expense_requests', 1);

        $html = $this->actingAs($this->admin)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->getContent();

        // The Main column header is gone...
        $this->assertStringNotContainsString('<th>Main</th>', $html);
        // ...and so is its duplicate Office/Agent charge-text cell.
        $this->assertStringNotContainsString('<td>Office</td>', $html);
        $this->assertStringNotContainsString('<td>Agent</td>', $html);

        // The Charge column remains, with its lowercase charge badge.
        $this->assertStringContainsString('<th>Charge</th>', $html);
        $this->assertStringContainsString('office', $html);

        // Other key columns are intact.
        $this->assertStringContainsString('<th>Account</th>', $html);
        $this->assertStringContainsString('<th>Offices</th>', $html);
    }
}
