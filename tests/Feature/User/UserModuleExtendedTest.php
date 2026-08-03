<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserModuleExtendedTest extends TestCase
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
    public function create_form_shows_extended_fields_and_branch_dropdown(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Middle Name')
            ->assertSee('Surname')
            ->assertSee('Contact #')
            ->assertSee('Access Level')
            ->assertSee('Accounting')
            ->assertSee('Receptionist')
            ->assertSee('Processing')
            ->assertSee('Branch')
            ->assertSee($branch->name);
    }

    #[Test]
    public function store_persists_extended_fields_and_preset_role(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'        => 'Maria',
                'middle_name' => 'Santos',
                'surname'     => 'Reyes',
                'email'       => 'maria.reyes@test.com',
                'contact'     => '0917-123-4567',
                'password'    => 'password123',
                'password_confirmation' => 'password123',
                'user_type'   => 'billing', // "Accounting" preset
                'branch_id'   => $branch->id,
                'status'      => 'active',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email'       => 'maria.reyes@test.com',
            'name'        => 'Maria',
            'middle_name' => 'Santos',
            'surname'     => 'Reyes',
            'contact'     => '0917-123-4567',
            'branch_id'   => $branch->id,
            'user_type'   => 'billing',
            'agency_id'   => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_rejects_branch_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherBranch = Branch::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'        => 'Bad Branch',
                'email'       => 'badbranch@test.com',
                'password'    => 'password123',
                'password_confirmation' => 'password123',
                'user_type'   => 'staff',
                'branch_id'   => $otherBranch->id,
                'status'      => 'active',
            ])->assertSessionHasErrors('branch_id');
    }

    #[Test]
    public function store_rejects_unknown_user_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name'        => 'No Role',
                'email'       => 'norole@test.com',
                'password'    => 'password123',
                'password_confirmation' => 'password123',
                'user_type'   => 'hacker_role',
                'status'      => 'active',
            ])->assertSessionHasErrors('user_type');
    }

    #[Test]
    public function update_changes_extended_fields(): void
    {
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name'        => 'Juan',
                'middle_name' => 'Dela',
                'surname'     => 'Cruz',
                'email'       => $user->email,
                'contact'     => '0922-888-7777',
                'user_type'   => 'processor', // "Processing" preset
                'status'      => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'          => $user->id,
            'middle_name' => 'Dela',
            'surname'     => 'Cruz',
            'contact'     => '0922-888-7777',
            'user_type'   => 'processor',
        ]);
    }

    #[Test]
    public function legacy_granular_roles_still_valid_on_update(): void
    {
        // A recruiter (non-preset role) can still be edited without breaking.
        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'recruiter',
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user), [
                'name'      => $user->name,
                'email'     => $user->email,
                'user_type' => 'recruiter',
                'status'    => 'active',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'user_type' => 'recruiter']);
    }

    #[Test]
    public function access_labels_map_to_friendly_names(): void
    {
        $this->assertEquals('Accounting', User::accessLabel('billing'));
        $this->assertEquals('Receptionist', User::accessLabel('staff'));
        $this->assertEquals('Processing', User::accessLabel('processor'));
        $this->assertEquals('Recruiter', User::accessLabel('recruiter'));
    }

    #[Test]
    public function full_name_accessor_combines_parts(): void
    {
        $user = User::factory()->create([
            'name'        => 'Maria',
            'middle_name' => 'Santos',
            'surname'     => 'Reyes',
        ]);

        $this->assertEquals('Maria Santos Reyes', $user->full_name);
    }
}
