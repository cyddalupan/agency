<?php

namespace Tests\Feature\Payment;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentCreateTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Bill $bill;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->bill = Bill::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('payments.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('payments.create'));

        $response->assertOk();
        $response->assertSee('Record Payment');
    }

    #[Test]
    public function store_creates_payment(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('payments.store'), [
                'bill_id' => $this->bill->id,
                'amount' => 25000,
                'category' => 'employer_cost',
                'type' => 'cash',
                'reference_no' => 'REF-001',
                'status' => 'confirmed',
                'payment_date' => '2026-06-15',
            ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'agency_id' => $this->agency->id,
            'bill_id' => $this->bill->id,
            'amount' => 25000,
            'category' => 'employer_cost',
        ]);
    }

    #[Test]
    public function store_requires_amount_and_category(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('payments.store'), []);

        $response->assertSessionHasErrors(['amount', 'category']);
    }

    #[Test]
    public function store_auto_sets_agency_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('payments.store'), [
                'bill_id' => $this->bill->id,
                'amount' => 5000,
                'category' => 'deposit',
                'type' => 'gcash',
            ]);

        $this->assertDatabaseHas('payments', [
            'amount' => 5000,
            'agency_id' => $this->agency->id,
        ]);
    }
}
