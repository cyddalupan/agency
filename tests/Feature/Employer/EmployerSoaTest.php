<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerSoaTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Employer $employer;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('tenant_agency', $this->agency = Agency::factory()->create());

        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_view_soa(): void
    {
        $response = $this->get(route('employers.soa', $this->employer));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function soa_page_displays_employer_name(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('employers.soa', $this->employer));

        $response->assertOk();
        $response->assertSee($this->employer->name);
        $response->assertSee('Statement of Account');
    }

    #[Test]
    public function soa_shows_bills_and_running_balance(): void
    {
        $bill = Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 50000,
            'status'       => 'sent',
        ]);

        $payment = Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill->id,
            'amount'    => 20000,
            'status'    => 'received',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('employers.soa', $this->employer));

        $response->assertOk();
        $response->assertSee(number_format(50000, 2));
        $response->assertSee(number_format(20000, 2));
        $response->assertSee('Balance');
    }

    #[Test]
    public function soa_calculates_balance_correctly(): void
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
            'amount'    => 30000,
            'status'    => 'received',
        ]);

        $bill2 = Bill::factory()->create([
            'agency_id'    => $this->agency->id,
            'employer_id'  => $this->employer->id,
            'employer_cost' => 50000,
            'status'       => 'sent',
        ]);

        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id'   => $bill2->id,
            'amount'    => 50000,
            'status'    => 'received',
        ]);

        // Total billed: 150,000; Total paid: 80,000; Balance: 70,000
        $response = $this->actingAs($this->user)
            ->get(route('employers.soa', $this->employer));

        $response->assertOk();
        $response->assertSee('70,000');
    }

    #[Test]
    public function soa_shows_only_this_employers_data(): void
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

        $response = $this->actingAs($this->user)
            ->get(route('employers.soa', $this->employer));

        $response->assertOk();
        $response->assertSee('100,000');
        $response->assertDontSee('99,999');
    }

    #[Test]
    public function soa_shows_no_bills_message(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('employers.soa', $this->employer));

        $response->assertOk();
        $response->assertSee('No bills');
        $response->assertSee('Statement of Account');
    }
}
