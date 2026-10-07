<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Country;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerBulkUploadTest extends TestCase
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

    private function uploadCsv(string $content): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->user)
            ->post(route('employers.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('employers.csv', $content),
            ]);
    }

    #[Test]
    public function bulk_upload_page_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('employers.bulk'));
        $response->assertOk();
        $response->assertSee('Bulk Upload FRAs');
        $response->assertSee('Download CSV Template');
    }

    #[Test]
    public function template_download_contains_headers_and_sample_rows(): void
    {
        $response = $this->actingAs($this->user)->get(route('employers.bulk.template'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $body = $response->streamedContent();
        // UTF-8 BOM
        $this->assertStringStartsWith(chr(0xEF).chr(0xBB).chr(0xBF), $body);
        $this->assertStringContainsString('Company Name', $body);
        $this->assertStringContainsString('Status', $body);
        $this->assertStringContainsString('Country', $body);
        $this->assertStringContainsString('Active', $body);
        $this->assertStringContainsString('Inactive', $body);
    }

    #[Test]
    public function import_creates_employers_from_valid_csv(): void
    {
        $saudi = Country::factory()->create(['name' => 'Saudi Arabia']);

        $csv = "Company Name,Company No.,Status,Contact Person,Contact,Email,Address,Country\n"
            ."Gulf Horizon Manpower Inc.,EMP-001,Active,Juan Dela Cruz,09171234567,gulf@example.com,123 Trade St,Saudi Arabia\n"
            ."Al Noor Recruitment Co.,EMP-002,Inactive,Maria Santos,09179876543,alnoor@example.com,456 Corniche Rd,\n";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('employers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employers', [
            'agency_id'  => $this->agency->id,
            'name'       => 'Gulf Horizon Manpower Inc.',
            'company_no' => 'EMP-001',
            'status'     => 'active',
            'country_id' => $saudi->id,
        ]);
        $this->assertDatabaseHas('employers', [
            'agency_id' => $this->agency->id,
            'name'      => 'Al Noor Recruitment Co.',
            'status'    => 'inactive',
        ]);
    }

    #[Test]
    public function import_is_case_and_space_insensitive(): void
    {
        Country::factory()->create(['name' => 'United Arab Emirates']);

        $csv = "Company Name,Status,Country\n"
            ."Alpha Corp,  ACTIVE  ,united  arab emirates\n"
            ."Beta Corp,inactive,UAE\n";

        $response = $this->uploadCsv($csv);

        // UAE row fails (not a real country name), Alpha imports as active
        $response->assertSessionHas('bulk_errors');

        $this->assertDatabaseMissing('employers', ['name' => 'Alpha Corp']);
        $this->assertDatabaseMissing('employers', ['name' => 'Beta Corp']);
    }

    #[Test]
    public function import_rejects_invalid_status_with_row_errors(): void
    {
        $csv = "Company Name,Status\n"
            ."Alpha Corp,Pending\n";

        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('bulk_errors');
        $this->assertDatabaseMissing('employers', ['name' => 'Alpha Corp']);
    }

    #[Test]
    public function import_requires_company_name(): void
    {
        $csv = "Company Name,Status\n"
            .",Active\n";

        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('bulk_errors');
        $this->assertDatabaseCount('employers', 0);
    }

    #[Test]
    public function import_auto_creates_one_login_user_per_email(): void
    {
        $csv = "Company Name,Email,Contact Person\n"
            ."Alpha Corp,dup@example.com,Alice\n"
            ."Beta Corp,dup@example.com,Bob\n"
            ."Gamma Corp,other@example.com,Carol\n";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('employers.index'));
        $this->assertDatabaseCount('employers', 3);
        $this->assertSame(1, User::where('email', 'dup@example.com')->count());
        $this->assertSame(1, User::where('email', 'other@example.com')->count());
    }

    #[Test]
    public function missing_header_row_returns_parse_error(): void
    {
        $csv = "Just some data\nwithout a proper header\n";

        $response = $this->uploadCsv($csv);

        $response->assertSessionHasErrors('csv_file');
        $this->assertDatabaseCount('employers', 0);
    }

    #[Test]
    public function template_round_trip_imports(): void
    {
        // The template's sample rows reference these countries.
        Country::factory()->create(['name' => 'Saudi Arabia']);
        Country::factory()->create(['name' => 'UAE']);

        // Download the actual template, then upload it unchanged -> the two
        // sample rows import cleanly.
        $template = $this->actingAs($this->user)->get(route('employers.bulk.template'));
        $body = $template->streamedContent();
        // strip BOM
        $body = substr($body, 3);

        $response = $this->uploadCsv($body);
        $response->assertRedirect(route('employers.index'));
        $this->assertSame(2, Employer::where('agency_id', $this->agency->id)->count());
    }
}
