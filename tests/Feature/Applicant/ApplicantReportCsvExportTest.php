<?php

namespace Tests\Feature\Applicant;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Position;
use App\Models\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6afae2da1635171f28b37751.
 *
 * Covers ReportController::applicantsExport — the `applicants-report.csv`
 * download behind the Applicant Report screen (route reports.applicants.export).
 *
 * This is a DIFFERENT export from ApplicantCsvExportTest, which covers
 * ApplicantController::export (`applicants.csv`, route applicants.export).
 * Two routes, two files, two different column sets. Only the second had tests,
 * which is how the Status column sat broken at 'N/A' on every row: it read
 * statusCode?->name, and StatusCode has `label`, not `name`.
 */
class ApplicantReportCsvExportTest extends TestCase
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
        ]);

        app()->instance('tenant_agency', $this->agency);
    }

    private function exportCsv(): string
    {
        $response = $this->actingAs($this->user)
            ->get(route('reports.applicants.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        return $response->streamedContent();
    }

    /** @return array<int, string> */
    private function headerColumns(string $csv): array
    {
        $first = explode("\n", trim($csv))[0];
        // Strip the UTF-8 BOM Excel needs before parsing.
        $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);

        return str_getcsv($first);
    }

    #[Test]
    public function unauthenticated_user_cannot_export(): void
    {
        $this->get(route('reports.applicants.export'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function the_export_serves_the_report_filename(): void
    {
        $this->actingAs($this->user)
            ->get(route('reports.applicants.export'))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=applicants-report.csv');
    }

    #[Test]
    public function the_columns_are_the_ones_the_screen_shows_in_the_same_order(): void
    {
        // The defect this card exists for: the CSV emitted a different column
        // set from the table the operator was looking at. Pin the exact list.
        $columns = $this->headerColumns($this->exportCsv());

        $this->assertSame([
            'Name',
            'Position',
            agent_header('Agent'),
            'Branch',
            'Status',
            'Country',
            'Contact',
            'Email',
            'Created At',
        ], $columns);
    }

    #[Test]
    public function gender_and_employer_are_no_longer_emitted(): void
    {
        // They appear on no screen. Dropping them is deliberate (Cyd's call):
        // a spreadsheet depending on columns nobody can see is not a contract
        // worth preserving.
        $columns = $this->headerColumns($this->exportCsv());

        $this->assertNotContains('Gender', $columns);
        $this->assertNotContains('Employer', $columns);
        $this->assertNotContains('First Name', $columns);
        $this->assertNotContains('Last Name', $columns);
    }

    #[Test]
    public function status_renders_its_label_rather_than_na(): void
    {
        // The actual bug. statusCode?->name is always null because StatusCode
        // has no `name` column, so every row read 'N/A'. This fails on the old
        // code and on any regression that swaps ->label back to ->name.
        StatusCode::create(['code' => 9001, 'label' => 'On Process']);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 9001,
        ]);
        $this->assertSame(9001, (int) $applicant->fresh()->status_code);

        $csv = $this->exportCsv();

        $this->assertStringContainsString('On Process', $csv);
        $this->assertStringNotContainsString('N/A', $csv);
    }

    #[Test]
    public function a_row_carries_position_agent_and_branch(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $country = Country::factory()->create(['name' => 'Saudi Arabia']);
        $position = Position::factory()->create(['name' => 'Housemaid']);

        $agent = \App\Models\Agent::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Maria Santos',
        ]);

        Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'first_name'  => 'Juan',
            'last_name'   => 'Dela Cruz',
            'contact'     => '09171234567',
            'email'       => 'juan@example.com',
            'branch_id'   => $branch->id,
            'country_id'  => $country->id,
            'position_id' => $position->id,
            'agent_id'    => $agent->id,
        ]);

        $csv = $this->exportCsv();

        // Name is one column: trimmed first + last, not two fields.
        $this->assertStringContainsString('Juan Dela Cruz', $csv);
        $this->assertStringContainsString('Housemaid', $csv);
        $this->assertStringContainsString('Maria Santos', $csv);
        $this->assertStringContainsString($branch->name, $csv);
        $this->assertStringContainsString('Saudi Arabia', $csv);
        $this->assertStringContainsString('09171234567', $csv);
        $this->assertStringContainsString('juan@example.com', $csv);
    }

    #[Test]
    public function every_data_row_has_the_same_column_count_as_the_header(): void
    {
        // Guards the whole class of bug this card is about: a header that does
        // not line up with the rows shifts every value one column left in Excel.
        StatusCode::create(['code' => 9002, 'label' => 'Deployed']);
        Applicant::factory()->count(3)->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 9002,
        ]);

        $lines = array_values(array_filter(explode("\n", trim($this->exportCsv()))));
        $headerCount = count($this->headerColumns($this->exportCsv()));

        $this->assertGreaterThan(1, count($lines), 'Expected a header and data rows.');

        foreach ($lines as $i => $line) {
            $cells = str_getcsv(preg_replace('/^\xEF\xBB\xBF/', '', $line));
            $this->assertCount(
                $headerCount,
                $cells,
                "Line {$i} has ".count($cells)." columns but the header has {$headerCount}."
            );
        }
    }

    #[Test]
    public function the_export_is_tenant_scoped(): void
    {
        Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Our',
            'last_name'  => 'Applicant',
        ]);

        $otherAgency = Agency::factory()->create();
        Applicant::factory()->create([
            'agency_id'  => $otherAgency->id,
            'first_name' => 'Other Agency',
            'last_name'  => 'Applicant',
        ]);

        $csv = $this->exportCsv();

        $this->assertStringContainsString('Our Applicant', $csv);
        $this->assertStringNotContainsString('Other Agency Applicant', $csv);
    }

    #[Test]
    public function an_empty_result_still_writes_the_header_row(): void
    {
        $csv = $this->exportCsv();

        $lines = array_values(array_filter(explode("\n", trim($csv))));
        $this->assertCount(1, $lines, 'An empty export must still contain the header row.');
        $this->assertSame('Name', $this->headerColumns($csv)[0]);
    }
}
