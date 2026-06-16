<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantSoaTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
        $this->applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function guest_cannot_access_worker_soa(): void
    {
        $response = $this->get(route('applicants.soa', $this->applicant));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_worker_soa_summary(): void
    {
        Bill::factory()->create([
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 5000,
            'applicant_deposit' => 2000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.soa', $this->applicant));

        $response->assertOk();
        $response->assertViewIs('applicants.soa');
        $response->assertViewHas('applicant');
        $response->assertViewHas('bills');
        $response->assertViewHas('totalCost');
        $response->assertViewHas('totalPaid');
        $response->assertViewHas('balance');
    }

    #[Test]
    public function calculates_totals_correctly(): void
    {
        $bill1 = Bill::factory()->create([
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 10000,
            'applicant_deposit' => 3000,
        ]);
        $bill2 = Bill::factory()->create([
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 5000,
            'applicant_deposit' => 2000,
        ]);

        Payment::factory()->create([
            'bill_id' => $bill1->id,
            'agency_id' => $this->agency->id,
            'amount' => 5000,
        ]);
        Payment::factory()->create([
            'bill_id' => $bill2->id,
            'agency_id' => $this->agency->id,
            'amount' => 2500,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.soa', $this->applicant));

        $response->assertOk();
        $this->assertEquals(15000, $response->viewData('totalCost'));
        $this->assertEquals(7500, $response->viewData('totalPaid'));
        $this->assertEquals(7500, $response->viewData('balance'));
    }

    #[Test]
    public function shows_zero_balance_when_fully_paid(): void
    {
        $bill = Bill::factory()->create([
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 7000,
        ]);
        Payment::factory()->create([
            'bill_id' => $bill->id,
            'agency_id' => $this->agency->id,
            'amount' => 7000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.soa', $this->applicant));

        $response->assertOk();
        $this->assertEquals(0, $response->viewData('balance'));
    }

    #[Test]
    public function shows_only_bills_for_this_applicant(): void
    {
        $otherApplicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        Bill::factory()->create([
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 3000,
        ]);
        Bill::factory()->create([
            'applicant_id' => $otherApplicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 9999,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.soa', $this->applicant));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('bills'));
        $this->assertEquals(3000, $response->viewData('totalCost'));
    }

    #[Test]
    public function is_scoped_to_tenant_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.soa', $otherApplicant));

        $response->assertNotFound();
    }

    #[Test]
    public function shows_bill_details_in_view(): void
    {
        Bill::factory()->create([
            'applicant_id' => $this->applicant->id,
            'agency_id' => $this->agency->id,
            'applicant_cost' => 4500,
            'applicant_deposit' => 500,
            'notes' => 'Processing fee',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.soa', $this->applicant));

        $response->assertOk();
        $response->assertSee('Processing fee');
    }
}
