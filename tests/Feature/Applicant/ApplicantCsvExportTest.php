<?php

namespace Tests\Feature\Applicant;

use App\Models\Applicant;
use App\Models\Agency;
use App\Models\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantCsvExportTest extends TestCase
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
    }

    #[Test]
    public function unauthenticated_user_cannot_export_csv(): void
    {
        $response = $this->get(route('applicants.export'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function export_returns_csv_with_correct_headers(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=applicants.csv');
    }

    #[Test]
    #[DataProvider('tenantHeaderKinds')]
    public function export_contains_expected_column_headers(string $subdomain, string $agentHeader): void
    {
        // The agent column is agent_header('Agent'), which prints "Marketing
        // Staff" for finas/csdi (ModuleAccess::RENAME_AGENCIES) and is quoted
        // by fputcsv because of the space. The default factory subdomain is a
        // random word, so it can never produce either rename tenant — testing
        // only the default silently left the other vocabulary unasserted.
        $agency = Agency::factory()->create(['subdomain' => $subdomain]);
        $user = User::factory()->create(['agency_id' => $agency->id]);

        app()->instance('tenant_agency', $agency);

        Applicant::factory()->create([
            'agency_id' => $agency->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        // Anchor on the exact header line, not a loose `Name` substring: a
        // regression back to "First Name"/"Last Name" also contains "Name".
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $lines = explode("\n", $content);
        $this->assertSame(
            'Name,Contact#,Gender,Age,Branch,' . $agentHeader . ',Position,Country,FRA,Status,"Created At",Encoder',
            trim(str_replace("\xEF\xBB\xBF", '', $lines[0]))
        );
    }

    public static function tenantHeaderKinds(): array
    {
        // finas is quoted because fputcsv quotes a field containing a space.
        return [
            'default tenant keeps Agent'         => ['acme', 'Agent'],
            'rename tenant prints Marketing Staff' => ['finas', '"Marketing Staff"'],
        ];
    }

    #[Test]
    public function export_contains_applicant_data(): void
    {
        app()->instance('tenant_agency', $this->agency);

        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'email'      => 'juan@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        // Assert the data row's first field, which is the joined name. The old
        // form checked for 'Juan' and 'Dela Cruz' anywhere in the CSV, which
        // would also pass if the two landed in separate columns — the exact
        // regression 52b47b7b removed.
        $lines = array_values(array_filter(explode("\n", $content)));
        $row = str_getcsv(str_replace("\xEF\xBB\xBF", '', $lines[1]));
        $this->assertSame('Juan Dela Cruz', $row[0],
            'The name must be a single joined column, not split first/last.');
    }

    #[Test]
    public function export_is_tenant_scoped(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Our Applicant',
        ]);

        $otherAgency = Agency::factory()->create();
        Applicant::factory()->create([
            'agency_id'  => $otherAgency->id,
            'first_name' => 'Other Agency Applicant',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Our Applicant', $content);
        $this->assertStringNotContainsString('Other Agency Applicant', $content);
    }

    #[Test]
    public function export_handles_no_applicants_gracefully(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        // Export prefixes a UTF-8 BOM for Excel; assert it is present rather
        // than trimming it away, so dropping it is a visible failure.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $lines = array_values(array_filter(explode("\n", $content)));
        $this->assertSame(
            'Name,Contact#,Gender,Age,Branch,Agent,Position,Country,FRA,Status,"Created At",Encoder',
            trim(str_replace("\xEF\xBB\xBF", '', $lines[0]))
        );
        // Should have header row but no data rows
        $this->assertCount(1, $lines);
    }
}
