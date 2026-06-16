<?php

namespace Tests\Feature\OfficialReceipt;

use App\Models\Agency;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfficialReceiptCreateTest extends TestCase
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
        $this->payment = Payment::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_create(): void
    {
        $response = $this->get(route('official-receipts.create'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('official-receipts.create'));

        $response->assertOk();
        $response->assertSee('Issue Official Receipt');
    }

    #[Test]
    public function store_creates_official_receipt(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('official-receipts.store'), [
                'payment_id' => $this->payment->id,
                'or_no' => 'OR-2026-0001',
                'amount' => 25000,
                'issue_date' => '2026-06-15',
                'issued_to' => 'employer',
                'issued_to_name' => 'Juan Dela Cruz',
            ]);

        $response->assertRedirect(route('official-receipts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('official_receipts', [
            'agency_id' => $this->agency->id,
            'or_no' => 'OR-2026-0001',
            'amount' => 25000,
        ]);
    }

    #[Test]
    public function store_requires_or_no_and_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('official-receipts.store'), []);

        $response->assertSessionHasErrors(['or_no', 'amount', 'issued_to', 'issued_to_name']);
    }

    #[Test]
    public function store_requires_unique_or_no(): void
    {
        OfficialReceipt::factory()->create([
            'agency_id' => $this->agency->id,
            'or_no' => 'OR-2026-0001',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('official-receipts.store'), [
                'payment_id' => $this->payment->id,
                'or_no' => 'OR-2026-0001',
                'amount' => 10000,
                'issue_date' => '2026-06-15',
                'issued_to' => 'employer',
                'issued_to_name' => 'Juan Dela Cruz',
            ]);

        $response->assertSessionHasErrors('or_no');
    }

    #[Test]
    public function store_auto_sets_agency_id(): void
    {
        $this->actingAs($this->user)
            ->post(route('official-receipts.store'), [
                'payment_id' => $this->payment->id,
                'or_no' => 'OR-2026-0002',
                'amount' => 5000,
                'issue_date' => '2026-06-15',
                'issued_to' => 'agent',
                'issued_to_name' => 'Maria Santos',
            ]);

        $this->assertDatabaseHas('official_receipts', [
            'or_no' => 'OR-2026-0002',
            'agency_id' => $this->agency->id,
        ]);
    }
}
