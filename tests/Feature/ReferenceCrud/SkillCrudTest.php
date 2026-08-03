<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SkillCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_skills(): void
    {
        $this->get(route('skills.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_skills(): void
    {
        $agency = Agency::factory()->create();
        $staff = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->get(route('skills.index'))->assertForbidden(403);
    }

    #[Test]
    public function index_lists_skills(): void
    {
        Skill::factory()->create(['name' => 'Cooking']);
        Skill::factory()->create(['name' => 'Driving']);

        $this->actingAs($this->admin)
            ->get(route('skills.index'))
            ->assertOk()
            ->assertSee('Cooking')
            ->assertSee('Driving');
    }

    #[Test]
    public function store_creates_skill(): void
    {
        $this->actingAs($this->admin)
            ->post(route('skills.store'), ['name' => 'Caregiving'])
            ->assertRedirect(route('skills.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('skills', ['name' => 'Caregiving']);
    }

    #[Test]
    public function store_requires_unique_name(): void
    {
        Skill::factory()->create(['name' => 'Cooking']);

        $this->actingAs($this->admin)
            ->post(route('skills.store'), ['name' => 'Cooking'])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function update_changes_skill(): void
    {
        $skill = Skill::factory()->create(['name' => 'Old Skill']);

        $this->actingAs($this->admin)
            ->put(route('skills.update', $skill), ['name' => 'New Skill'])
            ->assertRedirect(route('skills.index'));

        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'name' => 'New Skill']);
    }

    #[Test]
    public function destroy_deletes_skill(): void
    {
        $skill = Skill::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('skills.destroy', $skill))
            ->assertRedirect(route('skills.index'));

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }
}
