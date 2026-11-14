<?php

namespace Tests\Feature\Reports;

use App\Models\Agency;
use App\Models\ApplicantWelfareRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af81c30 part 3: "Remove the Agent (Marketing) Report on Report Module,
 * Instead Add Welfare Report."
 *
 * The Report module carries one agent card today. On FINAS and CSDI that card
 * is replaced by the Welfare Report; everywhere else it stays.
 */
class WelfareReportTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => strtoupper($subdomain)]);
    }

    private function user(Agency $agency, string $type = 'admin'): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => $type,
            'status' => 'active',
        ]);
    }

    private function record(Agency $agency, array $attrs = []): ApplicantWelfareRecord
    {
        return ApplicantWelfareRecord::create(array_merge([
            'agency_id' => $agency->id,
            'ofw_full_name' => 'Lourdes Bautista',
            'passport_number' => 'P4455667C',
            'dmw_ereg_id' => 'DMW-001',
            'country_of_destination' => 'Saudi Arabia',
            'job_site_city' => 'Riyadh',
            'position_designation' => 'Domestic Helper',
            'foreign_recruitment_agency' => 'Golden Bridge',
            'remarks' => 'Contract not released',
            'encoder' => 'QA Encoder',
            'date_of_contact' => '2026-11-01',
        ], $attrs));
    }

    public function test_finas_and_csdi_get_the_welfare_card_instead_of_agent(): void
    {
        foreach (['finas', 'csdi'] as $slug) {
            $agency = $this->agency($slug);
            $user = $this->user($agency);

            $html = $this->actingAs($user)->get(route('reports.index'))->assertOk()->getContent();

            $this->assertStringContainsString('Welfare Report', $html, "$slug should show the Welfare Report card");
            $this->assertStringNotContainsString('Agent Reports', $html, "$slug should no longer show the Agent Reports card");
        }
    }

    public function test_other_tenants_keep_the_agent_card(): void
    {
        $agency = $this->agency('gulf');
        $user = $this->user($agency);

        $html = $this->actingAs($user)->get(route('reports.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Agent Reports', $html);
        $this->assertStringNotContainsString('Welfare Report', $html);
    }

    public function test_the_welfare_report_lists_records(): void
    {
        $agency = $this->agency('finas');
        $user = $this->user($agency);
        $this->record($agency);

        $this->actingAs($user)->get(route('reports.welfare'))
            ->assertOk()
            ->assertSee('Lourdes Bautista')
            ->assertSee('P4455667C')
            ->assertSee('Riyadh');
    }

    public function test_the_welfare_report_is_scoped_to_the_tenant(): void
    {
        $finas = $this->agency('finas');
        $csdi = $this->agency('csdi');

        $this->record($finas, ['ofw_full_name' => 'Finas Person']);
        $this->record($csdi, ['ofw_full_name' => 'Csdi Person']);

        $this->actingAs($this->user($finas))->get(route('reports.welfare'))
            ->assertOk()
            ->assertSee('Finas Person')
            ->assertDontSee('Csdi Person');
    }

    public function test_the_welfare_report_is_refused_on_other_tenants(): void
    {
        $agency = $this->agency('gulf');

        $this->actingAs($this->user($agency))
            ->get(route('reports.welfare'))
            ->assertForbidden();
    }

    public function test_filters_narrow_the_report(): void
    {
        $agency = $this->agency('finas');
        $user = $this->user($agency);

        $this->record($agency, ['ofw_full_name' => 'Keep Me', 'country_of_destination' => 'Qatar']);
        $this->record($agency, ['ofw_full_name' => 'Drop Me', 'country_of_destination' => 'Kuwait']);

        $this->actingAs($user)->get(route('reports.welfare', ['country' => 'Qatar']))
            ->assertOk()
            ->assertSee('Keep Me')
            ->assertDontSee('Drop Me');
    }

    public function test_the_csv_exports_the_filtered_rows(): void
    {
        $agency = $this->agency('finas');
        $user = $this->user($agency);

        $this->record($agency, ['ofw_full_name' => 'Export Me']);

        $response = $this->actingAs($user)->get(route('reports.welfare.export'));
        $response->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Export Me', $csv);
        $this->assertStringContainsString('Passport No.', $csv);
    }

    public function test_the_csv_is_refused_on_other_tenants(): void
    {
        $agency = $this->agency('gulf');

        $this->actingAs($this->user($agency))
            ->get(route('reports.welfare.export'))
            ->assertForbidden();
    }
}
