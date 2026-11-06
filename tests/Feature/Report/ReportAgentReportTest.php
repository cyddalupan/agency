<?php

namespace Tests\Feature\Report;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Reports -> Agents.
 *
 * Agency users must ONLY see agents belonging to their own agency. Super admins
 * may see all agencies. Regression: the agents report previously queried every
 * agent with no agency scope, leaking agents from other agencies (e.g. a Gulf
 * user seeing Finas agents).
 */
class ReportAgentReportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        app()->instance('tenant_agency', $this->agency);

        // Agent Report is a privileged report: the 'staff' tier is restricted to
        // Applicant Reports by EnsureModuleAccess (returns 403), so these tests
        // use an agency admin.
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function guest_cannot_access_agents_report(): void
    {
        $this->get(route('reports.agents'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function agency_user_only_sees_agents_from_their_own_agency(): void
    {
        $otherAgency = Agency::factory()->create();

        $mine   = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Gulf Agent One']);
        $theirs = Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Finas Agent Leak']);

        $response = $this->actingAs($this->user)
            ->get(route('reports.agents'))
            ->assertOk();

        $html = $response->getContent();
        // Own agency agent present.
        $this->assertStringContainsString($mine->name, $html);
        // Other agency's agent must NOT appear.
        $this->assertStringNotContainsString($theirs->name, $html);
    }

    #[Test]
    public function agency_user_agents_export_excludes_other_agencies(): void
    {
        $otherAgency = Agency::factory()->create();

        $mine   = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Export Mine']);
        $theirs = Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Export Leak']);

        $response = $this->actingAs($this->user)
            ->get(route('reports.agents.export'))
            ->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString($mine->name, $csv);
        $this->assertStringNotContainsString($theirs->name, $csv);
    }

    #[Test]
    public function agents_export_headers_include_branch_after_name(): void
    {
        $admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('reports.agents.export'))
            ->assertOk();

        $csv = $response->streamedContent();
        $lines = preg_split('/\r\n|\n/', trim($csv));
        // Strip the UTF-8 BOM from the first line before parsing
        $lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);

        $this->assertSame(
            ['Name', 'Branch', 'Email', 'Contact', 'Commission Rate', 'Agency', 'Status', 'Created At'],
            str_getcsv($lines[0])
        );
    }

    #[Test]
    public function agents_export_writes_the_agents_branch_name(): void
    {
        $branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Davao Branch',
        ]);
        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Branchy Agent',
            'branch_id' => $branch->id,
        ]);

        $admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);

        $csv = $this->actingAs($admin)
            ->get(route('reports.agents.export'))
            ->assertOk()
            ->streamedContent();

        $row = collect(preg_split('/\r\n|\n/', trim($csv)))
            ->first(fn ($line) => str_contains($line, 'Branchy Agent'));

        $this->assertNotNull($row);
        $this->assertSame('Davao Branch', str_getcsv($row)[1]);
    }

    #[Test]
    public function agents_report_table_places_branch_after_name(): void
    {
        $branch = Branch::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Cebu Branch',
        ]);
        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'Branchy Report Agent',
            'branch_id' => $branch->id,
        ]);

        $html = $this->actingAs($this->user)
            ->get(route('reports.agents'))
            ->assertOk()
            ->getContent();

        // Headers: Name then Branch, immediately adjacent.
        $this->assertMatchesRegularExpression('/<th>\s*Name\s*<\/th>\s*<th>\s*Branch\s*<\/th>/', $html);
        // The agent's branch name renders in the row.
        $this->assertStringContainsString('Cebu Branch', $html);
    }

    #[Test]
    public function agents_export_leaves_branch_blank_when_the_agent_has_none(): void
    {
        Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'name' => 'No Branch Agent',
            'branch_id' => null,
        ]);

        $admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);

        $csv = $this->actingAs($admin)
            ->get(route('reports.agents.export'))
            ->assertOk()
            ->streamedContent();

        $row = collect(preg_split('/\r\n|\n/', trim($csv)))
            ->first(fn ($line) => str_contains($line, 'No Branch Agent'));

        $this->assertNotNull($row);
        $this->assertSame('', str_getcsv($row)[1]);
    }

    #[Test]
    public function super_admin_sees_agents_from_all_agencies(): void
    {
        $super = User::factory()->create(['user_type' => 'super_admin', 'agency_id' => null]);
        $otherAgency = Agency::factory()->create();

        $mine   = Agent::factory()->create(['agency_id' => $this->agency->id, 'name' => 'Super Mine']);
        $theirs = Agent::factory()->create(['agency_id' => $otherAgency->id, 'name' => 'Super Other']);

        $html = $this->actingAs($super)
            ->get(route('reports.agents'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($mine->name, $html);
        $this->assertStringContainsString($theirs->name, $html);
    }

    #[Test]
    public function agents_report_shows_all_agents_on_one_page(): void
    {
        Agent::factory()->count(30)->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($this->user)
            ->get(route('reports.agents'))
            ->assertOk();

        // CYD 2026-11-06: no pagination — every agency agent renders.
        $this->assertCount(30, $response->viewData('agents'));
        $response->assertDontSee('page=2');
    }

    #[Test]
    public function reports_index_links_to_agent_report(): void
    {
        $this->actingAs($this->user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee(route('reports.agents'));
    }
}
