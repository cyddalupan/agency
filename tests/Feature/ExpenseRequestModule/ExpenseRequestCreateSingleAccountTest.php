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
 * Create page (requested via Toybits 2026-08-16):
 * - the sub-account picker is RESTORED (change #5); the item stores the
 *   chosen sub-account id
 * - the Main Account is no longer user-picked: it is auto-derived from the
 *   Charge (office -> office main, agent -> agent main)
 * - store() still accepts an explicit main_account_id for backwards compat
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
    public function create_page_has_the_sub_account_picker_not_the_main_picker(): void
    {
        $html = $this->createPageHtml();

        // The account-type picker is back (change #5)...
        $this->assertStringContainsString('Account Type', $html);
        $this->assertStringContainsString('lines[0][sub_account_id]', $html);

        // ...and the user-facing Main Account picker is gone (main comes from Charge).
        $this->assertStringNotContainsString('lines[0][main_account_id]', $html);
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

        $sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $main->id,
            'name'        => 'Office Supplies',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => 'office',
                    'sub_account_id'  => $sub->id,
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
        $this->assertSame($sub->id, (int) $item->account_id);
    }

    #[Test]
    public function store_rejects_a_line_without_main_account(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();

        // Only an applicant main exists — no office main to auto-resolve.
        $applicantMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'APPLICANT',
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);
        $applicantSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $applicantMain->id,
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => 'office',
                    'sub_account_id'  => $applicantSub->id,
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
        $agentSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $agentMain->id,
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'          => 'office', // office charge with agent main -> invalid
                    'sub_account_id'  => $agentSub->id,
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

        // Agent/Applicant share one compact grid row (no agent-only row).
        $this->assertStringContainsString('grid grid-cols-1 sm:grid-cols-2 gap-3', $html);
        $this->assertStringContainsString('lines[0][agent_id]', $html);
        $this->assertStringContainsString('lines[0][applicant_id]', $html);
    }
}
