<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16: the "Main Account *" dropdown on the
 * expense-request create page is redundant — the Charge dropdown
 * (office/agent) already determines the account type. Remove the dropdown;
 * server-side auto-resolves the Main account from the charge.
 */
class ExpenseRequestMainAccountRemovedTest extends TestCase
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
    }

    #[Test]
    public function create_page_has_no_main_account_dropdown(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.create'));

        $response->assertOk();
        $response->assertDontSee('Main Account');
        // The dropdown's marker attribute only existed on the removed select;
        // a dead JS selector string in create.blade.php is a harmless no-op.
        $response->assertDontSee('data-main-group');
    }

    #[Test]
    public function store_resolves_office_main_account_from_charge_when_omitted(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $officeMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => 'Auto-resolve test',
            'lines'     => [
                [
                    'charge'          => 'office',
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => null,
                    'currency'        => 'PHP',
                    'amount'          => 250.00,
                    'particular'      => 'Office supplies',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('expense_request_items', [
            'charge'     => 'office',
            'account_id' => $officeMain->id,
        ]);

        $this->assertSame(1, ExpenseRequest::count());
    }

    #[Test]
    public function store_resolves_agent_main_account_from_charge_when_omitted(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $agentMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'lines'     => [
                [
                    'charge'          => 'agent',
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => null,
                    'currency'        => 'PHP',
                    'amount'          => 100.00,
                    'particular'      => 'Agent advance',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('expense_request_items', [
            'charge'     => 'agent',
            'account_id' => $agentMain->id,
        ]);
    }

    #[Test]
    public function store_still_accepts_an_explicit_main_account_id(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $officeMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'lines'     => [
                [
                    'charge'          => 'office',
                    'main_account_id' => $officeMain->id,
                    'agent_id'        => null,
                    'applicant_id'    => null,
                    'country_id'      => null,
                    'currency'        => 'PHP',
                    'amount'          => 50.00,
                    'particular'      => 'Explicit account',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('expense_request_items', [
            'charge'     => 'office',
            'account_id' => $officeMain->id,
        ]);
    }
}
