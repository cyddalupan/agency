<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommissionPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Employer $employer;
    private Commission $commission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
        $this->commission = Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'amount' => 50000,
            'paid_amount' => 0,
            'status' => 'pending',
        ]);
    }

    // ─── Index ──────────────────────────────────────────────────────

    #[Test]
    public function guest_cannot_list_commission_payments(): void
    {
        $this->get(route('commissions.commission-payments.index', $this->commission))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function lists_commission_payments(): void
    {
        CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
            'amount' => 10000,
        ]);
        CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
            'amount' => 20000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.commission-payments.index', $this->commission));

        $response->assertOk();
        $response->assertViewIs('commission-payments.index');
        $response->assertViewHas('payments');
        $response->assertViewHas('commission');
        $this->assertCount(2, $response->viewData('payments'));
    }

    #[Test]
    public function commission_payments_are_tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherEmployer = Employer::factory()->create(['agency_id' => $otherAgency->id]);
        $otherCommission = Commission::factory()->create([
            'agency_id' => $otherAgency->id,
            'employer_id' => $otherEmployer->id,
            'amount' => 99999,
        ]);
        CommissionPayment::factory()->create([
            'agency_id' => $otherAgency->id,
            'commission_id' => $otherCommission->id,
            'amount' => 99999,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.commission-payments.index', $otherCommission));

        $response->assertNotFound();
    }

    // ─── Create ─────────────────────────────────────────────────────

    #[Test]
    public function guest_cannot_access_create_form(): void
    {
        $this->get(route('commissions.commission-payments.create', $this->commission))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_create_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('commissions.commission-payments.create', $this->commission));

        $response->assertOk();
        $response->assertViewIs('commission-payments.create');
        $response->assertViewHas('commission');
    }

    // ─── Store ──────────────────────────────────────────────────────

    #[Test]
    public function guest_cannot_store_commission_payment(): void
    {
        $this->post(route('commissions.commission-payments.store', $this->commission), [
            'amount' => 10000,
            'payment_date' => now()->format('Y-m-d'),
        ])->assertRedirect(route('login'));
    }

    #[Test]
    public function stores_commission_payment(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('commissions.commission-payments.store', $this->commission), [
                'amount' => 15000,
                'payment_date' => '2026-06-16',
                'reference_no' => 'CP-001',
                'notes' => 'Partial payment',
            ]);

        $response->assertRedirect(route('commissions.commission-payments.index', $this->commission));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('commission_payments', [
            'commission_id' => $this->commission->id,
            'amount' => 15000,
            'agency_id' => $this->agency->id,
            'reference_no' => 'CP-001',
            'notes' => 'Partial payment',
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('commissions.commission-payments.store', $this->commission), []);

        $response->assertSessionHasErrors(['amount', 'payment_date']);
    }

    #[Test]
    public function store_validates_amount_range(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('commissions.commission-payments.store', $this->commission), [
                'amount' => -100,
                'payment_date' => '2026-06-16',
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    #[Test]
    public function store_updates_commission_paid_amount_and_status(): void
    {
        $this->actingAs($this->user)
            ->post(route('commissions.commission-payments.store', $this->commission), [
                'amount' => 50000,
                'payment_date' => '2026-06-16',
            ]);

        $this->commission->refresh();
        $this->assertEquals(50000, $this->commission->paid_amount);
        $this->assertEquals('paid', $this->commission->status);
    }

    #[Test]
    public function store_partial_payment_updates_status_to_partial(): void
    {
        $this->actingAs($this->user)
            ->post(route('commissions.commission-payments.store', $this->commission), [
                'amount' => 20000,
                'payment_date' => '2026-06-16',
            ]);

        $this->commission->refresh();
        $this->assertEquals(20000, $this->commission->paid_amount);
        $this->assertEquals('partial', $this->commission->status);
    }

    #[Test]
    public function store_tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherCommission = Commission::factory()->create([
            'agency_id' => $otherAgency->id,
            'employer_id' => Employer::factory()->create(['agency_id' => $otherAgency->id])->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('commissions.commission-payments.store', $otherCommission), [
                'amount' => 10000,
                'payment_date' => '2026-06-16',
            ]);

        $response->assertNotFound();
    }

    // ─── Edit / Update ──────────────────────────────────────────────

    #[Test]
    public function shows_edit_form(): void
    {
        $payment = CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('commissions.commission-payments.edit', [$this->commission, $payment]));

        $response->assertOk();
        $response->assertViewIs('commission-payments.edit');
    }

    #[Test]
    public function updates_commission_payment(): void
    {
        $payment = CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
            'amount' => 10000,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('commissions.commission-payments.update', [$this->commission, $payment]), [
                'amount' => 15000,
                'payment_date' => '2026-06-17',
                'reference_no' => 'CP-UPDATED',
            ]);

        $response->assertRedirect(route('commissions.commission-payments.index', $this->commission));
        $this->assertDatabaseHas('commission_payments', [
            'id' => $payment->id,
            'amount' => 15000,
            'reference_no' => 'CP-UPDATED',
        ]);
    }

    // ─── Delete ─────────────────────────────────────────────────────

    #[Test]
    public function deletes_commission_payment(): void
    {
        $payment = CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
            'amount' => 10000,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('commissions.commission-payments.destroy', [$this->commission, $payment]));

        $response->assertRedirect(route('commissions.commission-payments.index', $this->commission));
        $this->assertDatabaseMissing('commission_payments', ['id' => $payment->id]);
    }

    #[Test]
    public function delete_recalculates_commission(): void
    {
        $payment1 = CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
            'amount' => 20000,
        ]);
        $payment2 = CommissionPayment::factory()->create([
            'agency_id' => $this->agency->id,
            'commission_id' => $this->commission->id,
            'amount' => 15000,
        ]);

        // Set commission's paid_amount to match (simulating after store)
        $this->commission->update(['paid_amount' => 35000, 'status' => 'partial']);

        $this->actingAs($this->user)
            ->delete(route('commissions.commission-payments.destroy', [$this->commission, $payment1]));

        $this->commission->refresh();
        $this->assertEquals(15000, $this->commission->paid_amount);
        $this->assertEquals('partial', $this->commission->status);
    }
}
