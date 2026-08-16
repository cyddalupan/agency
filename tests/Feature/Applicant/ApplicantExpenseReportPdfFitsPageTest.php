<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits request 2026-08-16: the expense report PDF at
 * /reports/expense-report/{id} overflows the A4 paper (print gets cut).
 * The layout must fit within the page.
 */
class ApplicantExpenseReportPdfFitsPageTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function renderReportView(): string
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        return view('reports.applicant_expense_report', [
            'applicant'           => $applicant,
            'statementItems'      => collect(),
            'statementTotals'     => collect(),
            'statementGrandTotal' => 0.0,
            'agentItems'          => collect(),
            'agentGrandTotal'     => 0.0,
        ])->render();
    }

    #[Test]
    public function report_view_defines_a4_page_with_margins(): void
    {
        $css = $this->renderReportView();

        // A fixed 210mm width + dompdf's default margins overflows A4; the
        // page must be declared with margins and the sheet must not exceed them.
        $this->assertMatchesRegularExpression(
            '/@page\s*\{[^}]*size:\s*A4[^}]*margin:/is',
            $css,
            '@page rule should declare A4 size with margins'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.page\s*\{\s*width:\s*210mm/i',
            $css,
            '.page must not force a fixed 210mm width (that is what overflows A4)'
        );
    }

    #[Test]
    public function report_view_keeps_tables_within_page_width(): void
    {
        $css = $this->renderReportView();

        // Long Particular/Description text must wrap instead of pushing the
        // table wider than the printable area.
        $this->assertMatchesRegularExpression(
            '/table-layout\s*:\s*fixed/i',
            $css,
            'tables should use fixed layout so columns never exceed the page'
        );
        $this->assertMatchesRegularExpression(
            '/word-break|overflow-wrap/i',
            $css,
            'long text must be allowed to wrap inside cells'
        );
    }
}
