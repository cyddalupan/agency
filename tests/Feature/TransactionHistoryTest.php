<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
        $this->employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function guest_cannot_access_transaction_history(): void
    {
        $response = $this->get(route('transactions.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_transaction_history(): void
    {
        Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'employer_cost' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('transactions.index'));

        $response->assertOk();
        $response->assertViewIs('transactions.index');
        $response->assertViewHas('bills');
        $response->assertViewHas('payments');
    }

    #[Test]
    public function includes_bills_and_payments(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'employer_cost' => 50000,
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 20000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('transactions.index'));

        $this->assertCount(1, $response->viewData('bills'));
        $this->assertCount(1, $response->viewData('payments'));
    }

    #[Test]
    public function is_scoped_to_tenant_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherEmployer = Employer::factory()->create(['agency_id' => $otherAgency->id]);
        Bill::factory()->create([
            'agency_id' => $otherAgency->id,
            'employer_id' => $otherEmployer->id,
            'employer_cost' => 99999,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('transactions.index'));

        $this->assertCount(0, $response->viewData('bills'));
    }

    #[Test]
    public function shows_summary_totals(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'employer_cost' => 100000,
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 30000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('transactions.index'));

        $response->assertViewHas('totalBilled');
        $response->assertViewHas('totalPaid');
        $this->assertEquals(100000, $response->viewData('totalBilled'));
        $this->assertEquals(30000, $response->viewData('totalPaid'));
    }
}
