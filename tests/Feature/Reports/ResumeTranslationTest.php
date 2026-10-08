<?php

namespace Tests\Feature\Reports;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "LANDAS: Resume" (2026-10-08).
 *
 * Requirement: the printable resume must be bilingual — field labels shown in
 * English plus the language of the destination country (the country of the
 * FRA / foreign employer), and it must render the agency letterhead/logo.
 *
 * The label locale is resolved from the destination country via
 * config('resume.locale_by_country'); Arabic-speaking destinations map to 'ar',
 * etc. Countries without a mapping fall back to the application locale.
 */
class ResumeTranslationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep the suite fast/deterministic: do not shell out to wkhtmltopdf.
        config()->set('resume.engine', 'dompdf');
    }

    #[Test]
    public function destination_country_maps_to_expected_locale(): void
    {
        $this->assertSame('ar', resume_locale_for_country('SA'));
        $this->assertSame('ar', resume_locale_for_country('AE'));
        $this->assertSame('ar', resume_locale_for_country('KW'));
        $this->assertSame('ar', resume_locale_for_country('QA'));
        $this->assertSame('zh', resume_locale_for_country('CN'));
        $this->assertSame('ja', resume_locale_for_country('JP'));
        $this->assertSame('ar', resume_locale_for_country('sa'), 'Mapping is case-insensitive');
    }

    #[Test]
    public function unmapped_or_empty_country_returns_null(): void
    {
        $this->assertNull(resume_locale_for_country('MY'));
        $this->assertNull(resume_locale_for_country('PH'));
        $this->assertNull(resume_locale_for_country(''));
        $this->assertNull(resume_locale_for_country(null));
    }

    #[Test]
    public function resume_labels_exist_for_every_supported_language(): void
    {
        $required = array_keys(require lang_path('en/resume.php'));

        foreach (array_keys(config('app.supported_languages')) as $locale) {
            $file = lang_path("{$locale}/resume.php");
            $this->assertFileExists($file, "Missing resume labels for [{$locale}]");

            $translations = require $file;
            $this->assertSame(
                [],
                array_diff($required, array_keys($translations)),
                "Locale [{$locale}] is missing resume label keys"
            );
        }
    }

    #[Test]
    public function arabic_locale_renders_arabic_field_labels(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->create([
            'agency_id' => $agency->id,
            'first_name' => 'JANICE',
            'last_name' => 'AREVALO',
        ]);

        $html = view('reports.resume', [
            'applicant' => $applicant,
            'agency'    => $agency,
            'locale'    => 'ar',
        ])->render();

        // English label + Arabic translation side by side (per the sample).
        $this->assertStringContainsString('Name', $html);
        $this->assertStringContainsString('الاسم', $html);
        // The "Destination" section heading is always rendered.
        $this->assertStringContainsString('الوجهة', $html);
    }

    #[Test]
    public function english_locale_renders_english_only(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->create(['agency_id' => $agency->id]);

        $html = view('reports.resume', [
            'applicant' => $applicant,
            'agency'    => $agency,
            'locale'    => 'en',
        ])->render();

        $this->assertStringContainsString('Name', $html);
        $this->assertStringNotContainsString('الاسم', $html);
    }

    #[Test]
    public function resume_route_returns_a_pdf_for_the_owning_agency(): void
    {
        $agency = Agency::factory()->create();
        $country = Country::factory()->create(['code' => 'SA', 'name' => 'Saudi Arabia']);
        $employer = Employer::factory()->create([
            'agency_id'  => $agency->id,
            'country_id' => $country->id,
        ]);
        $applicant = Applicant::factory()->create([
            'agency_id'   => $agency->id,
            'employer_id' => $employer->id,
            'country_id'  => $country->id,
        ]);
        $user = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.resume', $applicant));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    #[Test]
    public function resume_route_is_denied_across_agencies(): void
    {
        $agency = Agency::factory()->create();
        $other = Agency::factory()->create();
        $applicant = Applicant::factory()->create(['agency_id' => $other->id]);
        $user = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);

        $this->actingAs($user)
            ->get(route('reports.resume', $applicant))
            ->assertNotFound();
    }
}
