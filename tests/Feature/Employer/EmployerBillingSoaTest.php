<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerBillingSoaTest extends TestCase
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
    public function unauthenticated_user_cannot_view_soa(): void
    {
        $response = $this->get(route('employer.billing.soa'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function soa_page_displays_statement_of_account(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.soa'));

        $response->assertOk();
        $response->assertSee('Statement of Account');
        $response->assertSee($this->employer->name);
    }

    #[Test]
    public function soa_shows_all_bills_with_balance(): void
    {
        Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 100000,
            'status'       => 'sent',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.soa'));

        $response->assertOk();
        $response->assertSee(number_format(100000, 2));
        $response->assertSee('100,000');
    }

    #[Test]
    public function soa_calculates_outstanding_balance(): void
    {
        $bill = Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 80000,
            'status'       => 'sent',
        ]);

        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill->id,
            'amount'    => 30000,
            'status'    => 'received',
        ]);

        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.soa'));

        $response->assertOk();
        $response->assertSee('50,000'); // 80,000 - 30,000 = 50,000
    }

    #[Test]
    public function soa_shows_empty_state(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.soa'));

        $response->assertOk();
        $response->assertSee('No bills');
    }

    #[Test]
    public function soa_includes_print_option(): void
    {
        $response = $this->actingAs($this->employerUser)
            ->get(route('employer.billing.soa'));

        $response->assertOk();
        $response->assertSee('Print');
    }

    #[Test]
    public function non_employer_user_cannot_access_soa(): void
    {
        $nonEmployer = User::factory()->create([
            'agency_id'   => $this->agency->id,
            'employer_id' => null,
            'user_type'   => 'admin',
        ]);

        $response = $this->actingAs($nonEmployer)
            ->get(route('employer.billing.soa'));

        $response->assertRedirect();
    }
}
