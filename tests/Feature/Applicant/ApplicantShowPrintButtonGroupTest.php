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
 * Toybits request 2026-08-16: on the applicant show page, "Generate CV" and
 * "Expense Report" should be grouped together (both are print actions),
 * while "Edit" is a totally different feature.
 */
class ApplicantShowPrintButtonGroupTest extends TestCase
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

    #[Test]
    public function show_page_groups_cv_and_expense_report_under_print_label(): void
    {
        $applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)->get(route('applicants.show', $applicant));
        $response->assertOk();

        $html = $response->getContent();

        // Both print links exist.
        $this->assertStringContainsString(route('reports.resume', $applicant), $html);
        $this->assertStringContainsString(route('reports.expense-report', $applicant), $html);

        // They live inside a common "print group" container with a Print label.
        preg_match('/<div[^>]*print-group[^>]*>(.*?)<\/div>\s*<\/div>/s', $html, $m);
        $group = $m[1] ?? '';
        $this->assertNotSame('', $group, 'print-group block should exist');
        $this->assertStringContainsString('Generate CV', $group);
        $this->assertStringContainsString('Expense Report', $group);

        // The Edit link is NOT inside the print group — it is a separate feature.
        $editUrl = route('applicants.edit', $applicant);
        $this->assertStringNotContainsString($editUrl, $group);
        $this->assertStringContainsString($editUrl, $html);
    }
}
