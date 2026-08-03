<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PositionCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_positions(): void
    {
        $this->get(route('positions.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_positions(): void
    {
        $agency = Agency::factory()->create();
        $staff = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->get(route('positions.index'))->assertForbidden(403);
    }

    #[Test]
    public function index_lists_positions(): void
    {
        Position::factory()->create(['name' => 'Domestic Helper']);
        Position::factory()->create(['name' => 'Driver']);

        $this->actingAs($this->admin)
            ->get(route('positions.index'))
            ->assertOk()
            ->assertSee('Domestic Helper')
            ->assertSee('Driver');
    }

    #[Test]
    public function store_creates_position(): void
    {
        $this->actingAs($this->admin)
            ->post(route('positions.store'), ['name' => 'Houseboy'])
            ->assertRedirect(route('positions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('positions', ['name' => 'Houseboy']);
    }

    #[Test]
    public function store_requires_unique_name(): void
    {
        Position::factory()->create(['name' => 'Driver']);

        $this->actingAs($this->admin)
            ->post(route('positions.store'), ['name' => 'Driver'])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function update_changes_position(): void
    {
        $position = Position::factory()->create(['name' => 'Old Position']);

        $this->actingAs($this->admin)
            ->put(route('positions.update', $position), ['name' => 'New Position'])
            ->assertRedirect(route('positions.index'));

        $this->assertDatabaseHas('positions', ['id' => $position->id, 'name' => 'New Position']);
    }

    #[Test]
    public function destroy_deletes_position(): void
    {
        $position = Position::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('positions.destroy', $position))
            ->assertRedirect(route('positions.index'));

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }
}
