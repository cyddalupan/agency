<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Toybits 2026-10-06 (Gulf) — Cyd: "Add to Excel form header: Date Applied,
 * Passport #, Date Issued, Place Issued, Expiration" on the Add Applicant
 * bulk upload.
 */
class ApplicantBulkUploadTest extends TestCase
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
            ->post(route('applicants.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('applicants.csv', $content),
            ]);
    }

    #[Test]
    public function template_download_contains_the_new_columns(): void
    {
        $response = $this->actingAs($this->user)->get(route('applicants.bulk.template'));
        $response->assertOk();

        $body = $response->streamedContent();
        foreach (['Date Applied', 'Passport #', 'Date Issued', 'Place Issued', 'Expiration'] as $header) {
            $this->assertStringContainsString($header, $body);
        }
    }

    #[Test]
    public function import_stores_date_applied_and_passport_details(): void
    {
        $csv = "First Name,Last Name,Date Applied,Passport #,Date Issued,Place Issued,Expiration\n"
            ."Ana,Reyes,2026-09-15,P1234567A,2021-05-10,DFA Manila,2031-05-09\n";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('applicants.index'));
        $response->assertSessionHas('success');

        $applicant = Applicant::where('first_name', 'Ana')->first();
        $this->assertNotNull($applicant);
        $this->assertSame('2026-09-15', $applicant->created_at->format('Y-m-d'));
        $this->assertSame('with', $applicant->has_passport);

        $passport = $applicant->passport;
        $this->assertNotNull($passport);
        $this->assertSame('P1234567A', $passport->passport_no);
        $this->assertSame('2021-05-10', $passport->issue_date->format('Y-m-d'));
        $this->assertSame('2031-05-09', $passport->expiry_date->format('Y-m-d'));
        $this->assertSame('DFA Manila', $passport->place_of_issue);
    }

    #[Test]
    public function import_without_passport_or_date_applied_still_works(): void
    {
        $csv = "First Name,Last Name,Date Applied,Passport #,Date Issued,Place Issued,Expiration\n"
            ."Ben,Cruz,,,,,\n";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('applicants.index'));
        $applicant = Applicant::where('first_name', 'Ben')->first();
        $this->assertNotNull($applicant);
        $this->assertNull($applicant->has_passport);
        $this->assertDatabaseCount('applicant_passports', 0);
    }

    #[Test]
    public function import_rejects_expiration_before_date_issued(): void
    {
        $csv = "First Name,Last Name,Passport #,Date Issued,Expiration\n"
            ."Cara,Lopez,P999,2024-01-01,2020-01-01\n";

        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('bulk_errors');
        $this->assertDatabaseCount('applicants', 0);
    }

    #[Test]
    public function import_rejects_invalid_date_applied(): void
    {
        $csv = "First Name,Last Name,Date Applied\n"
            ."Dan,Vega,not-a-date\n";

        $response = $this->uploadCsv($csv);

        $response->assertSessionHas('bulk_errors');
        $this->assertDatabaseCount('applicants', 0);
    }

    /**
     * Cyd 2026-10-07 (Gulf) — a valid template file failed with "No header row
     * found" whose preview showed the header intact with commas. Root cause: the
     * file used lone-CR (\r) line endings (Excel for Mac), so fgetcsv read the
     * whole file as one row. The parser now normalizes CR/CRLF to LF.
     */
    #[Test]
    public function import_handles_lone_cr_line_endings(): void
    {
        $csv = "First Name,Last Name,Date Applied\r"
            ."Eve,Santos,2026-09-15\r";

        $response = $this->uploadCsv($csv);

        $response->assertRedirect(route('applicants.index'));
        $response->assertSessionHas('success');
        $this->assertNotNull(Applicant::where('first_name', 'Eve')->first());
    }
}
