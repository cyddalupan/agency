<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for the gulf accounts page report:
 *
 *  "how can we add sub-accounts? there is an edit and delete but no add button.
 *   also adding an account replaces the existing account on the list."
 *
 * Findings:
 *  1. The index page had no per-row "add sub-account" button — users were
 *     forced to reuse Edit (rename), which visually "replaced" an existing
 *     account on the list. Fix: expose an explicit per-row Add Sub button
 *     that links to create with the parent pre-selected.
 *  2. Creating an account never actually deletes/modifies existing rows
 *     (verified live on gulf) — pin that with a regression test.
 */
class AccountAddSubButtonTest extends TestCase
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
    public function index_shows_an_add_sub_button_per_main_row(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('accounts.index'));

        $response->assertOk()
            ->assertSee(route('accounts.create', ['parent_id' => $main->id]), false)
            ->assertSee('Add Sub', false);
    }

    #[Test]
    public function create_page_preselects_parent_from_query_string(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('accounts.create', ['parent_id' => $main->id]));

        $response->assertOk()
            ->assertSee('selected', false) // a parent option is pre-selected
            ->assertSee('value="' . $main->id . '" selected', false);
    }

    #[Test]
    public function create_page_ignores_parent_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherMain = Account::factory()->create([
            'agency_id' => $otherAgency->id,
            'parent_id' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('accounts.create', ['parent_id' => $otherMain->id]));

        $response->assertOk()
            ->assertDontSee('value="' . $otherMain->id . '" selected', false);
    }

    #[Test]
    public function adding_an_account_never_replaces_existing_accounts(): void
    {
        $existing = [
            Account::factory()->create([
                'agency_id' => $this->agency->id,
                'name'      => 'Office Expenses',
                'type'      => 'expense',
                'parent_id' => null,
            ]),
            Account::factory()->create([
                'agency_id' => $this->agency->id,
                'name'      => 'Salaries',
                'type'      => 'expense',
                'parent_id' => null,
            ]),
        ];

        $this->actingAs($this->admin)
            ->post(route('accounts.store'), [
                'name'        => 'New Account',
                'type'        => 'expense',
                'charge_type' => 'office',
            ])
            ->assertRedirect(route('accounts.index'))
            ->assertSessionHas('success');

        // All pre-existing accounts are still there, untouched.
        foreach ($existing as $account) {
            $this->assertDatabaseHas('accounts', [
                'id'        => $account->id,
                'name'      => $account->name,
                'parent_id' => null,
            ]);
        }

        $this->assertDatabaseCount('accounts', 3);
        $this->assertDatabaseHas('accounts', ['name' => 'New Account']);
    }

    #[Test]
    public function adding_a_sub_account_keeps_the_main_account_list_intact(): void
    {
        $main = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'parent_id' => null,
        ]);
        Account::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Existing Main',
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

        // The main still exists as a top-level account; the sub nests under it.
        $this->assertDatabaseHas('accounts', ['id' => $main->id, 'parent_id' => null]);
        $this->assertDatabaseHas('accounts', [
            'name'      => 'Electric Bills',
            'parent_id' => $main->id,
        ]);
        $this->assertDatabaseCount('accounts', 3);
    }
}
