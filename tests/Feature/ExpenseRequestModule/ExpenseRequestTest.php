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

class ExpenseRequestTest extends TestCase
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

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_expense_request_module(): void
    {
        $this->get(route('expense_request.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_expense_request_module(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->actingAs($staff)
            ->get(route('expense_request.index'))
            ->assertForbidden();
    }

    // ---------- Create: parent + multi-line ----------

    #[Test]
    public function store_creates_parent_with_multiple_line_items(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Manila']);
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $country = Country::factory()->create();

        $officeAccount = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'parent_id' => null,
            'name'      => 'Salaries',
            'type'      => 'expense',
            'is_active' => true,
        ]);

        $agentAccount = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Agent Advances',
            'type'        => 'expense',
            'charge_type' => 'agent',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => 'Monthly expense run',
            'lines'     => [
                [
                    'charge'        => 'office',
                    'agent_id'      => null,
                    'applicant_id'  => null,
                    'country_id'    => $country->id,
                    'currency'      => 'PHP',
                    'amount'        => 12500.00,
                    'account_id'    => $officeAccount->id,
                    'particular'    => 'Office supplies',
                ],
                [
                    'charge'        => 'agent',
                    'agent_id'      => $agent->id,
                    'applicant_id'  => $applicant->id,
                    'country_id'    => $country->id,
                    'currency'      => 'USD',
                    'amount'        => 250.00,
                    'account_id'    => $agentAccount->id,
                    'particular'    => 'Agent advance for applicant',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('expense_requests', [
            'agency_id'      => $this->agency->id,
            'user_id'        => $this->user->id,
            'branch_id'      => $branch->id,
            'status'         => 'pending',
            'notes'          => 'Monthly expense run',
        ]);

        $request = ExpenseRequest::first();
        $this->assertNotNull($request->reference_no);

        $this->assertCount(2, $request->items);

        $first = $request->items->get(0);
        $this->assertSame('office', $first->charge);
        $this->assertSame('PHP', $first->currency);
        $this->assertSame(12500.00, (float) $first->amount);

        $this->assertCount(2, ExpenseRequestItem::all());
    }

    #[Test]
    public function expense_request_defaults_to_pending_status(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->payload($branch, $country, $account))
            ->assertRedirect();

        $this->assertSame('pending', ExpenseRequest::first()->status);
    }

    // ---------- Agency isolation ----------

    #[Test]
    public function store_is_isolated_by_agency(): void
    {
        $otherAgency = Agency::factory()->create();

        $branch = Branch::factory()->create(['agency_id' => $otherAgency->id]);
        $agent = Agent::factory()->create(['agency_id' => $otherAgency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $otherAgency->id, 'agent_id' => $agent->id]);
        $country = Country::factory()->create();
        $account = Account::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->payload($branch, $country, $account, [
                'lines' => [[
                    'charge'        => 'agent',
                    'agent_id'      => $agent->id,
                    'applicant_id'  => $applicant->id,
                    'country_id'    => $country->id,
                    'currency'      => 'PHP',
                    'amount'        => 500.00,
                    'account_id'    => $account->id,
                    'particular'    => 'Test',
                ]],
            ]))
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('expense_requests', 0);
    }

    // ---------- CoA gating (office vs agent) ----------

    #[Test]
    public function office_charge_only_allows_office_sub_accounts(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();

        $officeAccount = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'charge_type' => 'office',
        ]);
        $agentAccount = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'charge_type' => 'agent',
        ]);

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $this->payload($branch, $country, $officeAccount, [
                'lines' => [[
                    'charge'        => 'office',
                    'country_id'    => $country->id,
                    'currency'      => 'PHP',
                    'amount'        => 100.00,
                    'account_id'    => $agentAccount->id, // agent account with office charge -> invalid
                    'particular'    => 'Should fail',
                ]],
            ]))
            ->assertSessionHasErrors('lines.0.account_id');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    // ---------- Upload ----------

    #[Test]
    public function store_accepts_a_file_upload_per_line(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $country = Country::factory()->create();
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $payload = $this->payload($branch, $country, $account);
        $payload['lines'][0]['file'] = \Illuminate\Http\UploadedFile::fake()->image('receipt.png')->size(200);

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $item = ExpenseRequestItem::first();
        $this->assertNotNull($item->file_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($item->file_path);
    }

    #[Test]
    public function store_rejects_an_oversized_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);
        $country = Country::factory()->create();
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $payload = $this->payload($branch, $country, $account);
        $payload['lines'][0]['file'] = \Illuminate\Http\UploadedFile::fake()->image('big.png')->size(6000); // > 5120 KB

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasErrors('lines.0.file');

        $this->assertDatabaseCount('expense_requests', 0);
    }

    // ---------- Review: admin status change + history ----------

    #[Test]
    public function only_admin_can_change_expense_request_status(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $agentAccount = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $request = $this->createRequest($branch, $country, $agentAccount);

        $staff = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);
        $this->actingAs($staff)
            ->patch(route('expense_request.status', $request), ['status' => 'received'])
            ->assertForbidden();

        $this->assertSame('pending', $request->fresh()->status);
    }

    #[Test]
    public function admin_can_change_status_and_history_is_logged(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $agentAccount = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $request = $this->createRequest($branch, $country, $agentAccount);

        $this->actingAs($this->user) // admin
            ->patch(route('expense_request.status', $request), ['status' => 'received', 'note' => 'Docs verified'])
            ->assertRedirect();

        $this->assertSame('received', $request->fresh()->status);

        $this->assertDatabaseHas('expense_request_histories', [
            'expense_request_id' => $request->id,
            'agency_id'          => $this->agency->id,
            'from_status'        => 'pending',
            'to_status'          => 'received',
            'note'               => 'Docs verified',
        ]);
    }

    #[Test]
    public function histories_are_exposed_on_review_page(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $agentAccount = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $request = $this->createRequest($branch, $country, $agentAccount);

        $this->actingAs($this->user)
            ->patch(route('expense_request.status', $request), ['status' => 'received', 'note' => 'OK']);

        $this->actingAs($this->user)
            ->get(route('expense_request.show', $request))
            ->assertOk()
            ->assertSee('OK');
    }

    // ---------- Currency totals ----------

    #[Test]
    public function index_breaks_down_totals_by_currency(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create();
        $officeAccount = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'office']);
        $agentAccount = Account::factory()->create(['agency_id' => $this->agency->id, 'charge_type' => 'agent']);

        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), [
                'branch_id' => $branch->id,
                'lines'     => [
                    [
                        'charge'     => 'office',
                        'country_id' => $country->id,
                        'currency'   => 'PHP',
                        'amount'     => 1000.00,
                        'account_id' => $officeAccount->id,
                        'particular' => 'Office',
                    ],
                    [
                        'charge'        => 'agent',
                        'agent_id'      => $agent->id,
                        'applicant_id'  => $applicant->id,
                        'country_id'    => $country->id,
                        'currency'      => 'USD',
                        'amount'        => 50.00,
                        'account_id'    => $agentAccount->id,
                        'particular'    => 'Advance',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($this->user)
            ->get(route('expense_request.index'))
            ->assertOk()
            ->assertSee('₱1,000.00')
            ->assertSee('$50.00');
    }

    // ---------- Helpers ----------

    private function createRequest(Branch $branch, Country $country, Account $account): ExpenseRequest
    {
        $payload = $this->payload($branch, $country, $account);
        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload);

        return ExpenseRequest::firstOrFail();
    }


    private function payload(Branch $branch, Country $country, Account $account, array $overrides = []): array
    {
        $agent = Agent::factory()->create(['agency_id' => $this->agency->id, 'branch_id' => $branch->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id, 'agent_id' => $agent->id]);

        return array_merge([
            'branch_id' => $branch->id,
            'notes'     => null,
            'lines'     => [
                [
                    'charge'        => 'agent',
                    'agent_id'      => $agent->id,
                    'applicant_id'  => $applicant->id,
                    'country_id'    => $country->id,
                    'currency'      => 'PHP',
                    'amount'        => 1000.00,
                    'account_id'    => $account->id,
                    'particular'    => 'Advance',
                ],
            ],
        ], $overrides);
    }
}
