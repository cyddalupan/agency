<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\User;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountingOverviewTest extends TestCase
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
    public function guest_cannot_access_accounting(): void
    {
        $response = $this->get(route('accounting.employer', $this->employer));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_employer_accounting_overview(): void
    {
        Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'employer_cost' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.employer', $this->employer));

        $response->assertOk();
        $response->assertViewHas('employer');
        $response->assertViewHas('totalBilled');
        $response->assertViewHas('totalPaid');
        $response->assertViewHas('totalCommissions');
        $response->assertViewHas('balance');
    }

    #[Test]
    public function calculates_financial_summary(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'employer_cost' => 100000,
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 40000,
        ]);
        Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'amount' => 10000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.employer', $this->employer));

        $response->assertOk();
        $this->assertEquals(100000, $response->viewData('totalBilled'));
        $this->assertEquals(40000, $response->viewData('totalPaid'));
        $this->assertEquals(10000, $response->viewData('totalCommissions'));
        $this->assertEquals(60000, $response->viewData('balance'));
    }

    #[Test]
    public function shows_zero_balance_when_fully_paid(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'employer_cost' => 50000,
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.employer', $this->employer));

        $this->assertEquals(0, $response->viewData('balance'));
    }

    #[Test]
    public function tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherEmployer = Employer::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.employer', $otherEmployer));

        $response->assertNotFound();
    }

    // Worker accounting

    #[Test]
    public function shows_worker_accounting_overview(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'applicant_id' => $applicant->id,
            'applicant_cost' => 5000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.worker', $applicant));

        $response->assertOk();
        $response->assertViewHas('applicant');
        $response->assertViewHas('totalCost');
        $response->assertViewHas('totalPaid');
        $response->assertViewHas('balance');
    }

    #[Test]
    public function worker_accounting_totals(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'applicant_id' => $applicant->id,
            'applicant_cost' => 15000,
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 5000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.worker', $applicant));

        $response->assertOk();
        $this->assertEquals(15000, $response->viewData('totalCost'));
        $this->assertEquals(5000, $response->viewData('totalPaid'));
        $this->assertEquals(10000, $response->viewData('balance'));
    }

    #[Test]
    public function worker_accounting_tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherApplicant = Applicant::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.worker', $otherApplicant));

        $response->assertNotFound();
    }
}
