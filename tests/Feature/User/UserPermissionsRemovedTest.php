<?php

namespace Tests\Feature\User;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The granular "Permissions" UI was removed (Mjolnir card "Users Module",
 * 2026-10-06). It confused staff and was never enforced anywhere — access is
 * driven solely by the role presets (user_type / ModuleAccess). These tests
 * guard the removal so it does not creep back in.
 */
class UserPermissionsRemovedTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    private function admin(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ], $attrs));
    }

    #[Test]
    public function permissions_routes_no_longer_exist(): void
    {
        $this->assertFalse(Route::has('users.permissions'));
        $this->assertFalse(Route::has('users.permissions.update'));
    }

    #[Test]
    public function users_index_has_no_permissions_link(): void
    {
        $response = $this->actingAs($this->admin())->get(route('users.index'));

        $response->assertOk();
        $response->assertDontSee('/permissions', false);
    }

    #[Test]
    public function users_index_still_shows_view_and_edit(): void
    {
        $response = $this->actingAs($this->admin())->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('>View<', false);
        $response->assertSee('>Edit<', false);
    }

    #[Test]
    public function permissions_view_file_is_gone(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/users/permissions.blade.php'));
    }

    #[Test]
    public function user_permissions_model_is_gone(): void
    {
        $this->assertFileDoesNotExist(app_path('Models/UserPermission.php'));
    }

    #[Test]
    public function user_model_has_no_permissions_relation(): void
    {
        $this->assertFalse(method_exists(new User(), 'permissions'));
    }

    #[Test]
    public function roles_can_still_be_changed_from_the_edit_form(): void
    {
        $target = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($this->admin())
            ->put(route('users.update', $target), [
                'name'      => $target->name,
                'email'     => $target->email,
                'user_type' => 'coordinator',
                'status'    => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id'        => $target->id,
            'user_type' => 'coordinator',
        ]);
    }
}
