<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\StatusCode;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Withdrawn & Repat visibility (Toybits report 2026-08-10).
 *
 * 1. The Status tab dropdown must show the FULL status list (same as Add/Edit),
 *    never a subset filtered by agency settings.
 * 2. Applicants with withdrawn statuses (35 Repatriated, 38 Cancel, 50 Backout)
 *    must appear ONLY on the Withdrawn & Repat tab — never on the main
 *    applicants page or its CSV export.
 * 3. The Repat checkbox on the Status tab is redundant (status 35 Repatriated
 *    in the dropdown already covers it; the repat boolean was never read
 *    anywhere) and must be removed.
 */
class WithdrawnRepatVisibilityTest extends TestCase
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

    private function applicantWithStatus(int $code, string $firstName = 'DefaultName'): Applicant
    {
        return Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => $code,
            'first_name'  => $firstName,
        ]);
    }

    // ---- Issue 1: Status tab dropdown = full list ----

    #[Test]
    public function status_tab_dropdown_shows_full_status_list_even_when_agency_restricts(): void
    {
        // Agency restricts status codes to [0] only.
        $settings = $this->agency->settings ?? [];
        $settings['applicant_form_defaults'] = [
            'status_codes' => [0],
        ];
        $this->agency->update(['settings' => $settings]);

        // A status outside the agency's configured subset.
        StatusCode::create(['code' => 900, 'label' => 'Only B', 'sort_order' => 900]);

        $applicant = $this->applicantWithStatus(0);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant))
            ->assertOk()
            ->getContent();

        // The dropdown must include statuses outside the agency subset (full list),
        // matching the Add/Edit Applicant form.
        $this->assertMatchesRegularExpression('/value="900"[^>]*>\s*Only B\s*<\/option>/', $html);
        $this->assertMatchesRegularExpression('/value="35"[^>]*>\s*Repatriated\s*<\/option>/', $html);
    }

    // ---- Issue 2: withdrawn statuses only on the Withdrawn & Repat tab ----

    #[Test]
    public function main_applicants_index_excludes_withdrawn_and_repatriated(): void
    {
        $normal  = $this->applicantWithStatus(0, 'NormalActive');
        $repat   = $this->applicantWithStatus(35, 'RepatHidden');
        $cancel  = $this->applicantWithStatus(38, 'CancelHidden');
        $backout = $this->applicantWithStatus(50, 'BackoutHidden');

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertSee($normal->first_name);
        $response->assertDontSee($repat->first_name);
        $response->assertDontSee($cancel->first_name);
        $response->assertDontSee($backout->first_name);
    }

    #[Test]
    public function main_applicants_index_does_not_offer_withdrawn_status_chips(): void
    {
        // A normal applicant is required for the filter form to render at all.
        $this->applicantWithStatus(0, 'NormalActive');
        $this->applicantWithStatus(35, 'RepatHidden');

        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();

        // Intent: withdrawn/backout statuses must not be offered as filter
        // chips/options on the main index. Scope to the status filter options
        // (the sidebar nav now legitimately contains "Backout, Cancelled & Repat").
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();

        $statusOptions = [];
        foreach ($dom->getElementsByTagName('select') as $select) {
            if ($select->getAttribute('name') === 'status') {
                foreach ($select->getElementsByTagName('option') as $option) {
                    $statusOptions[] = trim($option->textContent);
                }
            }
        }

        $this->assertNotEmpty($statusOptions, 'Status filter options should be rendered.');
        $this->assertNotContains('Backout', $statusOptions);
        $this->assertNotContains('Repatriated', $statusOptions);
    }

    #[Test]
    public function main_index_status_filter_never_reveals_withdrawn_applicants(): void
    {
        $repat = $this->applicantWithStatus(35, 'RepatHidden');

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index', ['status' => 35]));

        $response->assertOk();
        $response->assertDontSee($repat->first_name);
    }

    #[Test]
    public function main_applicants_export_excludes_withdrawn_and_repatriated(): void
    {
        $normal = $this->applicantWithStatus(0, 'NormalExport');
        $repat  = $this->applicantWithStatus(35, 'RepatExportHidden');
        $cancel = $this->applicantWithStatus(38, 'CancelExportHidden');

        $response = $this->actingAs($this->user)->get(route('applicants.export'));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('NormalExport', $csv);
        $this->assertStringNotContainsString('RepatExportHidden', $csv);
        $this->assertStringNotContainsString('CancelExportHidden', $csv);
    }

    // ---- Issue 3: Repat checkbox removed, Repatriated stays in the dropdown ----

    #[Test]
    public function status_tab_has_no_repat_checkbox_or_repat_date(): void
    {
        $applicant = $this->applicantWithStatus(0);

        $html = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('name="repat"', $html);
        $this->assertStringNotContainsString('name="repat_date"', $html);

        // Repatriated remains available via the status dropdown.
        $this->assertMatchesRegularExpression('/value="35"[^>]*>\s*Repatriated\s*<\/option>/', $html);
    }

    #[Test]
    public function posting_repat_field_is_ignored_by_status_update(): void
    {
        $applicant = $this->applicantWithStatus(0);

        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 1,
                'repat'       => '1',
                'repat_date'  => '2026-08-10',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Status saved; the obsolete repat flag is NOT written.
        $fresh = $applicant->fresh();
        $this->assertSame(1, $fresh->status_code);
        $this->assertFalse((bool) $fresh->repat);
        $this->assertNull($fresh->repat_date);
    }

    #[Test]
    public function updating_status_to_repatriated_works_and_stays_off_main_index(): void
    {
        $applicant = $this->applicantWithStatus(0, 'GoingRepat');

        $response = $this->actingAs($this->user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => 35,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(35, $applicant->fresh()->status_code);

        // Appears on the Withdrawn & Repat tab...
        $withdrawn = $this->actingAs($this->user)->get(route('applicants.withdrawn'));
        $withdrawn->assertSee('GoingRepat');

        // ...but not on the main applicants page.
        $index = $this->actingAs($this->user)->get(route('applicants.index'));
        $index->assertDontSee('GoingRepat');
    }
}
