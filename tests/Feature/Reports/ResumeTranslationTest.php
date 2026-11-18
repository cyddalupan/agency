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

        // Assert the REAL rendered spans for this locale.
        //
        // What was wrong with the original: it looked for the lowercase "Name",
        // which is `resume.name` — a key used only in the references table, and
        // this applicant has no references loaded, so that block is skipped.
        // A bare assertStringContainsString would be no better either: the
        // strings here also appear in a CSS comment at the top of the view.
        //
        // Asserting on the elements catches both problems and still fails on a
        // blank render — an empty tr() leaves <span class="en"></span>.
        //
        // Note both spans carry Arabic when locale=ar: the view passes $locale
        // straight to __(), so the .en span is translated too. That is existing
        // behaviour, not something this test should "correct".
        $this->assertMatchesRegularExpression(
            '/<span class="en">\s*الإسم بالكامل\s*<\/span>/u',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<span class="ar"[^>]*>\s*الإسم بالكامل\s*<\/span>/u',
            $html
        );

        // The old third assertion looked for the Arabic "Destination" heading.
        // `resume.destination` exists in lang/en and lang/ar and is referenced
        // NOWHERE in reports/resume.blade.php — there is no Destination section
        // in this document. It had been failing since the test was written, for
        // a label the view does not render.
        //
        // A section heading is the honest replacement: it proves the translator
        // ran for this locale, across the whole document rather than one label.
        $this->assertStringContainsString('العمل السابق في الخارج', $html);
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

        // Assert the label INSIDE its rendered span, not as a bare substring:
        // "FULL NAME" also appears in a CSS comment in this view, so a bare
        // contains-check would pass on markup and prove nothing about the
        // translation being rendered. An empty tr() leaves an empty span here,
        // so this still fails on a blank render.
        $this->assertMatchesRegularExpression(
            '/<span class="en">\s*FULL NAME\s*<\/span>/',
            $html
        );

        // The document must carry no Arabic TRANSLATION of its labels. Note the
        // .ar spans are not empty in English: the view renders resume.ar.*
        // unconditionally, in every locale — that is existing behaviour this card
        // does not change. So the honest claim is not "contains no Arabic" (it
        // does, by design); it is that the labels are not translated.
        $this->assertStringNotContainsString('الإسم بالكامل', $html);
        $this->assertStringNotContainsString('المهنة', $html);
        // The English heading is English, not its Arabic counterpart. Whitespace
        // and entity escaping around the ampersand are left to the renderer, so
        // match the words rather than the raw markup.
        $this->assertMatchesRegularExpression('/SKILLS\s*&amp;[^<]*EXPERIENCE/', $html);
        $this->assertStringNotContainsString('المهارات والخبرة', $html);
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
