<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Only Super Admins may create/produce Super Admin users.
 *
 * An agency Admin can create other agency users (staff/processor/billing/…),
 * including binding them to a branch (a "branch account"), but must NEVER be
 * able to create or promote a user to super_admin.
 */
class UserRoleEscalationTest extends TestCase
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

    private function superAdmin(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'user_type' => 'super_admin',
        ], $attrs));
    }

    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'New User',
            'email'       => 'new.user@test.com',
            'password'    => 'password123',
            'password_confirmation' => 'password123',
            'user_type'   => 'staff',
            'status'      => 'active',
        ], $overrides);
    }

    // ─── CREATE: super_admin is super-admin-only ─────────────────────

    #[Test]
    public function admin_cannot_create_a_super_admin_user(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $this->storePayload(['user_type' => 'super_admin']));

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new.user@test.com']);
    }

    #[Test]
    public function super_admin_can_create_a_super_admin_user(): void
    {
        $super = $this->superAdmin();

        $response = $this->actingAs($super)
            ->post(route('users.store'), $this->storePayload(['user_type' => 'super_admin', 'email' => 'another.super@test.com']));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'another.super@test.com', 'user_type' => 'super_admin']);
    }

    #[Test]
    public function admin_can_create_a_non_super_admin_user(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $this->storePayload(['user_type' => 'staff', 'email' => 'staff@test.com']));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'staff@test.com']);
    }

    #[Test]
    public function admin_can_create_a_staff_user_bound_to_own_branch(): void
    {
        // This is the "create a branch user" flow: pick a staff/processor role + branch_id.
        $admin = $this->admin();
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $this->storePayload([
                'user_type' => 'staff',
                'branch_id' => $branch->id,
                'email'     => 'branch.staff@test.com',
            ]));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'branch.staff@test.com',
            'user_type' => 'staff',
            'branch_id' => $branch->id,
        ]);
        // A staff user bound to a branch IS a branch account (isBranchAccount).
        $created = User::where('email', 'branch.staff@test.com')->first();
        $this->assertTrue($created->isBranchAccount());
    }

    // ─── UPDATE: no escalation to super_admin ─────────────────────────

    #[Test]
    public function admin_cannot_promote_an_existing_user_to_super_admin(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.update', $target), $this->storePayload([
                'user_type' => 'super_admin',
                'email'     => $target->email,
            ]));

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'user_type' => 'staff']);
    }

    #[Test]
    public function super_admin_can_promote_a_user_to_super_admin(): void
    {
        $super = $this->superAdmin();
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($super)
            ->put(route('users.update', $target), $this->storePayload([
                'user_type' => 'super_admin',
                'email'     => $target->email,
            ]));

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $target->id, 'user_type' => 'super_admin']);
    }

    #[Test]
    public function admin_cannot_downgrade_an_existing_super_admin(): void
    {
        $admin = $this->admin();
        $super = $this->superAdmin();

        // Even hitting update on a super_admin target as an admin must be blocked,
        // because the policy already routes super_admin, but the role changing to
        // something non-super_admin must not be possible from an admin either.
        $response = $this->actingAs($admin)
            ->put(route('users.update', $super), $this->storePayload([
                'user_type' => 'admin',
                'email'     => $super->email,
            ]));

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $super->id, 'user_type' => 'super_admin']);
    }

    // ─── CREATE / EDIT FORM: hide super_admin option for admins ───────

    #[Test]
    public function admin_create_form_does_not_offer_super_admin_option(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertDontSee('<option value="super_admin"');
    }

    #[Test]
    public function super_admin_create_form_does_offer_super_admin_option(): void
    {
        $super = $this->superAdmin();

        $this->actingAs($super)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('<option value="super_admin"', false);
    }

    #[Test]
    public function admin_edit_form_does_not_offer_super_admin_option(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($admin)
            ->get(route('users.edit', $target))
            ->assertOk()
            ->assertDontSee('<option value="super_admin"');
    }
}
