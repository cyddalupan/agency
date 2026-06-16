<?php

namespace Tests\Feature\Commission;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommissionDeleteTest extends TestCase
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
    public function unauthenticated_user_cannot_delete(): void
    {
        $commission = Commission::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->delete(route('commissions.destroy', $commission));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function delete_removes_commission(): void
    {
        $commission = Commission::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('commissions.destroy', $commission));

        $response->assertRedirect(route('commissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('commissions', ['id' => $commission->id]);
    }
}
