<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerCreateTest extends TestCase
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

    #[Test]
    public function unauthenticated_user_cannot_access_create_form(): void
    {
        $response = $this->get(route('employers.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays_correctly(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('employers.create'));

        $response->assertOk();
        $response->assertSee('Add Employer');
    }

    #[Test]
    public function store_requires_name(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('employers.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function store_creates_employer(): void
    {
        $country = Country::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('employers.store'), [
                'name' => 'Acme Corp',
                'contact_person' => 'John Doe',
                'contact' => '09171234567',
                'email' => 'john@acme.com',
                'address' => '123 Main St',
                'country_id' => $country->id,
            ]);

        $response->assertRedirect(route('employers.index'));
        $this->assertDatabaseHas('employers', [
            'name' => 'Acme Corp',
            'email' => 'john@acme.com',
        ]);
    }

    #[Test]
    public function store_accepts_optional_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('employers.store'), [
                'name' => 'Minimal Corp',
            ]);

        $response->assertRedirect(route('employers.index'));
        $this->assertDatabaseHas('employers', [
            'name' => 'Minimal Corp',
            'status' => 'active',
        ]);
    }
}
