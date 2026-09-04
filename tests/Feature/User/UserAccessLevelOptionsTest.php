<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Access levels offered on the User create/edit forms (Mjolnir access levels).
 *
 * The Trello "User Access Level" card (updated 2026-09-03) lists Paralegal
 * and Branch as assignable levels. Option B: make them assignable NOW so
 * accounts can be created; permission wiring happens module-by-module after.
 */
class UserAccessLevelOptionsTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    private function admin(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ], $attrs));
    }

    // ---------- Create form options ----------

    #[Test]
    public function admin_create_form_offers_paralegal_and_branch_options(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('<option value="paralegal"', false)
            ->assertSee('<option value="branch"', false)
            ->assertSee('Paralegal', false)
            ->assertSee('Branch', false);
    }

    #[Test]
    public function super_admin_create_form_also_offers_paralegal_and_branch_options(): void
    {
        $super = User::factory()->create(['user_type' => 'super_admin']);

        $this->actingAs($super)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('<option value="paralegal"', false)
            ->assertSee('<option value="branch"', false);
    }

    // ---------- Store ----------

    #[Test]
    public function admin_can_create_a_paralegal_user(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'                  => 'Para Atty',
                'email'                 => 'paralegal@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'user_type'             => 'paralegal',
                'status'                => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email'     => 'paralegal@example.com',
            'user_type' => 'paralegal',
        ]);
    }

    #[Test]
    public function admin_can_create_a_branch_user_bound_to_a_branch(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'                  => 'Branch Mgr',
                'email'                 => 'branch@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'user_type'             => 'branch',
                'branch_id'             => $branch->id,
                'status'                => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email'     => 'branch@example.com',
            'user_type' => 'branch',
            'branch_id' => $branch->id,
        ]);
    }

    // ---------- Labels ----------

    #[Test]
    public function access_labels_map_for_new_levels(): void
    {
        $this->assertSame('Paralegal', User::accessLabel('paralegal'));
        $this->assertSame('Branch', User::accessLabel('branch'));
    }
}
