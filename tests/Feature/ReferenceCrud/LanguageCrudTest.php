<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LanguageCrudTest extends TestCase
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
    public function unauthenticated_user_cannot_access_languages(): void
    {
        $this->get(route('languages.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function non_admin_cannot_access_languages(): void
    {
        $agency = Agency::factory()->create();
        $staff = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->get(route('languages.index'))->assertForbidden(403);
    }

    #[Test]
    public function index_lists_languages(): void
    {
        Language::factory()->create(['name' => 'Tagalog']);
        Language::factory()->create(['name' => 'Arabic']);

        $this->actingAs($this->admin)
            ->get(route('languages.index'))
            ->assertOk()
            ->assertSee('Tagalog')
            ->assertSee('Arabic');
    }

    #[Test]
    public function store_creates_language(): void
    {
        $this->actingAs($this->admin)
            ->post(route('languages.store'), ['name' => 'Ilocano'])
            ->assertRedirect(route('languages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('languages', ['name' => 'Ilocano']);
    }

    #[Test]
    public function store_requires_unique_name(): void
    {
        Language::factory()->create(['name' => 'Tagalog']);

        $this->actingAs($this->admin)
            ->post(route('languages.store'), ['name' => 'Tagalog'])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function update_changes_language(): void
    {
        $lang = Language::factory()->create(['name' => 'Bisaya']);

        $this->actingAs($this->admin)
            ->put(route('languages.update', $lang), ['name' => 'Cebuano'])
            ->assertRedirect(route('languages.index'));

        $this->assertDatabaseHas('languages', ['id' => $lang->id, 'name' => 'Cebuano']);
    }

    #[Test]
    public function destroy_deletes_language(): void
    {
        $lang = Language::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('languages.destroy', $lang))
            ->assertRedirect(route('languages.index'));

        $this->assertDatabaseMissing('languages', ['id' => $lang->id]);
    }
}
