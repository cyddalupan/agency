<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryCrudTest extends TestCase
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
    public function unauthenticated_user_cannot_access_countries(): void
    {
        $this->get(route('countries.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_countries(): void
    {
        $agency = Agency::factory()->create();
        $staff = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->get(route('countries.index'))->assertForbidden(403);
    }

    #[Test]
    public function index_lists_countries(): void
    {
        Country::factory()->create(['name' => 'Saudi Arabia']);
        Country::factory()->create(['name' => 'Qatar']);

        $this->actingAs($this->admin)
            ->get(route('countries.index'))
            ->assertOk()
            ->assertSee('Saudi Arabia')
            ->assertSee('Qatar');
    }

    #[Test]
    public function store_creates_country(): void
    {
        $this->actingAs($this->admin)
            ->post(route('countries.store'), [
                'name'        => 'United Arab Emirates',
                'code'        => 'AE',
                'nationality' => 'Emirati',
            ])
            ->assertRedirect(route('countries.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('countries', [
            'name'        => 'United Arab Emirates',
            'code'        => 'AE',
            'nationality' => 'Emirati',
        ]);
    }

    #[Test]
    public function store_requires_unique_name(): void
    {
        Country::factory()->create(['name' => 'Kuwait']);

        $this->actingAs($this->admin)
            ->post(route('countries.store'), ['name' => 'Kuwait'])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function update_changes_country(): void
    {
        $country = Country::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)
            ->put(route('countries.update', $country), ['name' => 'New Name'])
            ->assertRedirect(route('countries.index'));

        $this->assertDatabaseHas('countries', ['id' => $country->id, 'name' => 'New Name']);
    }

    #[Test]
    public function destroy_deletes_country(): void
    {
        $country = Country::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('countries.destroy', $country))
            ->assertRedirect(route('countries.index'));

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}
