<?php

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\Employer;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountingDashboardTest extends TestCase
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
    public function unauthenticated_user_cannot_access_dashboard(): void
    {
        $this->get(route('accounting.dashboard'))->assertRedirect(route('login'));
    }

    #[Test]
    public function unauthorized_role_cannot_access_dashboard(): void
    {
        $outsider = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff', // staff not in admin,super_admin,billing
        ]);

        $this->actingAs($outsider)->get(route('accounting.dashboard'))->assertForbidden(403);
    }

    // ---------- Aggregation: money in / money out / balance ----------

    #[Test]
    public function dashboard_aggregates_money_in_and_money_out(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'expense']);

        // Money in: payments totaling 1500
        Payment::factory()->create(['agency_id' => $this->agency->id, 'amount' => 1000.00, 'category' => 'employer_cost']);
        Payment::factory()->create(['agency_id' => $this->agency->id, 'amount' => 500.00, 'category' => 'applicant_cost']);

        // Money out: expense 200 + commission paid 350
        Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $account->id, 'amount' => 200.00]);
        $commission = Commission::factory()->create(['agency_id' => $this->agency->id, 'amount' => 350.00, 'paid_amount' => 0]);
        CommissionPayment::factory()->create(['agency_id' => $this->agency->id, 'commission_id' => $commission->id, 'amount' => 350.00]);

        $this->actingAs($this->user)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertViewHas('moneyIn', 1500.00)
            ->assertViewHas('moneyOut', 550.00)
            ->assertViewHas('balance', 950.00);
    }

    #[Test]
    public function dashboard_only_counts_own_agencies_data(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAccount = Account::factory()->create(['agency_id' => $otherAgency->id]);
        Expense::factory()->create(['agency_id' => $otherAgency->id, 'account_id' => $otherAccount->id, 'amount' => 9999.00]);
        Payment::factory()->create(['agency_id' => $otherAgency->id, 'amount' => 8888.00, 'category' => 'employer_cost']);

        $this->actingAs($this->user)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertViewHas('moneyIn', 0.0)
            ->assertViewHas('moneyOut', 0.0)
            ->assertViewHas('balance', 0.0);
    }

    #[Test]
    public function dashboard_respects_date_range_filter(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'expense']);

        Payment::factory()->create(['agency_id' => $this->agency->id, 'amount' => 100.00, 'category' => 'employer_cost', 'payment_date' => '2026-07-01']);
        Payment::factory()->create(['agency_id' => $this->agency->id, 'amount' => 300.00, 'category' => 'employer_cost', 'payment_date' => '2026-08-15']);

        Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $account->id, 'amount' => 50.00, 'date' => '2026-07-20']);

        $this->actingAs($this->user)
            ->get(route('accounting.dashboard', ['from' => '2026-08-01', 'to' => '2026-08-31']))
            ->assertOk()
            ->assertViewHas('moneyIn', 300.00)   // only the Aug payment
            ->assertViewHas('moneyOut', 0.0);    // July expense excluded
    }

    // ---------- P&L by account ----------

    #[Test]
    public function dashboard_groups_income_and_expense_by_account(): void
    {
        $incomeMain = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'income', 'name' => 'Placement Fees']);
        $expenseMain = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'expense', 'name' => 'Office Expenses']);
        $salaries = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'expense', 'name' => 'Salaries', 'parent_id' => $expenseMain->id]);

        Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $salaries->id, 'amount' => 400.00]);
        Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $expenseMain->id, 'amount' => 100.00]);

        // no account-linked income yet (payments are bill-based); expect expense breakdown present
        $this->actingAs($this->user)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertViewHas('expensesByAccount', function ($byAccount) use ($salaries) {
                $lump = $byAccount->mapWithKeys(fn ($r) => [$r['account_name'] => $r['total']]);
                return $lump['Salaries'] == 400.0 && $lump['Office Expenses'] == 100.0;
            })
            ->assertViewHas('incomeByAccount', fn ($v) => $v->isEmpty());
    }

    // ---------- Period trend ----------

    #[Test]
    public function dashboard_builds_monthly_trend(): void
    {
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'expense']);

        Payment::factory()->create(['agency_id' => $this->agency->id, 'amount' => 500.00, 'category' => 'employer_cost', 'payment_date' => '2026-07-10']);

        Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $account->id, 'amount' => 200.00, 'date' => '2026-07-15']);

        $this->actingAs($this->user)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertViewHas('monthlyTrend', function ($trend) {
                $july = collect($trend)->firstWhere('month', '2026-07');
                return $july && $july['in'] == 500.0 && $july['out'] == 200.0 && $july['net'] == 300.0;
            });
    }

    // ---------- Entity breakdown (per employer) ----------

    #[Test]
    public function dashboard_groups_money_by_entity_employer(): void
    {
        $employerA = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Acme Corp']);
        $employerB = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Globex Inc']);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $billA = Bill::factory()->create(['agency_id' => $this->agency->id, 'employer_id' => $employerA->id, 'applicant_id' => $applicant->id]);
        $billB = Bill::factory()->create(['agency_id' => $this->agency->id, 'employer_id' => $employerB->id, 'applicant_id' => $applicant->id]);

        Payment::factory()->create(['agency_id' => $this->agency->id, 'bill_id' => $billA->id, 'amount' => 800.00, 'payment_date' => '2026-08-02']);
        Payment::factory()->create(['agency_id' => $this->agency->id, 'bill_id' => $billA->id, 'amount' => 200.00, 'payment_date' => '2026-08-03']);
        Payment::factory()->create(['agency_id' => $this->agency->id, 'bill_id' => $billB->id, 'amount' => 500.00, 'payment_date' => '2026-08-04']);

        $this->actingAs($this->user)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertViewHas('moneyInByEntity', function ($byEntity) {
                $e = collect($byEntity)->keyBy('employer_name');
                return ($e['Acme Corp']['in'] ?? null) == 1000.0 && ($e['Globex Inc']['in'] ?? null) == 500.0;
            });
    }

    #[Test]
    public function entity_breakdown_only_counts_own_agencies(): void
    {
        $other = Agency::factory()->create();
        $otherEmployer = Employer::factory()->create(['agency_id' => $other->id, 'name' => 'Other Co']);
        $otherApplicant = Applicant::factory()->create(['agency_id' => $other->id]);
        $otherBill = Bill::factory()->create(['agency_id' => $other->id, 'employer_id' => $otherEmployer->id, 'applicant_id' => $otherApplicant->id]);
        Payment::factory()->create(['agency_id' => $other->id, 'bill_id' => $otherBill->id, 'amount' => 9000.00, 'payment_date' => '2026-08-01']);

        $this->actingAs($this->user)
            ->get(route('accounting.dashboard'))
            ->assertOk()
            ->assertViewHas('moneyInByEntity', fn ($v) => $v->isEmpty());
    }

    // ---------- Export: CSV ----------

    #[Test]
    public function csv_export_contains_income_expense_and_entity_rows(): void
    {
        $employer = Employer::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Acme Corp']);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        $bill = Bill::factory()->create(['agency_id' => $this->agency->id, 'employer_id' => $employer->id, 'applicant_id' => $applicant->id]);
        $account = Account::factory()->create(['agency_id' => $this->agency->id, 'type' => 'expense', 'name' => 'Office Expenses']);

        Payment::factory()->create(['agency_id' => $this->agency->id, 'bill_id' => $bill->id, 'amount' => 400.00, 'payment_date' => '2026-08-01']);
        Expense::factory()->create(['agency_id' => $this->agency->id, 'account_id' => $account->id, 'amount' => 150.00, 'date' => '2026-08-02']);

        $resp = $this->actingAs($this->user)
            ->get(route('accounting.export', ['format' => 'csv']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment', $resp->headers->get('content-disposition'));
        $csv = $resp->streamedContent();

        $this->assertStringContainsString('Category,Amount', $csv);
        $this->assertStringContainsString('400.00', $csv);
        $this->assertStringContainsString('Acme Corp', $csv);
        $this->assertStringContainsString('Office Expenses', $csv);
    }

    #[Test]
    public function csv_export_requires_finance_role(): void
    {
        $staff = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);
        $this->actingAs($staff)
            ->get(route('accounting.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    // ---------- Export: PDF ----------

    #[Test]
    public function pdf_export_returns_pdf_document(): void
    {
        $resp = $this->actingAs($this->user)
            ->get(route('accounting.export', ['format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $pdf = $resp->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
    }
}
