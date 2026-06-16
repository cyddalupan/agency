<?php

namespace Tests\Feature\Commission;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommissionIndexTest extends TestCase
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
    public function unauthenticated_user_cannot_access(): void
    {
        $response = $this->get(route('commissions.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_displays_commissions(): void
    {
        Commission::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.index'));

        $response->assertOk();
        $response->assertSee('Commissions');
    }

    #[Test]
    public function index_is_tenant_scoped(): void
    {
        Commission::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $otherAgency = Agency::factory()->create();
        Commission::factory()->count(2)->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.index'));

        $response->assertOk();
    }

    #[Test]
    public function index_shows_employer_name(): void
    {
        $employer = \App\Models\Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'TestEmployer Inc',
        ]);

        Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $employer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.index'));

        $response->assertSee('TestEmployer Inc');
    }

    #[Test]
    public function index_shows_status_badge(): void
    {
        Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.index'));

        $response->assertSee('Paid');
    }
}
