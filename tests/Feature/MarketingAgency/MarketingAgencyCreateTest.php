<?php

namespace Tests\Feature\MarketingAgency;

use App\Models\Agency;
use App\Models\MarketingAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingAgencyCreateTest extends TestCase
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
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('marketing-agencies.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('marketing-agencies.create'));

        $response->assertOk();
        $response->assertSee('Add New Marketing Agency');
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('marketing-agencies.store'), []);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function store_creates_marketing_agency(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('marketing-agencies.store'), [
                'name' => 'Golden Harvest Agency',
                'contact_person' => 'Juan Dela Cruz',
                'contact' => '09171234567',
                'email' => 'juan@goldenharvest.com',
                'address' => '123 Rizal St, Manila',
                'commission_rate' => 15,
            ]);

        $response->assertRedirect(route('marketing-agencies.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('marketing_agencies', [
            'name' => 'Golden Harvest Agency',
            'agency_id' => $this->agency->id,
            'commission_rate' => 15.00,
        ]);
    }

    #[Test]
    public function store_auto_sets_agency_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('marketing-agencies.store'), [
                'name' => 'Test Agency',
            ]);

        $this->assertDatabaseHas('marketing_agencies', [
            'name' => 'Test Agency',
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_accepts_optional_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('marketing-agencies.store'), [
                'name' => 'Minimal Agency',
            ]);

        $response->assertRedirect(route('marketing-agencies.index'));

        $this->assertDatabaseHas('marketing_agencies', [
            'name' => 'Minimal Agency',
            'status' => 'active',
        ]);
    }
}
