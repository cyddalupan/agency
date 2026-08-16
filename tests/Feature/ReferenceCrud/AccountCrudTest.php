<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Account;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountCrudTest extends TestCase
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
    public function unauthenticated_user_cannot_access_accounts(): void
    {
        $this->get(route('accounts.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_accounts(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)->get(route('accounts.index'))->assertForbidden(403);
    }

    // ---------- Index / listing ----------

    #[Test]
    public function create_page_renders_with_main_account_options(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Income',
            'type'      => 'income',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.create'))
            ->assertOk()
            ->assertSee('Income');
    }

    #[Test]
    public function edit_page_renders_for_own_agency_account(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.edit', $main))
            ->assertOk()
            ->assertSee('Office Expenses');
    }

    #[Test]
    public function index_lists_only_own_agencies_accounts(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $otherAgency = Agency::factory()->create();
        $otherMain = Account::factory()->create([
            'agency_id' => $otherAgency->id,
            'name'      => 'Secret Cost Center',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSee($main->name)
            ->assertDontSee($otherMain->name);
    }

    // ---------- Store ----------

    #[Test]
    public function store_creates_main_account_scoped_to_agency(): void
    {
        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name'        => 'Office Expenses',
                'type'        => 'expense',
                'charge_type' => 'office',
            ])
            ->assertRedirect(route('accounts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('accounts', [
            'agency_id'   => $this->agency->id,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
            'parent_id'   => null,
        ]);
    }

    #[Test]
    public function store_defaults_charge_type_to_office_when_omitted(): void
    {
        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name' => 'Default Charge',
                'type' => 'expense',
            ])
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'agency_id'   => $this->agency->id,
            'name'        => 'Default Charge',
            'charge_type' => 'office',
        ]);
    }

    #[Test]
    public function store_persists_agent_charge_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name'        => 'Agent Advances',
                'type'        => 'expense',
                'charge_type' => 'agent',
            ])
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'agency_id'   => $this->agency->id,
            'name'        => 'Agent Advances',
            'charge_type' => 'agent',
        ]);
    }

    #[Test]
    public function store_rejects_invalid_charge_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name'        => 'Bad Charge',
                'type'        => 'expense',
                'charge_type' => 'corporate',
            ])
            ->assertSessionHasErrors('charge_type');

        $this->assertDatabaseCount('accounts', 0);
    }

    #[Test]
    public function update_changes_charge_type(): void
    {
        $account = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'name'        => 'Advances',
            'type'        => 'expense',
            'charge_type' => 'office',
            'parent_id'   => null,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('accounts.update', $account), [
                'name'        => 'Advances',
                'type'        => 'expense',
                'charge_type' => 'agent',
            ])
            ->assertRedirect(route('accounts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('accounts', [
            'id'          => $account->id,
            'charge_type' => 'agent',
        ]);
    }

    #[Test]
    public function create_page_shows_charge_type_field(): void
    {
        $this->actingAs($this->admin)
            ->get(route('accounts.create'))
            ->assertOk()
            ->assertSee('Charge Type')
            ->assertSee('value="agent"', false);
    }

    #[Test]
    public function store_creates_sub_account_under_main(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name'      => 'Electric Bills',
                'type'      => 'expense',
                'parent_id' => $main->id,
            ])
            ->assertRedirect(route('accounts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('accounts', [
            'agency_id' => $this->agency->id,
            'name'      => 'Electric Bills',
            'parent_id' => $main->id,
        ]);
    }

    #[Test]
    public function store_requires_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('accounts.store'), ['type' => 'expense'])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_requires_valid_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name' => 'Bad Type',
                'type' => 'not-a-real-type',
            ])
            ->assertSessionHasErrors('type');
    }

    #[Test]
    public function sub_account_must_belong_to_same_agency_as_parent(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherMain = Account::factory()->create([
            'agency_id' => $otherAgency->id,
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name'      => 'Hijacked Sub',
                'type'      => 'expense',
                'parent_id' => $otherMain->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('accounts', ['name' => 'Hijacked Sub']);
    }

    // ---------- Update ----------

    #[Test]
    public function update_changes_account(): void
    {
        $account = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Old Name',
            'type'      => 'income',
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->put(route('accounts.update', $account), [
                'name' => 'New Name',
                'type' => 'expense',
            ])
            ->assertRedirect(route('accounts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('accounts', [
            'id'   => $account->id,
            'name' => 'New Name',
            'type' => 'expense',
        ]);
    }

    // ---------- Destroy ----------

    #[Test]
    public function destroy_deletes_account(): void
    {
        $account = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('accounts.destroy', $account))
            ->assertRedirect(route('accounts.index'));

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    #[Test]
    public function destroy_main_with_children_is_blocked(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'parent_id' => null,
        ]);
        Account::factory()->create([
            'agency_id' => $this->agency->id,
            'parent_id' => $main->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('accounts.destroy', $main))
            ->assertSessionHasErrors('children');

        $this->assertDatabaseHas('accounts', ['id' => $main->id]);
    }

    // ---------- Cross-agency isolation ----------

    #[Test]
    public function user_cannot_modify_another_agencys_account(): void
    {
        $otherAgency = Agency::factory()->create();
        $account = Account::factory()->create([
            'agency_id' => $otherAgency->id,
            'parent_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->get(route('accounts.edit', $account))
            ->assertForbidden(403);

        $this->actingAs($this->admin)
            ->put(route('accounts.update', $account), ['name' => 'Hacked', 'type' => 'expense'])
            ->assertForbidden(403);

        $this->actingAs($this->admin)
            ->delete(route('accounts.destroy', $account))
            ->assertForbidden(403);
    }
}
