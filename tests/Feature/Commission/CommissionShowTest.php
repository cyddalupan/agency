<?php

namespace Tests\Feature\Commission;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommissionShowTest extends TestCase
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
    public function unauthenticated_user_cannot_view(): void
    {
        $commission = Commission::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('commissions.show', $commission));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_commission_details(): void
    {
        $employer = \App\Models\Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'ACME Corp',
        ]);

        $commission = Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $employer->id,
            'amount' => 25000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.show', $commission));

        $response->assertOk();
        $response->assertSee(number_format(25000, 2));
        $response->assertSee('ACME Corp');
    }

    #[Test]
    public function show_shows_back_link(): void
    {
        $commission = Commission::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.show', $commission));

        $response->assertOk();
        $response->assertSee('Back');
    }
}
