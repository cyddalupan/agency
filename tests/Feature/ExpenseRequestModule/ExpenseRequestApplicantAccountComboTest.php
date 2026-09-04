<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-08-16 — bug: "Selected Sub Account is invalid."
 *
 * Front-end rule (locked): Charge = office + Applicant picked → the Account
 * Type dropdown shows applicant accounts only. The backend store() must
 * accept applicant sub-accounts for that combination, not just children of
 * the office main.
 */
class ExpenseRequestApplicantAccountComboTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    private Branch $branch;

    private Account $officeMain;

    private Account $officeSub;

    private Account $applicantMain;

    private Account $applicantSub;

    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->officeMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->officeSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->officeMain->id,
            'name'        => 'Salaries',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->applicantMain = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'APPLICANT',
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);
        $this->applicantSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->applicantMain->id,
            'name'        => 'Medical',
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);

        $this->applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
    }

    #[Test]
    public function office_plus_applicant_accepts_applicant_sub_account(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'notes'     => 'Office + applicant combo',
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $this->applicantSub->id,
                    'agent_id'       => null,
                    'applicant_id'   => $this->applicant->id,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 300.00,
                    'particular'     => 'Medical for applicant',
                ],
            ],
        ];

        $this->actingAs($this->user)->post(route('expense_request.store'), $payload)
            ->assertRedirect();

        $item = ExpenseRequestItem::first();
        $this->assertNotNull($item);
        $this->assertSame($this->applicantSub->id, (int) $item->account_id);
        $this->assertSame($this->applicant->id, (int) $item->applicant_id);
    }

    #[Test]
    public function office_without_applicant_still_rejects_applicant_sub_account(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $this->applicantSub->id,
                    'agent_id'       => null,
                    'applicant_id'   => null,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 100.00,
                    'particular'     => 'Should fail',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('expense_request.store'), $payload);

        $response->assertSessionHasErrors();
        $this->assertNull(ExpenseRequestItem::first());
    }

    #[Test]
    public function office_plus_applicant_rejects_office_sub_account(): void
    {
        // Per the locked rule, office + applicant shows applicant accounts
        // only — an office sub-account is not a valid pick in that combo.
        $payload = [
            'branch_id' => $this->branch->id,
            'lines'     => [
                [
                    'charge'         => 'office',
                    'sub_account_id' => $this->officeSub->id,
                    'agent_id'       => null,
                    'applicant_id'   => $this->applicant->id,
                    'country_id'     => null,
                    'currency'       => 'PHP',
                    'amount'         => 150.00,
                    'particular'     => 'Office expense w/ applicant',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->post(route('expense_request.store'), $payload);

        $response->assertSessionHasErrors();
        $this->assertNull(ExpenseRequestItem::first());
    }
}
