<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSetting;
use App\Models\ExpenseRequest;
use App\Models\ReportGeneratedLog;
use App\Models\ReportTemplate;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b008b8d3ca32bfcb58ce970 — the nine models that carried agency_id but
 * applied no tenant scope, so a tenant-bound request returned EVERY agency's
 * rows.
 *
 * Every test seeds one row in agency A and one in agency B, binds A, and
 * asserts the query sees A's row AND NOT B's. The negative half is the point:
 * "A's row is present" passes in the broken state too.
 *
 * Agent is the exception to "add use HasTenant" — see AgentScopeTest for why
 * it needs its own guard, and ActivityLog for why it is deliberately outside
 * the shared scope (its 262 NULL-agency rows are real history).
 */
class TenantOwnedModelsScopeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Agency $otherAgency;

    protected function setUp(): void
    {
        parent::setUp();

        // Name them so "first()" ordering is the WRONG one: a path that still
        // guesses picks $otherAgency and the test notices.
        $this->otherAgency = Agency::factory()->create(['name' => 'AAA Other', 'subdomain' => 'aaa-other']);
        $this->agency      = Agency::factory()->create(['name' => 'Zeta Agency', 'subdomain' => 'zeta-agency']);

        app()->instance('tenant_agency', $this->agency);
    }

    #[Test]
    public function branch_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Own Branch']);
        $other = Branch::factory()->create(['agency_id' => $this->otherAgency->id, 'name' => 'Other Branch']);

        $names = Branch::query()->pluck('name')->all();

        $this->assertContains('Own Branch', $names);
        $this->assertNotContains('Other Branch', $names, 'Branch::query() returned another agency\'s branch');
        $this->assertNull(Branch::find($other->id), 'Branch::find() crossed the tenant boundary');
        $this->assertSame($own->id, Branch::find($own->id)?->id);
    }

    #[Test]
    public function employee_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = Employee::create(['agency_id' => $this->agency->id, 'employee_no' => 'A-001', 'full_name' => 'Own Employee']);
        $other = Employee::create(['agency_id' => $this->otherAgency->id, 'employee_no' => 'B-001', 'full_name' => 'Other Employee']);

        $names = Employee::query()->pluck('full_name')->all();

        $this->assertContains('Own Employee', $names);
        $this->assertNotContains('Other Employee', $names, 'Employee::query() returned another agency\'s employee');
        $this->assertNull(Employee::find($other->id));
        $this->assertSame($own->id, Employee::find($own->id)?->id);
    }

    #[Test]
    public function employee_setting_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = EmployeeSetting::create(['agency_id' => $this->agency->id, 'type' => 'department', 'name' => 'Own Dept', 'status' => 'active']);
        $other = EmployeeSetting::create(['agency_id' => $this->otherAgency->id, 'type' => 'department', 'name' => 'Other Dept', 'status' => 'active']);

        // The form helper is the only reader; it also filters by agency_id, so
        // assert through the model query as well to prove the scope itself.
        $names = EmployeeSetting::query()->pluck('name')->all();

        $this->assertContains('Own Dept', $names);
        $this->assertNotContains('Other Dept', $names, 'EmployeeSetting::query() returned another agency\'s option');
        $this->assertNull(EmployeeSetting::find($other->id));
        $this->assertSame(['Own Dept'], array_values(EmployeeSetting::optionsFor($this->agency->id, 'department')));
    }

    #[Test]
    public function report_template_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = ReportTemplate::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Own Template']);
        $other = ReportTemplate::factory()->create(['agency_id' => $this->otherAgency->id, 'name' => 'Other Template']);

        $names = ReportTemplate::query()->pluck('name')->all();

        $this->assertContains('Own Template', $names);
        $this->assertNotContains('Other Template', $names, 'ReportTemplate::query() returned another agency\'s template');
        $this->assertNull(ReportTemplate::find($other->id));
        $this->assertSame($own->id, ReportTemplate::find($own->id)?->id);
    }

    #[Test]
    public function report_generated_log_queries_are_scoped_to_the_bound_agency(): void
    {
        $template = ReportTemplate::factory()->create(['agency_id' => $this->agency->id]);
        $otherTemplate = ReportTemplate::factory()->create(['agency_id' => $this->otherAgency->id]);

        $ownEncoder   = User::firstOrCreate(['email' => 'own@agency.test'], ['agency_id' => $this->agency->id, 'name' => 'Own', 'user_type' => 'staff', 'password' => bcrypt('password')]);
        $otherEncoder = User::firstOrCreate(['email' => 'other@agency.test'], ['agency_id' => $this->otherAgency->id, 'name' => 'Other', 'user_type' => 'staff', 'password' => bcrypt('password')]);

        $own = ReportGeneratedLog::create([
            'agency_id'           => $this->agency->id,
            'user_id'             => $ownEncoder->id,
            'report_template_id'  => $template->id,
            'format'              => 'pdf',
        ]);
        $other = ReportGeneratedLog::create([
            'agency_id'           => $this->otherAgency->id,
            'user_id'             => $otherEncoder->id,
            'report_template_id'  => $otherTemplate->id,
            'format'              => 'csv',
        ]);

        $ids = ReportGeneratedLog::query()->pluck('id')->all();

        $this->assertContains($own->id, $ids);
        $this->assertNotContains($other->id, $ids, 'ReportGeneratedLog::query() returned another agency\'s log');
        $this->assertNull(ReportGeneratedLog::find($other->id));
    }

    #[Test]
    public function sponsor_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = Sponsor::factory()->create(['agency_id' => $this->agency->id, 'company_name' => 'Own Sponsor']);
        $other = Sponsor::factory()->create(['agency_id' => $this->otherAgency->id, 'company_name' => 'Other Sponsor']);

        $names = Sponsor::query()->pluck('company_name')->all();

        $this->assertContains('Own Sponsor', $names);
        $this->assertNotContains('Other Sponsor', $names, 'Sponsor::query() returned another agency\'s sponsor');
        $this->assertNull(Sponsor::find($other->id));
        $this->assertSame($own->id, Sponsor::find($own->id)?->id);
    }

    #[Test]
    public function expense_request_queries_are_scoped_to_the_bound_agency(): void
    {
        $own   = ExpenseRequest::create($this->expensePayload($this->agency->id, 'OWN-0001'));
        $other = ExpenseRequest::create($this->expensePayload($this->otherAgency->id, 'OTHER-0001'));

        $refs = ExpenseRequest::query()->pluck('reference_no')->all();

        $this->assertContains('OWN-0001', $refs);
        $this->assertNotContains('OTHER-0001', $refs, 'ExpenseRequest::query() returned another agency\'s request');
        $this->assertNull(ExpenseRequest::find($other->id), 'ExpenseRequest::find() crossed the tenant boundary');
        $this->assertSame($own->id, ExpenseRequest::find($own->id)?->id);
    }

    /**
     * The 262 NULL-agency activity_logs rows are deliberately visible to NO
     * tenant: ActivityLog is outside TenantScope and its reads go through
     * forAgency(), which resolves a tenant actor to that agency only.
     */
    #[Test]
    public function activity_log_reads_never_show_another_agency_or_unattributed_rows(): void
    {
        $own = ActivityLog::create(['agency_id' => $this->agency->id, 'action' => 'own_row', 'description' => 'own']);
        $other = ActivityLog::create(['agency_id' => $this->otherAgency->id, 'action' => 'other_row', 'description' => 'other']);
        $orphan = ActivityLog::create(['agency_id' => null, 'action' => 'orphan_row', 'description' => 'nobody']);

        $actions = ActivityLog::query()->forAgency($this->agency->id)->pluck('action')->all();

        $this->assertContains($own->action, $actions);
        $this->assertNotContains($other->action, $actions, 'the sensitive-actions read returned another agency\'s row');
        $this->assertNotContains($orphan->action, $actions, 'the sensitive-actions read returned an unattributed (NULL agency) row');

        // The escape the card asked for: a null-agency actor sees exactly the
        // unattributed rows and nothing else.
        $orphanOnly = ActivityLog::query()->forAgency(null)->pluck('action')->all();
        $this->assertSame(['orphan_row'], $orphanOnly);

        // And they are not silently deleted or rewritten.
        $this->assertDatabaseHas('activity_logs', ['id' => $orphan->id, 'agency_id' => null]);
    }

    /** @return array<string, mixed> */
    private function expensePayload(int $agencyId, string $reference): array
    {
        // user_id is NOT NULL on expense_requests: a request is always encoded
        // by somebody's User. One user per agency keeps the fixtures honest.
        $encoder = User::firstOrCreate(
            ['email' => 'encoder@agency-' . $agencyId . '.test'],
            ['agency_id' => $agencyId, 'name' => 'Encoder ' . $agencyId, 'user_type' => 'staff', 'password' => bcrypt('password')],
        );

        return [
            'agency_id'    => $agencyId,
            'reference_no' => $reference,
            'date'         => '2026-11-20',
            'status'       => ExpenseRequest::STATUS_PENDING,
            'user_id'      => $encoder->id,
        ];
    }
}
