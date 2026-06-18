<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgencyCrudTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── INDEX / LIST ─────────────────────────────────────────────────

    #[Test]
    public function admin_can_list_agencies(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        Agency::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->get(route('agencies.index'));

        $response->assertOk();
        $response->assertViewHas('agencies');
    }

    #[Test]
    public function super_admin_can_list_all_agencies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.index'));

        $response->assertOk();
        $response->assertViewHas('agencies');
    }

    #[Test]
    public function staff_cannot_list_agencies(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('agencies.index'));

        $response->assertForbidden();
    }

    // ─── CREATE ───────────────────────────────────────────────────────

    #[Test]
    public function admin_can_see_create_form(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('agencies.create'));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_create_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $agencyData = [
            'name'      => 'Test Agency',
            'subdomain' => 'test-agency',
        ];

        $response = $this->actingAs($admin)
            ->post(route('agencies.store'), $agencyData);

        $response->assertRedirect(route('agencies.index'));
        $this->assertDatabaseHas('agencies', [
            'name'      => 'Test Agency',
            'subdomain' => 'test-agency',
            'status'    => 'active',
        ]);
    }

    #[Test]
    public function agency_creation_validates_required_fields(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('agencies.store'), []);

        $response->assertSessionHasErrors(['name', 'subdomain']);
    }

    #[Test]
    public function agency_creation_validates_unique_subdomain(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        Agency::factory()->create(['subdomain' => 'taken']);

        $response = $this->actingAs($admin)
            ->post(route('agencies.store'), [
                'name'      => 'Duplicate Subdomain',
                'subdomain' => 'taken',
            ]);

        $response->assertSessionHasErrors(['subdomain']);
    }

    // ─── EDIT / UPDATE ───────────────────────────────────────────────

    #[Test]
    public function admin_can_see_edit_form(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = Agency::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('agencies.edit', $target));

        $response->assertOk();
        $response->assertViewHas('agency');
    }

    #[Test]
    public function admin_can_update_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = Agency::factory()->create();

        $response = $this->actingAs($admin)
            ->put(route('agencies.update', $target), [
                'name'      => 'Updated Agency',
                'subdomain' => 'updated-subdomain',
            ]);

        $response->assertRedirect(route('agencies.index'));
        $this->assertDatabaseHas('agencies', [
            'id'        => $target->id,
            'name'      => 'Updated Agency',
            'subdomain' => 'updated-subdomain',
        ]);
    }

    #[Test]
    public function update_validates_unique_subdomain_excluding_self(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = Agency::factory()->create(['subdomain' => 'my-agency']);

        // Same subdomain should be OK for the same agency
        $response = $this->actingAs($admin)
            ->put(route('agencies.update', $target), [
                'name'      => 'My Agency',
                'subdomain' => 'my-agency',
            ]);

        $response->assertRedirect(route('agencies.index'));

        // Different agency's subdomain should fail
        Agency::factory()->create(['subdomain' => 'other-agency']);
        $response = $this->actingAs($admin)
            ->put(route('agencies.update', $target), [
                'name'      => 'My Agency',
                'subdomain' => 'other-agency',
            ]);

        $response->assertSessionHasErrors(['subdomain']);
    }

    // ─── ACTIVATE / DEACTIVATE ───────────────────────────────────────

    #[Test]
    public function admin_can_deactivate_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = Agency::factory()->create();

        $response = $this->actingAs($admin)
            ->put(route('agencies.deactivate', $target));

        $response->assertRedirect(route('agencies.index'));
        $this->assertDatabaseHas('agencies', [
            'id'     => $target->id,
            'status' => 'inactive',
        ]);
    }

    #[Test]
    public function admin_can_activate_agency(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $target = Agency::factory()->inactive()->create();

        $response = $this->actingAs($admin)
            ->put(route('agencies.activate', $target));

        $response->assertRedirect(route('agencies.index'));
        $this->assertDatabaseHas('agencies', [
            'id'     => $target->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function agency_activate_deactivate_requires_admin(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $target = Agency::factory()->create();

        $response = $this->actingAs($staff)
            ->put(route('agencies.deactivate', $target));

        $response->assertForbidden();

        $response = $this->actingAs($staff)
            ->put(route('agencies.activate', $target));

        $response->assertForbidden();
    }

    // ─── STATUS FILTER ON INDEX ──────────────────────────────────────

    #[Test]
    public function admin_can_filter_agencies_by_status(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        Agency::factory()->count(3)->create();
        Agency::factory()->count(2)->inactive()->create();

        $response = $this->actingAs($admin)
            ->get(route('agencies.index', ['status' => 'active']));

        $response->assertOk();
        $agencies = $response->viewData('agencies');
        $this->assertCount(4, $agencies); // 3 new + the setUp agency (active)
    }

    #[Test]
    public function admin_can_filter_agencies_by_inactive_status(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        Agency::factory()->count(3)->create();
        Agency::factory()->count(2)->inactive()->create();

        $response = $this->actingAs($admin)
            ->get(route('agencies.index', ['status' => 'inactive']));

        $response->assertOk();
        $agencies = $response->viewData('agencies');
        $this->assertCount(2, $agencies);
    }
}
