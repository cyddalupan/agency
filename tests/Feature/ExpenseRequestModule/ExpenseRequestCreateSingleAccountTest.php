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
 * Create page simplification (requested via Toybits):
 * - the "Account" (sub-account) field is removed; "Main Account" is the
 *   single account picker
 * - the line form is re-aligned into compact 3-column rows so the page
 *   is not too tall
 * - store() no longer needs account_id: the item's account IS the selected
 *   Main Account (schema keeps account_id NOT NULL)
 */
class ExpenseRequestCreateSingleAccountTest extends TestCase
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

    private function createPageHtml(): string
    {
        return $this->actingAs($this->admin)
            ->get(route('expense_request.create'))
            ->assertOk()
            ->getContent();
    }

    #[Test]
    public function create_page_has_only_the_main_account_picker(): void
    {
        $html = $this->createPageHtml();

        // Main Account remains the single picker...
        $this->assertStringContainsString('Main Account', $html);
        $this->assertStringContainsString('lines[0][main_account_id]', $html);

        // ...and the sub-account "Account" field is gone.
        $this->assertStringNotContainsString('lines[0][account_id]', $html);
        $this->assertStringNotContainsString('— Select Account —', $html);
        $this->assertStringNotContainsString('data-account-group', $html);
        $this->assertStringNotContainsString('Account shows Sub Accounts', $html);
    }

    #[Test]
    public function create_page_lists_main_accounts_for_picking(): void
    {
        $main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $html = $this->createPageHtml();

        $this->assertStringContainsString($main->name, $html);
        $this->assertStringContainsString('data-offset="' . $main->charge_type . '"', $html);
    }

    #[Test]
    public function store_accepts_a_line_without_account_id(): void
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

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => 'office',
                    'main_account_id' => $main->id,
                    // NOTE: no account_id submitted — the field is removed.
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => $country->id,
                    'currency'        => 'PHP',
                    'amount'          => 1000.00,
                    'particular'      => 'Office supplies',
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('expense_requests', 1);
        $item = ExpenseRequest::first()->items->first();
        $this->assertSame($main->id, (int) $item->account_id);
    }

    #[Test]
    public function store_rejects_a_line_without_main_account(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => 'office',
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => $country->id,
                    'currency'        => 'PHP',
                    'amount'          => 1000.00,
                    'particular'      => 'No account',
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.main_account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function store_rejects_main_account_whose_charge_type_mismatches(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $agentMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => 'office', // office charge with agent main -> invalid
                    'main_account_id' => $agentMain->id,
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => $country->id,
                    'currency'        => 'PHP',
                    'amount'          => 1000.00,
                    'particular'      => 'Mismatch',
                ],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.main_account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    #[Test]
    public function line_form_is_compact_without_wide_account_cell(): void
    {
        $html = $this->createPageHtml();

        // The old sub-account field used sm:col-span-2; the compact form has none.
        $this->assertStringNotContainsString('sm:col-span-2', $html);

        // Particular sits inside a grid row next to the account/country fields.
        $this->assertStringContainsString('lines[0][particular]', $html);

        // Agent/Applicant row is still present (hidden for office charge).
        $this->assertStringContainsString('data-agent-row', $html);
        $this->assertStringContainsString('lines[0][agent_id]', $html);
        $this->assertStringContainsString('lines[0][applicant_id]', $html);
    }
}
