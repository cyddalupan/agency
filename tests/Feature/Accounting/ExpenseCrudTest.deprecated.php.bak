<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseCrudTest extends TestCase
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

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_expenses(): void
    {
        $this->get(route('expenses.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_expenses(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)->get(route('expenses.index'))->assertForbidden(403);
    }

    // ---------- Index / listing ----------

    #[Test]
    public function create_page_renders_with_account_options(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('expenses.create'))
            ->assertOk()
            ->assertSee('Office Expenses');
    }

    #[Test]
    public function edit_page_renders_for_own_agency_expense(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);
        $expense = Expense::factory()->create([
            'agency_id' => $this->agency->id,
            'account_id' => $account->id,
            'payee' => 'Meralco',
        ]);

        $this->actingAs($this->admin)
            ->get(route('expenses.edit', $expense))
            ->assertOk()
            ->assertSee('Meralco');
    }

    #[Test]
    public function index_lists_only_own_agencies_expenses(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);
        $expense = Expense::factory()->create([
            'agency_id' => $this->agency->id,
            'account_id' => $account->id,
            'payee' => 'Meralco',
        ]);

        $otherAgency = Agency::factory()->create();
        $otherAccount = Account::factory()->create(['agency_id' => $otherAgency->id]);
        $otherExpense = Expense::factory()->create([
            'agency_id' => $otherAgency->id,
            'account_id' => $otherAccount->id,
            'payee' => 'Secret Vendor',
        ]);

        $this->actingAs($this->admin)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee($expense->payee)
            ->assertDontSee($otherExpense->payee);
    }

    #[Test]
    public function index_can_filter_by_account(): void
    {
        $accountA = Account::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Rent']);
        $accountB = Account::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Supplies']);
        $rent = Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $accountA->id, 'payee' => 'Landlord']);
        $supply = Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $accountB->id, 'payee' => 'SM Stationers']);

        $this->actingAs($this->admin)
            ->get(route('expenses.index', ['account_id' => $accountA->id]))
            ->assertOk()
            ->assertSee('Landlord')
            ->assertDontSee('SM Stationers');
    }

    // ---------- Store ----------

    #[Test]
    public function store_creates_expense_scoped_to_agency(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'account_id'   => $account->id,
                'amount'       => 1500.50,
                'date'         => '2026-08-01',
                'payee'        => 'Meralco',
                'method'       => 'bank_transfer',
                'reference_no' => 'REF-100',
                'notes'        => 'August electric bill',
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'agency_id' => $this->agency->id,
            'account_id' => $account->id,
            'amount'     => 1500.5,
            'date'       => '2026-08-01 00:00:00',
            'payee'      => 'Meralco',
            'user_id'    => $this->admin->id,
        ]);
    }

    #[Test]
    public function store_requires_amount(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'account_id' => $account->id,
                'date'       => '2026-08-01',
                'payee'      => 'Meralco',
            ])
            ->assertSessionHasErrors('amount');
    }

    #[Test]
    public function store_requires_numeric_positive_amount(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'account_id' => $account->id,
                'amount'     => -50,
                'date'       => '2026-08-01',
                'payee'      => 'Meralco',
            ])
            ->assertSessionHasErrors('amount');
    }

    #[Test]
    public function store_requires_account_and_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'amount' => 100,
                'payee'  => 'Meralco',
            ])
            ->assertSessionHasErrors(['account_id', 'date']);
    }

    #[Test]
    public function store_rejects_account_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAccount = Account::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'account_id' => $otherAccount->id,
                'amount'     => 100,
                'date'       => '2026-08-01',
                'payee'      => 'Meralco',
            ])
            ->assertSessionHasErrors('account_id');

        $this->assertDatabaseMissing('expenses', ['payee' => 'Meralco']);
    }

    // ---------- Update ----------

    #[Test]
    public function update_changes_expense(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);
        $expense = Expense::factory()->create([
            'agency_id' => $this->agency->id,
            'account_id' => $account->id,
            'payee' => 'Old Vendor',
            'amount' => 100,
        ]);

        $this->actingAs($this->admin)
            ->put(route('expenses.update', $expense), [
                'account_id'   => $account->id,
                'amount'       => 250,
                'date'         => '2026-08-02',
                'payee'        => 'New Vendor',
                'method'       => 'cash',
                'reference_no' => 'REF-200',
                'notes'        => 'Updated',
            ])
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'payee' => 'New Vendor', 'amount' => 250]);
    }

    // ---------- Destroy ----------

    #[Test]
    public function destroy_deletes_expense(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id]);
        $expense = Expense::factory()->create([
            'agency_id' => $this->agency->id,
            'account_id' => $account->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index'));

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    // ---------- Cross-agency isolation ----------

    #[Test]
    public function user_cannot_modify_another_agencys_expense(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAccount = Account::factory()->create(['agency_id' => $otherAgency->id]);
        $expense = Expense::factory()->create([
            'agency_id' => $otherAgency->id,
            'account_id' => $otherAccount->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('expenses.edit', $expense))
            ->assertForbidden(403);

        $this->actingAs($this->admin)
            ->delete(route('expenses.destroy', $expense))
            ->assertForbidden(403);
    }
}
