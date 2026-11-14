<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Card 6af82438, part 1: "Create a PROFESSIONAL tab on the Applicant module"
 * (CSDI and FINAS only).
 */
class ProfessionalApplicantTabTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => strtoupper($subdomain)]);
    }

    private function user(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status' => 'active',
        ]);
    }

    private function position(string $category, string $name): Position
    {
        return Position::create(['name' => $name, 'category' => $category]);
    }

    public function test_finas_and_csdi_show_the_professional_tab(): void
    {
        foreach (['finas', 'csdi'] as $slug) {
            $agency = $this->agency($slug);

            $html = $this->actingAs($this->user($agency))
                ->get(route('applicants.index'))
                ->assertOk()
                ->getContent();

            $this->assertStringContainsString('PROFESSIONAL', $html, "$slug should show the PROFESSIONAL tab");
        }
    }

    public function test_gulf_does_not_show_the_professional_tab(): void
    {
        $agency = $this->agency('gulf');

        $html = $this->actingAs($this->user($agency))
            ->get(route('applicants.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('PROFESSIONAL', $html);
    }

    public function test_the_professional_tab_lists_only_professional_applicants(): void
    {
        $agency = $this->agency('finas');
        $user = $this->user($agency);

        $proPos = $this->position(Position::CATEGORY_PROFESSIONAL, 'Registered Nurse');
        $skillPos = $this->position(Position::CATEGORY_SKILLED, 'Welder');

        Applicant::create([
            'agency_id' => $agency->id, 'first_name' => 'PRO', 'last_name' => 'NURSE',
            'position_id' => $proPos->id, 'applicant_type' => Applicant::TYPE_PROFESSIONAL,
        ]);
        Applicant::create([
            'agency_id' => $agency->id, 'first_name' => 'SKILL', 'last_name' => 'WELDER',
            'position_id' => $skillPos->id, 'applicant_type' => Applicant::TYPE_SKILLED,
        ]);

        $html = $this->actingAs($user)
            ->get(route('applicants.index', ['type' => 'professional']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('NURSE', $html);
        $this->assertStringNotContainsString('WELDER', $html);
    }

    public function test_professional_applicants_no_longer_leak_into_the_skilled_tab(): void
    {
        $agency = $this->agency('finas');
        $user = $this->user($agency);

        $proPos = $this->position(Position::CATEGORY_PROFESSIONAL, 'Pharmacist');

        // A legacy-style row: type NULL, but the position is Professional.
        Applicant::create([
            'agency_id' => $agency->id, 'first_name' => 'LEGACY', 'last_name' => 'PHARMA',
            'position_id' => $proPos->id, 'applicant_type' => null,
        ]);

        $skilled = $this->actingAs($user)
            ->get(route('applicants.index', ['type' => 'skilled']))
            ->assertOk()->getContent();
        $this->assertStringNotContainsString('PHARMA', $skilled, 'Professional position leaked into Skilled');

        $professional = $this->actingAs($user)
            ->get(route('applicants.index', ['type' => 'professional']))
            ->assertOk()->getContent();
        $this->assertStringContainsString('PHARMA', $professional);
    }

    public function test_the_professional_tab_is_scoped_to_the_tenant(): void
    {
        $finas = $this->agency('finas');
        $csdi = $this->agency('csdi');

        Applicant::create([
            'agency_id' => $finas->id, 'first_name' => 'FINAS', 'last_name' => 'PROF',
            'applicant_type' => Applicant::TYPE_PROFESSIONAL,
        ]);
        Applicant::create([
            'agency_id' => $csdi->id, 'first_name' => 'OTHERAGENCY', 'last_name' => 'PERSON',
            'applicant_type' => Applicant::TYPE_PROFESSIONAL,
        ]);

        // Bind the tenant the way the middleware does in a real request;
        // without it TenantScope does not filter and the assertion is noise.
        app()->instance('tenant_agency', $finas);

        $html = $this->actingAs($this->user($finas))
            ->get(route('applicants.index', ['type' => 'professional']))
            ->assertOk()->getContent();

        $this->assertStringContainsString('FINAS', $html);
        $this->assertStringNotContainsString('OTHERAGENCY', $html);
    }
}
