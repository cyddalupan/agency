<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchCrudTest extends TestCase
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
    public function unauthenticated_user_cannot_access_branches(): void
    {
        $this->get(route('branches.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_branches(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)->get(route('branches.index'))->assertForbidden(403);
    }

    #[Test]
    public function index_lists_only_own_agencies_branches(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $otherAgency = Agency::factory()->create();
        $otherBranch = Branch::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->admin)
            ->get(route('branches.index'))
            ->assertOk()
            ->assertSee($branch->name)
            ->assertDontSee($otherBranch->name);
    }

    #[Test]
    public function store_creates_branch_scoped_to_agency(): void
    {
        $this->actingAs($this->admin)
            ->post(route('branches.store'), [
                'name'    => 'Manila Branch',
                'address' => '123 Rizal Ave',
                'contact' => '0917-123-4567',
            ])
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'agency_id' => $this->agency->id,
            'name'      => 'Manila Branch',
            'address'   => '123 Rizal Ave',
        ]);
    }

    #[Test]
    public function store_requires_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('branches.store'), [])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function update_changes_branch(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->put(route('branches.update', $branch), [
                'name'    => 'Cebu Branch',
                'address' => '456 Colon St',
                'contact' => '0922-555-9999',
                'status'  => 'inactive',
            ])
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'id'    => $branch->id,
            'name'  => 'Cebu Branch',
            'status' => 'inactive',
        ]);
    }

    #[Test]
    public function destroy_deletes_branch(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->delete(route('branches.destroy', $branch))
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    #[Test]
    public function user_cannot_modify_another_agencys_branch(): void
    {
        $otherAgency = Agency::factory()->create();
        $branch = Branch::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->admin)
            ->get(route('branches.edit', $branch))
            ->assertForbidden(403);

        $this->actingAs($this->admin)
            ->put(route('branches.update', $branch), ['name' => 'Hacked'])
            ->assertForbidden(403);

        $this->actingAs($this->admin)
            ->delete(route('branches.destroy', $branch))
            ->assertForbidden(403);
    }
}
