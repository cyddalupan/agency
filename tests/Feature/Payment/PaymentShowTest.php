<?php

namespace Tests\Feature\Payment;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentShowTest extends TestCase
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
        $payment = Payment::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->get(route('payments.show', $payment));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_payment_details(): void
    {
        $payment = Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'amount' => 15000,
            'reference_no' => 'REF-001',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.show', $payment));

        $response->assertOk();
        $response->assertSee(number_format(15000, 2));
        $response->assertSee('REF-001');
    }

    #[Test]
    public function show_shows_bill_link(): void
    {
        $payment = Payment::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.show', $payment));

        $response->assertOk();
        $response->assertSee('Bill');
    }
}
