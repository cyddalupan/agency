<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Agent;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgentBranchAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function create_form_shows_branches_dropdown(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->get(route('agents.create'))
            ->assertOk()
            ->assertSee('Branch')
            ->assertSee($branch->name);
    }

    #[Test]
    public function store_persists_branch_assignment(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);

        $this->actingAs($this->admin)
            ->post(route('agents.store'), [
                'name'            => 'Agent With Branch',
                'email'           => 'branched@test.com',
                'contact'         => '0917-000-0000',
                'password'        => 'secret123',
                'password_confirmation' => 'secret123',
                'branch_id'       => $branch->id,
                'commission_rate' => 10,
            ])
            ->assertRedirect(route('agents.index'));

        $this->assertDatabaseHas('agents', [
            'email'     => 'branched@test.com',
            'branch_id' => $branch->id,
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function store_rejects_branch_from_another_agency(): void
    {
        $otherAgency = Agency::factory()->create();
        $otherBranch = Branch::factory()->create(['agency_id' => $otherAgency->id]);

        $this->actingAs($this->admin)
            ->post(route('agents.store'), [
                'name'      => 'Bad Branch Agent',
                'email'     => 'badbranch@test.com',
                'password'  => 'secret123',
                'branch_id' => $otherBranch->id,
            ])->assertSessionHasErrors('branch_id');
    }

    #[Test]
    public function edit_form_shows_assigned_branch(): void
    {
        $branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $agent = Agent::factory()->create([
            'agency_id' => $this->agency->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('agents.edit', $agent))
            ->assertOk()
            ->assertSee('Branch');
    }
}
