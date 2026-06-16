<?php

namespace Tests\Feature\Payment;

use App\Models\Agency;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentDeleteTest extends TestCase
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
        $payment = Payment::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->delete(route('payments.destroy', $payment));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function delete_removes_payment(): void
    {
        $payment = Payment::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('payments.destroy', $payment));

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
