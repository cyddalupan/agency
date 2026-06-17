<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerBillingApplicantTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;
    private User $employerUser;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('tenant_agency', $this->agency = Agency::factory()->create());

        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->employerUser = User::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => $this->employer->id,
            'user_type'   => 'employer',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_view_applicant_billing(): void
    {
        $response = $this->get(route('employer.billing.applicant', $this->applicant));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function applicant_billing_shows_applicant_name(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.applicant', $this->applicant));

        $response->assertOk();
        $response->assertSee($this->applicant->first_name);
        $response->assertSee($this->applicant->last_name);
    }

    #[Test]
    public function applicant_billing_shows_applicant_bills(): void
    {
        Bill::factory()->create([
            'agency_id'      => $this->agency->id,
            'employer_id'    => $this->employer->id,
            'applicant_id'   => $this->applicant->id,
            'applicant_cost' => 15000,
            'status'         => 'sent',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.applicant', $this->applicant));

        $response->assertOk();
        $response->assertSee(number_format(15000, 2));
    }

    #[Test]
    public function applicant_billing_shows_payment_breakdown(): void
    {
        $bill = Bill::factory()->create([
            'agency_id'      => $this->agency->id,
            'employer_id'    => $this->employer->id,
            'applicant_id'   => $this->applicant->id,
            'applicant_cost' => 20000,
            'status'         => 'sent',
        ]);

        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill->id,
            'amount'    => 10000,
            'status'    => 'received',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.applicant', $this->applicant));

        $response->assertOk();
        $response->assertSee(number_format(20000, 2));
        $response->assertSee(number_format(10000, 2));
    }

    #[Test]
    public function applicant_billing_shows_empty_state(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.applicant', $this->applicant));

        $response->assertOk();
        $response->assertSee('No bills');
    }

    #[Test]
    public function employer_cannot_see_applicants_from_other_employers(): void
    {
        $otherEmployer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $otherApplicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.applicant', $otherApplicant));

        $response->assertNotFound();
    }

    #[Test]
    public function applicant_billing_includes_total_cost_balance(): void
    {
        $bill = Bill::factory()->create([
            'agency_id'      => $this->agency->id,
            'employer_id'    => $this->employer->id,
            'applicant_id'   => $this->applicant->id,
            'applicant_cost' => 25000,
            'status'         => 'sent',
        ]);

        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill->id,
            'amount'    => 10000,
            'status'    => 'received',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.applicant', $this->applicant));

        $response->assertOk();
        $response->assertSee('Balance');
        $response->assertSee('15,000'); // 25,000 - 10,000 = 15,000 balance
    }
}
