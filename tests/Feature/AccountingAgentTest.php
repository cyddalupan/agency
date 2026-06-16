<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\Employer;
use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountingAgentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;
    private Employer $employer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        $this->user = User::factory()->create(['agency_id' => $this->agency->id]);
        $this->employer = Employer::factory()->create(['agency_id' => $this->agency->id]);
    }

    // ─── Marketing Agency ───────────────────────────────────────────

    #[Test]
    public function guest_cannot_access_marketing_agency_accounting(): void
    {
        $agency = MarketingAgency::factory()->create(['agency_id' => $this->agency->id]);

        $this->get(route('accounting.marketing-agency', $agency))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_marketing_agency_accounting(): void
    {
        $mkAgency = MarketingAgency::factory()->create(['agency_id' => $this->agency->id]);
        Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'commissionable_type' => 'marketing_agency',
            'commissionable_id' => $mkAgency->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.marketing-agency', $mkAgency));

        $response->assertOk();
        $response->assertViewIs('accounting.marketing-agency');
        $response->assertViewHas('commissions');
        $response->assertViewHas('totalCommissions');
        $response->assertViewHas('totalPaid');
        $response->assertViewHas('balance');
        $this->assertEquals(50000, $response->viewData('totalCommissions'));
    }

    #[Test]
    public function marketing_agency_accounting_tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherMkAgency = MarketingAgency::factory()->create(['agency_id' => $otherAgency->id]);
        Commission::factory()->create([
            'agency_id' => $otherAgency->id,
            'employer_id' => Employer::factory()->create(['agency_id' => $otherAgency->id])->id,
            'commissionable_type' => 'marketing_agency',
            'commissionable_id' => $otherMkAgency->id,
            'amount' => 99999,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.marketing-agency', $otherMkAgency));

        $response->assertNotFound();
    }

    // ─── Marketing Agent ────────────────────────────────────────────

    #[Test]
    public function guest_cannot_access_marketing_agent_accounting(): void
    {
        $agent = MarketingAgent::factory()->create(['agency_id' => $this->agency->id]);

        $this->get(route('accounting.marketing-agent', $agent))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_marketing_agent_accounting(): void
    {
        $agent = MarketingAgent::factory()->create(['agency_id' => $this->agency->id]);
        Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'commissionable_type' => 'marketing_agent',
            'commissionable_id' => $agent->id,
            'amount' => 30000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.marketing-agent', $agent));

        $response->assertOk();
        $response->assertViewIs('accounting.marketing-agent');
        $response->assertViewHas('commissions');
        $response->assertViewHas('totalCommissions');
        $response->assertViewHas('totalPaid');
        $response->assertViewHas('balance');
        $this->assertEquals(30000, $response->viewData('totalCommissions'));
    }

    #[Test]
    public function marketing_agent_accounting_tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = MarketingAgent::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.marketing-agent', $otherAgent));

        $response->assertNotFound();
    }

    // ─── Recruitment Agent ──────────────────────────────────────────

    #[Test]
    public function guest_cannot_access_recruitment_agent_accounting(): void
    {
        $response = $this->get(route('accounting.recruitment-agent', 1));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function shows_recruitment_agent_accounting(): void
    {
        // Recruitment agents are polymorphic — use the user model with commissionable_type
        $recruitmentAgent = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'recruitment_agent',
        ]);
        Commission::factory()->create([
            'agency_id' => $this->agency->id,
            'employer_id' => $this->employer->id,
            'commissionable_type' => 'recruitment_agent',
            'commissionable_id' => $recruitmentAgent->id,
            'amount' => 40000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.recruitment-agent', $recruitmentAgent));

        $response->assertOk();
        $response->assertViewIs('accounting.recruitment-agent');
        $response->assertViewHas('totalCommissions');
        $this->assertEquals(40000, $response->viewData('totalCommissions'));
    }

    #[Test]
    public function recruitment_agent_accounting_tenant_scoped(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherAgent = User::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('accounting.recruitment-agent', $otherAgent));

        $response->assertNotFound();
    }
}
