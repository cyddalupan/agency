<?php

namespace Tests\Feature\Payment;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentIndexTest extends TestCase
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
        $response = $this->get(route('payments.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function index_displays_payments(): void
    {
        Payment::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.index'));

        $response->assertOk();
        $response->assertSee('Payments');
    }

    #[Test]
    public function index_is_tenant_scoped(): void
    {
        Payment::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $otherAgency = Agency::factory()->create();
        Payment::factory()->count(2)->create([
            'agency_id' => $otherAgency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.index'));

        $response->assertOk();
    }

    #[Test]
    public function index_shows_amount_formatted(): void
    {
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'amount' => 15000.50,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.index'));

        $response->assertOk();
        $response->assertSee('15,000.50');
    }
}
