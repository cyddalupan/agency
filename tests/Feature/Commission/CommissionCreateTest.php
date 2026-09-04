<?php

namespace Tests\Feature\Commission;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommissionCreateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('commissions.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('commissions.create'));

        $response->assertOk();
        $response->assertSee('Record Commission');
    }

    #[Test]
    public function store_creates_commission(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('commissions.store'), [
                'employer_id' => $this->employer->id,
                'amount' => 15000,
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('commissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commissions', [
            'agency_id' => $this->agency->id,
            'amount' => 15000,
        ]);
    }

    #[Test]
    public function store_requires_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('commissions.store'), [
                'employer_id' => $this->employer->id,
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    #[Test]
    public function store_auto_sets_agency_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('commissions.store'), [
                'employer_id' => $this->employer->id,
                'amount' => 5000,
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('commissions', [
            'amount' => 5000,
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_persists_agent_id_when_provided(): void
    {
        $agent = \App\Models\Agent::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('commissions.store'), [
                'employer_id' => $this->employer->id,
                'agent_id'    => $agent->id,
                'amount'      => 15000,
                'status'      => 'pending',
            ])
            ->assertRedirect(route('commissions.index'));

        $this->assertDatabaseHas('commissions', [
            'agent_id' => $agent->id,
            'amount'   => 15000,
        ]);
    }
}
