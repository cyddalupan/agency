<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16 (expense-request create page):
 * 1. Remove "(under agent)" from the Applicant input label.
 * 2. Applicant must still be selectable when Charge = office.
 */
class ExpenseRequestCreateApplicantTest extends TestCase
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

    #[Test]
    public function create_page_applicant_label_has_no_under_agent_suffix(): void
    {
        $response = $this->actingAs($this->user)->get(route('expense_request.create'));

        $response->assertOk();
        $response->assertDontSee('(under agent)');
        $response->assertSee('Applicant');
    }

    #[Test]
    public function create_page_agent_and_applicant_share_one_row(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)->get(route('expense_request.create'));
        $response->assertOk();

        $html = $response->getContent();

        // Agent Name and Applicant are connected, so they live side by side
        // in one grid row (no separate agent-only row exists anymore).
        $this->assertStringContainsString('name="lines[0][agent_id]"', $html);
        $this->assertStringContainsString('name="lines[0][applicant_id]"', $html);
        $this->assertStringNotContainsString('data-agent-row', $html);

        preg_match('/<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">(.*?)<\/div>\s*<\/div>/s', $html, $m);
        $sharedRow = $m[1] ?? '';
        $this->assertNotSame('', $sharedRow, 'agent/applicant shared row block should exist');
        $this->assertStringContainsString('agent_id', $sharedRow);
        $this->assertStringContainsString('applicant_id', $sharedRow);
    }

    #[Test]
    public function store_accepts_an_applicant_on_an_office_charge_line(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        // Toybits rule: office + applicant -> applicant accounts.
        $applicantAccount = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'APPLICANT',
            'type'        => 'expense',
            'is_active'   => true,
            'charge_type' => 'applicant',
        ]);

        $applicantSub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $applicantAccount->id,
            'name'        => 'Medical Advance',
            'type'        => 'expense',
            'charge_type' => 'applicant',
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => 'Office charge with applicant',
            'lines'     => [
                [
                    'charge'          => 'office',
                    'sub_account_id'  => $applicantSub->id,
                    'agent_id'        => null,
                    'applicant_id'    => $applicant->id,
                    'country_id'      => null,
                    'main_account_id' => $applicantAccount->id,
                    'currency'        => 'PHP',
                    'amount'          => 500.00,
                    'particular'      => 'Medical advance',
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('expense_request.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('expense_request_items', [
            'applicant_id' => $applicant->id,
            'charge'       => 'office',
        ]);

        $this->assertSame(1, ExpenseRequest::count());
    }
}
