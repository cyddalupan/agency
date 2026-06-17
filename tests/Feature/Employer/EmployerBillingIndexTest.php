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

class EmployerBillingIndexTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;
    private User $employerUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('tenant_agency', $this->agency = Agency::factory()->create());

        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->employerUser = User::factory()->create([
            'agency_id'  => $this->agency->id,
            'employer_id' => $this->employer->id,
            'user_type'  => 'employer',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_billing(): void
    {
        $response = $this->get(route('employer.billing.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function billing_page_displays_billing_header(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.index'));

        $response->assertOk();
        $response->assertSee('Billing');
    }

    #[Test]
    public function billing_page_shows_total_billed_paid_and_balance(): void
    {
        $bill1 = Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 100000,
            'status'       => 'sent',
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill1->id,
            'amount'    => 40000,
            'status'    => 'received',
        ]);

        $bill2 = Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 50000,
            'status'       => 'paid',
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill2->id,
            'amount'    => 50000,
            'status'    => 'received',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.index'));

        $response->assertOk();
        // Total billed: 150,000; Total paid: 90,000; Balance: 60,000
        $response->assertSee(number_format(150000, 2));
        $response->assertSee(number_format(90000, 2));
        $response->assertSee(number_format(60000, 2));
    }

    #[Test]
    public function billing_page_shows_bills_table(): void
    {
        $bill = Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 75000,
            'status'       => 'sent',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.index'));

        $response->assertOk();
        $response->assertSee(number_format(75000, 2));
        $response->assertSee('Sent');
    }

    #[Test]
    public function billing_page_hides_other_employers_bills(): void
    {
        $otherEmployer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 100000,
            'status'       => 'sent',
        ]);

        Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $otherEmployer->id,
            'employer_cost' => 99999,
            'status'       => 'sent',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.index'));

        $response->assertOk();
        $response->assertSee('100,000');
        $response->assertDontSee('99,999');
    }

    #[Test]
    public function billing_page_shows_empty_state_when_no_bills(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.index'));

        $response->assertOk();
        $response->assertSee('No bills');
    }

    #[Test]
    public function billing_page_has_link_to_statement_of_account(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.index'));

        $response->assertOk();
        $response->assertSee('Statement of Account');
    }

    #[Test]
    public function non_employer_user_cannot_access_billing(): void
    {
        $nonEmployer = User::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => null,
            'user_type'   => 'admin',
        ]);

        $response = $this->actingAs($nonEmployer)
            ->get(route('employer.billing.index'));

        $response->assertRedirect();
    }
}
