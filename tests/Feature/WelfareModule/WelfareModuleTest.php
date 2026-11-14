<?php

namespace Tests\Feature\WelfareModule;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\ApplicantWelfareRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af81f6a — 'FINAS, CSDI: WELFARE module'.
 *
 * "Separate the Welfare from the Personal Information of the Applicants and
 * create a new module named WELFARE under BACKOUT, CANCELLED & REPAT... Add
 * Create Welfare button... upload document below remarks... filters... CSV."
 * "This is only for FINAS and CSDI."
 */
class WelfareModuleTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => ucfirst($subdomain), 'status' => 'active']);
    }

    private function admin(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
    }

    private function record(Agency $agency, array $overrides = []): ApplicantWelfareRecord
    {
        return ApplicantWelfareRecord::create(array_merge([
            'agency_id'               => $agency->id,
            'ofw_full_name'           => 'Maria Santos',
            'passport_number'         => 'P1234567A',
            'dmw_ereg_id'             => 'DMW-99110',
            'country_of_destination'  => 'KSA',
            'job_site_city'           => 'Riyadh',
            'position_designation'    => 'Domestic Helper',
            'foreign_recruitment_agency' => 'Al Rajhi Manpower',
            'remarks'                 => 'Wages delayed by two weeks.',
            'date_of_contact'         => now()->toDateString(),
        ], $overrides));
    }

    // ---------------------------------------------------------------- access

    #[Test]
    public function finas_and_csdi_can_open_the_welfare_module(): void
    {
        foreach (['finas', 'csdi'] as $subdomain) {
            $user = $this->admin($this->agency($subdomain));

            $this->actingAs($user)->get(route('welfare.index'))->assertOk();
            $this->actingAs($user)->get(route('welfare.create'))->assertOk();
        }
    }

    #[Test]
    public function other_tenants_are_refused_even_as_admin(): void
    {
        foreach (['gulf', 'omnis'] as $subdomain) {
            $user = $this->admin($this->agency($subdomain));

            $this->actingAs($user)->get(route('welfare.index'))->assertForbidden();
            $this->actingAs($user)->get(route('welfare.create'))->assertForbidden();
        }
    }

    #[Test]
    public function the_sidebar_link_only_appears_where_the_module_exists(): void
    {
        $finas = $this->admin($this->agency('finas'));
        $this->actingAs($finas)->get(route('agency.dashboard'))
            ->assertOk()->assertSee(route('welfare.index'), false);

        $gulf = $this->admin($this->agency('gulf'));
        $this->actingAs($gulf)->get(route('agency.dashboard'))
            ->assertOk()->assertDontSee(route('welfare.index'), false);
    }

    // ------------------------------------------------------------ create + csv

    #[Test]
    public function all_cards_columns_are_shown_in_the_table(): void
    {
        $agency = $this->agency('finas');
        $this->record($agency);

        $this->actingAs($this->admin($agency))->get(route('welfare.index'))
            ->assertOk()
            ->assertSee('Name')
            ->assertSee('Passport No.')
            ->assertSee('DMW E-Reg ID')
            ->assertSee('Country')
            ->assertSee('Job Site City')
            ->assertSee('Position')
            ->assertSee('FRA')
            ->assertSee('Remarks / Issues / Actions Taken')
            ->assertSee('Create Welfare')
            ->assertSee('Maria Santos');
    }

    #[Test]
    public function a_welfare_record_can_be_created_with_an_uploaded_document(): void
    {
        Storage::fake('public');
        $agency = $this->agency('finas');

        $this->actingAs($this->admin($agency))->post(route('welfare.store'), [
            'ofw_full_name'              => 'Ana Reyes',
            'passport_number'            => 'P7654321B',
            'dmw_ereg_id'                => 'DMW-55221',
            'country_of_destination'     => 'Qatar',
            'job_site_city'              => 'Doha',
            'position_designation'       => 'Staff Nurse',
            'foreign_recruitment_agency' => 'Gulf Star Agency',
            'remarks'                    => 'Medical issue, referred to clinic.',
            'date_of_contact'            => '2026-11-10',
            'document'                   => UploadedFile::fake()->create('case-report.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('welfare.index'));

        $rec = ApplicantWelfareRecord::where('ofw_full_name', 'Ana Reyes')->firstOrFail();

        $this->assertSame($agency->id, (int) $rec->agency_id);
        $this->assertSame('P7654321B', $rec->passport_number);
        $this->assertSame('Qatar', $rec->country_of_destination);
        $this->assertNotNull($rec->document_path);
        $this->assertSame('case-report.pdf', $rec->document_name);
        Storage::disk('public')->assertExists($rec->document_path);
        // Standalone: no applicant required.
        $this->assertNull($rec->applicant_id);
    }

    #[Test]
    public function a_welfare_record_can_be_created_from_an_applicant(): void
    {
        $agency    = $this->agency('csdi');
        $applicant = Applicant::factory()->create(['agency_id' => $agency->id, 'first_name' => 'Jose', 'last_name' => 'Cruz']);

        $this->actingAs($this->admin($agency))->post(route('welfare.store'), [
            'applicant_id'     => $applicant->id,
            'ofw_full_name'    => 'Jose Cruz',
            'date_of_contact'  => now()->toDateString(),
        ])->assertRedirect(route('welfare.index'));

        $rec = ApplicantWelfareRecord::where('ofw_full_name', 'Jose Cruz')->firstOrFail();
        $this->assertSame($applicant->id, (int) $rec->applicant_id);
    }

    #[Test]
    public function extract_to_csv_returns_the_table_columns(): void
    {
        $agency = $this->agency('finas');
        $this->record($agency);

        $response = $this->actingAs($this->admin($agency))->get(route('welfare.export'));
        $response->assertOk();

        $csv = $response->streamedContent();
        // fputcsv quotes any header containing a space or dot.
        $this->assertStringContainsString('"Passport No."', $csv);
        $this->assertStringContainsString('"DMW E-Reg ID"', $csv);
        $this->assertStringContainsString('"Job Site City"', $csv);
        $this->assertStringContainsString('"Remarks / Issues / Actions Taken"', $csv);
        $this->assertStringContainsString('Maria Santos', $csv);
        $this->assertStringContainsString('P1234567A', $csv);
        $this->assertStringContainsString('Riyadh', $csv);
    }

    // --------------------------------------------------------------- filters

    #[Test]
    public function each_card_filter_narrows_the_table(): void
    {
        $agency = $this->agency('finas');
        $this->record($agency, [
            'ofw_full_name' => 'Maria Santos', 'passport_number' => 'P1234567A',
            'dmw_ereg_id' => 'DMW-99110', 'country_of_destination' => 'KSA',
            'position_designation' => 'Domestic Helper', 'foreign_recruitment_agency' => 'Al Rajhi Manpower',
        ]);
        $this->record($agency, [
            'ofw_full_name' => 'Ana Reyes', 'passport_number' => 'P7654321B',
            'dmw_ereg_id' => 'DMW-55221', 'country_of_destination' => 'Qatar',
            'position_designation' => 'Staff Nurse', 'foreign_recruitment_agency' => 'Gulf Star Agency',
        ]);

        $user = $this->admin($agency);

        $this->actingAs($user)->get(route('welfare.index', ['name' => 'Maria']))
            ->assertSee('Maria Santos')->assertDontSee('Ana Reyes');
        $this->actingAs($user)->get(route('welfare.index', ['passport' => 'P7654321B']))
            ->assertSee('Ana Reyes')->assertDontSee('Maria Santos');
        $this->actingAs($user)->get(route('welfare.index', ['dmw_ereg_id' => 'DMW-99110']))
            ->assertSee('Maria Santos')->assertDontSee('Ana Reyes');
        $this->actingAs($user)->get(route('welfare.index', ['country' => 'Qatar']))
            ->assertSee('Ana Reyes')->assertDontSee('Maria Santos');
        $this->actingAs($user)->get(route('welfare.index', ['position' => 'Staff Nurse']))
            ->assertSee('Ana Reyes')->assertDontSee('Maria Santos');
        $this->actingAs($user)->get(route('welfare.index', ['fra' => 'Al Rajhi']))
            ->assertSee('Maria Santos')->assertDontSee('Ana Reyes');
    }

    // --------------------------------------------------------------- tenancy

    #[Test]
    public function records_never_leak_between_agencies(): void
    {
        $finas = $this->agency('finas');
        $csdi  = $this->agency('csdi');
        $foreign = $this->record($csdi, ['ofw_full_name' => 'CSDI Only Person']);

        $user = $this->admin($finas);

        $this->actingAs($user)->get(route('welfare.index'))
            ->assertOk()->assertDontSee('CSDI Only Person');

        $this->actingAs($user)->get(route('welfare.edit', $foreign))->assertForbidden();
        $this->actingAs($user)->put(route('welfare.update', $foreign), ['ofw_full_name' => 'Hijacked'])->assertForbidden();
        $this->actingAs($user)->delete(route('welfare.destroy', $foreign))->assertForbidden();

        $this->assertSame('CSDI Only Person', $foreign->fresh()->ofw_full_name);

        // CSV is scoped too.
        $csv = $this->actingAs($user)->get(route('welfare.export'))->streamedContent();
        $this->assertStringNotContainsString('CSDI Only Person', $csv);
    }

    #[Test]
    public function deleting_a_record_removes_its_document(): void
    {
        Storage::fake('public');
        $agency = $this->agency('finas');
        $rec = $this->record($agency, ['document_path' => 'welfare/8/x.pdf', 'document_name' => 'x.pdf']);
        Storage::disk('public')->put('welfare/8/x.pdf', 'abc');

        $this->actingAs($this->admin($agency))->delete(route('welfare.destroy', $rec))
            ->assertRedirect(route('welfare.index'));

        Storage::disk('public')->assertMissing('welfare/8/x.pdf');
        $this->assertNull(ApplicantWelfareRecord::find($rec->id));
    }
}
