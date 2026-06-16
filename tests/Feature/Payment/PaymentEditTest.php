<?php

namespace Tests\Feature\Payment;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentEditTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->payment = Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'amount' => 10000,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_edit(): void
    {
        $response = $this->get(route('payments.edit', $this->payment));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('payments.edit', $this->payment));

        $response->assertOk();
        $response->assertSee('Edit Payment');
    }

    #[Test]
    public function update_saves_changes(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('payments.update', $this->payment), [
                'bill_id' => $this->payment->bill_id,
                'amount' => 20000,
                'category' => 'deposit',
                'type' => 'bank_transfer',
            ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'amount' => 20000,
            'category' => 'deposit',
        ]);
    }

    #[Test]
    public function update_requires_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('payments.update', $this->payment), [
                'bill_id' => $this->payment->bill_id,
            ]);

        $response->assertSessionHasErrors('amount');
    }
}
