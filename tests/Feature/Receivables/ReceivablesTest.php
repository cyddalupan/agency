<?php

namespace Tests\Feature\Receivables;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceivablesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    // ---------- Access control ----------

    #[Test]
    public function unauthenticated_user_cannot_access_receivables(): void
    {
        $this->get(route('accounting.receivables'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_receivables(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $this->actingAs($staff)
            ->get(route('accounting.receivables'))
            ->assertForbidden();
    }

    // ---------- Receivables: outstanding per entity ----------

    #[Test]
    public function receivables_shows_outstanding_balance_per_employer(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Acme Corp']);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        // Bill billed 20,000; only 6,000 confirmed paid => outstanding 14,000
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $employer->id,
            'applicant_id' => $applicant->id,
            'employer_cost' => 20000.00,
            'employer_deposit' => 0,
            'status' => 'partially_paid',
        ]);
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 6000.00,
            'status' => 'confirmed',
            'payment_date' => '2026-08-01',
        ]);
        // A pending payment must NOT count toward collections
        Payment::factory()->create([
            'agency_id' => $this->agency->id,
            'bill_id' => $bill->id,
            'amount' => 5000.00,
            'status' => 'pending',
            'payment_date' => '2026-08-02',
        ]);

        $this->actingAs($this->user)
            ->get(route('accounting.receivables'))
            ->assertOk()
            ->assertViewHas('receivables', function ($rows) {
                $row = collect($rows)->firstWhere('employer_name', 'Acme Corp');
                return $row && abs($row['outstanding'] - 14000.00) < 0.01;
            });
    }

    #[Test]
    public function receivables_outstanding_uses_employer_cost_only_and_ignores_other_agencies(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Globex']);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $bill = Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $employer->id,
            'applicant_id' => $applicant->id,
            'employer_cost' => 5000.00,
            'employer_deposit' => 1000.00,
            'status' => 'pending',
        ]);

        // Other agency's bill + payment must be ignored
        $other = Agency::factory()->create();
        $otherEmp = Employer::factory()->create(['agency_id' => $other->id, 'name' => 'Other Co']);
        $otherApp = Applicant::factory()->create(['agency_id' => $other->id]);
        $otherBill = Bill::factory()->create([
            'agency_id' => $other->id,
            'employer_id' => $otherEmp->id,
            'applicant_id' => $otherApp->id,
            'employer_cost' => 99999.00,
            'status' => 'pending',
        ]);

        $this->actingAs($this->user)
            ->get(route('accounting.receivables'))
            ->assertOk()
            ->assertViewHas('receivables', function ($rows) {
                return collect($rows)->every(fn ($r) => $r['employer_name'] !== 'Other Co');
            })
            ->assertViewHas('receivables', function ($rows) {
                $row = collect($rows)->firstWhere('employer_name', 'Globex');
                return $row && abs($row['outstanding'] - 6000.00) < 0.01;
            });
    }

    // ---------- Overdue classification ----------

    #[Test]
    public function receivables_marks_overdue_bills_past_due_date_with_balance(): void
    {
        $app = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $e1 = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Overdue Inc']);
        $e2 = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Current Inc']);

        // Overdue: due_date in the past, no payments
        Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $e1->id,
            'applicant_id' => $app->id,
            'employer_cost' => 10000.00,
            'status' => 'pending',
            'due_date' => now()->subDays(10)->toDateString(),
        ]);
        // Current: due in the future
        Bill::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $e2->id,
            'applicant_id' => $app->id,
            'employer_cost' => 10000.00,
            'status' => 'pending',
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($this->user)
            ->get(route('accounting.receivables'))
            ->assertOk()
            ->assertViewHas('overdueCount', 1)
            ->assertViewHas('receivables', function ($rows) {
                $overdue = collect($rows)->firstWhere('employer_name', 'Overdue Inc');
                $current = collect($rows)->firstWhere('employer_name', 'Current Inc');
                return $overdue['status'] === 'overdue' && $current['status'] === 'current';
            });
    }

    // (Collection Module removed) — the "accounting.collections" route/view and its tests were deleted.
}
