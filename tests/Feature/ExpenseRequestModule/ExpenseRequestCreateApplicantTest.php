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
    public function create_page_applicant_select_is_not_inside_the_agent_only_row(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)->get(route('expense_request.create'));
        $response->assertOk();

        $html = $response->getContent();

        // The applicant picker must still be present on the page...
        $this->assertStringContainsString('name="lines[0][applicant_id]"', $html);

        // ...but must NOT live inside the data-agent-row block that gets
        // hidden when Charge = office.
        preg_match('/<div[^>]*data-agent-row="0"[^>]*>(.*?)<\/div>\s*<\/div>/s', $html, $m);
        $agentRow = $m[1] ?? '';
        $this->assertNotSame('', $agentRow, 'agent-only row block should exist');
        $this->assertStringNotContainsString('applicant_id', $agentRow);
    }

    #[Test]
    public function store_accepts_an_applicant_on_an_office_charge_line(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $officeAccount = Account::factory()->create([
            'agency_id' => $this->agency->id,
            'parent_id' => null,
            'name'      => 'Office Expenses',
            'type'      => 'expense',
            'is_active' => true,
        ]);

        $payload = [
            'branch_id' => $branch->id,
            'notes'     => 'Office charge with applicant',
            'lines'     => [
                [
                    'charge'          => 'office',
                    'agent_id'        => null,
                    'applicant_id'    => $applicant->id,
                    'country_id'      => null,
                    'main_account_id' => $officeAccount->id,
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
