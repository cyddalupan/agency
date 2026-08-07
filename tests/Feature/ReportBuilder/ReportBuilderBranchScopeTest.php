<?php

namespace Tests\Feature\ReportBuilder;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\ReportTemplate;
use App\Models\User;
use App\Services\ReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Reports must be branch-scoped for branch accounts.
 *
 * A branch user's report page should only show data from their branch
 * (confirmed by Cyd 2026-08-07). Agency admin still sees all agency data.
 */
class ReportBuilderBranchScopeTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Branch $branchA;
    private Branch $branchB;
    private User $agencyAdmin; // no branch
    private User $branchUserA; // branch_id = branchA

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['subdomain' => 'report-branch', 'name' => 'Report Branch Agency']);
        $this->branchA = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'QC Branch']);
        $this->branchB = Branch::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Cebu Branch']);

        $this->agencyAdmin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'branch_id' => null,
        ]);

        $this->branchUserA = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
            'branch_id' => $this->branchA->id,
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function applicant(?int $branchId, string $name, array $extra = []): Applicant
    {
        return Applicant::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'branch_id' => $branchId,
            'first_name' => $name,
            'last_name' => 'Test',
        ], $extra));
    }

    private function applicantReportTemplate(): ReportTemplate
    {
        return ReportTemplate::factory()->create([
            'agency_id' => $this->agency->id,
            'config' => [
                'columns' => ['name', 'email', 'status'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
        ]);
    }

    // ─── ReportBuilder (template-based preview/pdf/csv) ──────────────

    #[Test]
    public function branch_user_report_builder_only_returns_own_branch_applicants(): void
    {
        $this->actingAs($this->branchUserA);
        $this->applicant($this->branchA->id, 'Alice'); // own branch
        $this->applicant($this->branchB->id, 'Bob');   // other branch
        $this->applicant(null, 'Carol');               // unassigned

        $template = $this->applicantReportTemplate();
        $rows = app(ReportBuilder::class)->fromTemplate($template)->get();

        $names = $rows->pluck('name')->implode(', ');

        $this->assertStringContainsString('Alice', $names);
        $this->assertStringNotContainsString('Bob', $names);
        $this->assertStringNotContainsString('Carol', $names);
        $this->assertCount(1, $rows);
    }

    #[Test]
    public function agency_admin_report_builder_returns_all_agency_applicants(): void
    {
        $this->actingAs($this->agencyAdmin);
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');
        $this->applicant(null, 'Carol');

        $template = $this->applicantReportTemplate();
        $rows = app(ReportBuilder::class)->fromTemplate($template)->get();

        $names = $rows->pluck('name')->implode(', ');

        $this->assertStringContainsString('Alice', $names);
        $this->assertStringContainsString('Bob', $names);
        $this->assertStringContainsString('Carol', $names);
        $this->assertCount(3, $rows);
    }

    // ─── ReportController::applicants (reports.applicants) ───────────

    #[Test]
    public function branch_user_report_applicants_page_only_has_own_branch(): void
    {
        $this->actingAs($this->branchUserA);
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');
        $this->applicant(null, 'Carol');

        $response = $this->get(route('reports.applicants'));

        $response->assertOk();
        $applicants = $response->viewData('applicants');
        $this->assertCount(1, $applicants);
        $this->assertEquals('Alice', $applicants->first()->first_name);
    }

    #[Test]
    public function agency_admin_report_applicants_page_has_all_applicants(): void
    {
        $this->actingAs($this->agencyAdmin);
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');
        $this->applicant(null, 'Carol');

        $response = $this->get(route('reports.applicants'));

        $response->assertOk();
        $this->assertCount(3, $response->viewData('applicants'));
    }

    // ─── ReportController::statistics (reports.statistics) ───────────

    #[Test]
    public function branch_user_report_statistics_are_branch_scoped(): void
    {
        $this->actingAs($this->branchUserA);
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');
        $this->applicant(null, 'Carol');

        $response = $this->get(route('reports.statistics'));

        $response->assertOk();
        $total = $response->viewData('totalApplicants') ?? null;
        $this->assertSame(1, $total, 'Branch user statistics should count only their own branch.');
    }

    #[Test]
    public function agency_admin_report_statistics_count_all(): void
    {
        $this->actingAs($this->agencyAdmin);
        $this->applicant($this->branchA->id, 'Alice');
        $this->applicant($this->branchB->id, 'Bob');
        $this->applicant(null, 'Carol');

        $response = $this->get(route('reports.statistics'));

        $response->assertOk();
        $total = $response->viewData('totalApplicants') ?? null;
        $this->assertSame(3, $total);
    }
}
