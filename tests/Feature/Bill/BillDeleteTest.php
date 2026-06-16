<?php

namespace Tests\Feature\Bill;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BillDeleteTest extends TestCase
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
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->delete(route('bills.destroy', $bill));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function delete_removes_bill(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('bills.destroy', $bill));

        $response->assertRedirect(route('bills.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
    }
}
