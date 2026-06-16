<?php

namespace Tests\Feature\Report;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\Employer;
use App\Models\OfficialReceipt;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Agency $agency;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
        $this->employer = Employer::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_download_reports(): void
    {
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id]);

        $this->get(route('reports.bill', $bill))->assertRedirect(route('login'));
        $this->get(route('reports.or', 1))->assertRedirect(route('login'));
        $this->get(route('reports.commission', 1))->assertRedirect(route('login'));
    }

    #[Test]
    public function bill_pdf_returns_pdf_response(): void
    {
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.bill', $bill));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function or_pdf_returns_pdf_response(): void
    {
        $payment = Payment::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $or = OfficialReceipt::factory()->create([
            'agency_id' => $this->agency->id,
            'payment_id' => $payment->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.or', $or));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function commission_pdf_returns_pdf_response(): void
    {
        $commission = Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.commission', $commission));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function report_returns_404_for_nonexistent_record(): void
    {
        $this->actingAs($this->user)
            ->get(route('reports.bill', 99999))
            ->assertNotFound();

        $this->actingAs($this->user)
            ->get(route('reports.or', 99999))
            ->assertNotFound();

        $this->actingAs($this->user)
            ->get(route('reports.commission', 99999))
            ->assertNotFound();
    }

    #[Test]
    public function user_cannot_access_report_from_other_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherBill = Bill::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->user)
            ->get(route('reports.bill', $otherBill))
            ->assertNotFound();
    }
}
