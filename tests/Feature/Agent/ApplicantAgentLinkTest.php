<?php

namespace Tests\Feature\Agent;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantAgentLinkTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    // ─── FORM DROPDOWN BEHAVIOR ─────────────────────────────────────

    #[Test]
    public function create_form_shows_agent_dropdown_when_source_is_referral(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('agent_id');
        $response->assertSee($this->agent->name);
    }

    #[Test]
    public function store_saves_agent_id(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('applicants.store'), [
                'first_name' => 'Referral',
                'last_name'  => 'Test',
                'source'     => 'Referral',
                'agent_id'   => $this->agent->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Referral',
            'agent_id'   => $this->agent->id,
        ]);
    }

    #[Test]
    public function store_agent_id_can_be_null_when_not_referral(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('applicants.store'), [
                'first_name' => 'Walkin',
                'last_name'  => 'Test',
                'source'     => 'Walk-in',
                'agent_id'   => null,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name' => 'Walkin',
            'agent_id'   => null,
        ]);
    }

    #[Test]
    public function update_changes_agent_id(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('applicants.update', $applicant), [
                'first_name' => $applicant->first_name,
                'last_name'  => $applicant->last_name,
                'source'     => 'Referral',
                'agent_id'   => $this->agent->id,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'id'       => $applicant->id,
            'agent_id' => $this->agent->id,
        ]);
    }

    // ─── SHOW PAGE ──────────────────────────────────────────────────

    #[Test]
    public function show_page_displays_agent_name(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $this->agent->id,
            'has_passport' => 'with',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee($this->agent->name);
    }

    // ─── EXPORT ─────────────────────────────────────────────────────

    #[Test]
    public function export_contains_agent_column(): void
    {
        app()->instance('tenant_agency', $this->agency);

        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'agent_id'  => $this->agent->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('applicants.export'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Referred By', $content);
        $this->assertStringContainsString($this->agent->name, $content);
    }
}
