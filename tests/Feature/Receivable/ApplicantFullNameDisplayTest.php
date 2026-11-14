<?php

namespace Tests\Feature\Receivable;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af82fef: LANDAS New Receivable / New Expense Request.
 *
 * The selects must show the applicant's full name
 * (First Name, Middle Name, Last Name) instead of "LAST, FIRST".
 */
class ApplicantFullNameDisplayTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agency = Agency::factory()->create(['subdomain' => 'landas', 'name' => 'LANDAS']);
        app()->instance('tenant_agency', $this->agency);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    private function applicant(?string $middle, ?string $suffix = null, string $first = 'Manolo', string $last = 'Abacajin'): Applicant
    {
        return Applicant::create([
            'agency_id' => $this->agency->id,
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'suffix' => $suffix,
            'status' => 'active',
        ]);
    }

    public function test_full_name_accessor_joins_first_middle_last(): void
    {
        $a = $this->applicant('Reyes');

        $this->assertSame('Manolo Reyes Abacajin', $a->full_name);
    }

    public function test_full_name_accessor_skips_a_missing_middle_name(): void
    {
        // Legacy rows have no middle name; the old string concat left a double
        // space, and "LAST, FIRST" hid the gap by reordering the parts.
        $a = $this->applicant(null);

        $this->assertSame('Manolo Abacajin', $a->full_name);
        $this->assertStringNotContainsString('  ', $a->full_name);
    }

    public function test_full_name_accessor_includes_the_suffix(): void
    {
        $a = $this->applicant('Reyes', 'Jr.');

        $this->assertSame('Manolo Reyes Abacajin Jr.', $a->full_name);
    }

    public function test_receivable_create_select_lists_the_full_name(): void
    {
        $agent = Agent::create([
            'agency_id' => $this->agency->id,
            'name' => 'Agent Smith',
            'status' => 'active',
        ]);
        $this->applicant('Reyes')->update(['agent_id' => $agent->id]);

        $response = $this->actingAs($this->admin())->get(route('receivable.create'))->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('Manolo Reyes Abacajin', $html);
        $this->assertStringNotContainsString('Abacajin, Manolo', $html);
    }

    public function test_receivable_index_lists_the_full_name(): void
    {
        $r = Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin()->id,
            'code' => 'R' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'date' => now()->toDateString(),
            'applicant_id' => $this->applicant('Reyes')->id,
            'amount' => 100,
            'status' => Receivable::STATUS_PENDING,
        ]);

        $html = $this->actingAs($this->admin())->get(route('receivable.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Manolo Reyes Abacajin', $html);
    }

    public function test_receivable_show_lists_the_full_name(): void
    {
        $r = Receivable::create([
            'agency_id' => $this->agency->id,
            'user_id' => $this->admin()->id,
            'code' => 'R' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'date' => now()->toDateString(),
            'applicant_id' => $this->applicant('Reyes')->id,
            'amount' => 100,
            'status' => Receivable::STATUS_PENDING,
        ]);

        $html = $this->actingAs($this->admin())->get(route('receivable.show', $r))->assertOk()->getContent();

        $this->assertStringContainsString('Manolo Reyes Abacajin', $html);
    }

    public function test_expense_request_form_lists_the_full_name(): void
    {
        $agent = Agent::create([
            'agency_id' => $this->agency->id,
            'name' => 'Agent Smith',
            'status' => 'active',
        ]);
        $this->applicant('Reyes')->update(['agent_id' => $agent->id]);

        $response = $this->actingAs($this->admin())->get(route('expense_request.create'));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('Manolo Reyes Abacajin', $html);
        $this->assertStringNotContainsString('Abacajin, Manolo', $html);
    }
}
