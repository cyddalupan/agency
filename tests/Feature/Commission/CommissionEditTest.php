<?php

namespace Tests\Feature\Commission;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommissionEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Commission $commission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->commission = Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'amount' => 10000,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_edit(): void
    {
        $response = $this->get(route('commissions.edit', $this->commission));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('commissions.edit', $this->commission));

        $response->assertOk();
        $response->assertSee('Edit Commission');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->put(route('commissions.update', $this->commission), [
                'employer_id' => $employer->id,
                'amount' => 20000,
                'paid_amount' => 5000,
                'status' => 'partial',
            ]);

        $response->assertRedirect(route('commissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commissions', [
            'id' => $this->commission->id,
            'amount' => 20000,
            'paid_amount' => 5000,
            'status' => 'partial',
        ]);
    }

    #[Test]
    public function update_requires_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('commissions.update', $this->commission), []);

        $response->assertSessionHasErrors(['amount']);
    }
}
