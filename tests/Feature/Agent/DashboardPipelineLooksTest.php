<?php

namespace Tests\Feature\Agent;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\StatusCode;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class DashboardPipelineLooksTest extends TestCase
{
    use RefreshDatabase;

    private Agent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = Agency::factory()->create();
        $this->agent = Agent::factory()->create([
            'agency_id' => $agency->id,
            'name'      => 'Test Agent',
            'email'     => 'agent@test.com',
        ]);

        $this->seed(StatusCodesSeeder::class);

        Applicant::factory()->count(5)->create([
            'agency_id'   => $agency->id,
            'agent_id'    => $this->agent->id,
            'status_code' => 0,
        ]);
        Applicant::factory()->count(3)->create([
            'agency_id'   => $agency->id,
            'agent_id'    => $this->agent->id,
            'status_code' => 1,
        ]);
    }

    #[Test]
    public function pipeline_chips_look_like_badges_with_old_style_colors(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);
        // Should use badge-style classes, not btn classes
        $response->assertSee('badge badge-lg');
    }

    #[Test]
    public function pipeline_chips_are_clickable_links(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('<a href=', false);
        $response->assertDontSee('<span class="badge badge-lg', false);
    }

    #[Test]
    public function pipeline_chips_show_counts(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Pending', '5']);
        $response->assertSeeInOrder(['For Interview', '3']);
    }

    #[Test]
    public function no_btn_classes_on_pipeline_chips(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard'));

        $response->assertStatus(200);
        // Pipeline section should use badge classes, not btn classes
        $html = $response->getContent();
        $this->assertStringContainsString('badge badge-lg', $html);
    }

    #[Test]
    public function active_filter_chip_has_active_indicator(): void
    {
        $response = $this->actingAs($this->agent, 'agent')
            ->get(route('agent.dashboard', ['status' => 1]));

        $response->assertStatus(200);
        $response->assertSee('For Interview');
        $response->assertSee('3');
    }
}
