<?php

namespace Tests\Feature\Agent;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgentModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function agent_has_required_fields(): void
    {
        $agency = Agency::factory()->create();
        $agent = Agent::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@agent.com',
        ]);

        $this->assertNotNull($agent);
        $this->assertEquals('Juan Dela Cruz', $agent->name);
        $this->assertEquals('juan@agent.com', $agent->email);
        $this->assertEquals($agency->id, $agent->agency_id);
        $this->assertEquals('active', $agent->status);
    }

    #[Test]
    public function agent_belongs_to_agency(): void
    {
        $agency = Agency::factory()->create();
        $agent = Agent::factory()->create(['agency_id' => $agency->id]);

        $this->assertTrue($agent->agency->is($agency));
    }

    #[Test]
    public function agent_email_is_unique(): void
    {
        $agency = Agency::factory()->create();

        Agent::factory()->create([
            'agency_id' => $agency->id,
            'email' => 'same@email.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Agent::factory()->create([
            'agency_id' => $agency->id,
            'email' => 'same@email.com',
        ]);
    }
}
