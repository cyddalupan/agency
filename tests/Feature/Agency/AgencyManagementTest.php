<?php

namespace Tests\Feature\Agency;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AgencyManagementTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    // ─── FEATURE 1: LOGO (ICON) UPLOAD VIA THE EDIT PAGE ───────────────

    #[Test]
    public function agency_index_displays_logo_when_set(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('logo.png')->store('logos', 'public');
        $this->agency->update(['logo' => $path]);

        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('agencies.index'));

        $response->assertOk();
        // The logo should be rendered as an <img> on the list row
        $response->assertSee('img', false);
    }

    #[Test]
    public function agency_index_does_not_have_inline_logo_upload(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('agencies.index'));

        $response->assertOk();
        // Icon upload lives on the edit page, not the list
        $response->assertDontSee('Set Icon');
    }

    #[Test]
    public function agency_list_rows_are_clickable_to_detail_page(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('agencies.index'));

        $response->assertOk();
        // Each row links/points to the agency detail page
        $response->assertSee(route('agencies.show', $this->agency));
    }

    #[Test]
    public function admin_can_upload_agency_logo_through_edit_page(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $logo = UploadedFile::fake()->image('new-icon.png', 200, 200);

        $response = $this->actingAs($admin)
            ->put(route('agencies.update', $this->agency), [
                'name'      => $this->agency->name,
                'subdomain' => $this->agency->subdomain,
                'logo'      => $logo,
            ]);

        $response->assertRedirect();
        $this->assertNotNull($this->agency->fresh()->logo);
        $this->assertTrue(Storage::disk('public')->exists($this->agency->fresh()->logo));
    }

    #[Test]
    public function logo_upload_requires_an_image_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('agencies.update', $this->agency), [
                'name'      => $this->agency->name,
                'subdomain' => $this->agency->subdomain,
                'logo'      => 'not-a-file',
            ]);

        $response->assertSessionHasErrors('logo');
    }

    #[Test]
    public function staff_cannot_upload_agency_logo(): void
    {
        Storage::fake('public');
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);
        $logo = UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($staff)
            ->put(route('agencies.update', $this->agency), [
                'name'      => $this->agency->name,
                'subdomain' => $this->agency->subdomain,
                'logo'      => $logo,
            ]);

        $response->assertForbidden();
    }

    // ─── FEATURE 2: AGENCY DETAIL SHOWS AGENCY INFO + ITS USERS ─────────

    #[Test]
    public function agency_detail_shows_own_users_only(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $ownUser = User::factory()->create(['agency_id' => $this->agency->id]);
        $otherAgency = Agency::factory()->create();
        $otherUser = User::factory()->create(['agency_id' => $otherAgency->id]);

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.show', $this->agency));

        $response->assertOk();
        $response->assertSee($ownUser->name);
        $response->assertDontSee($otherUser->name);
    }

    #[Test]
    public function agency_detail_shows_agency_information(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.show', $this->agency));

        $response->assertOk();
        $response->assertSee($this->agency->name);
        $response->assertSee($this->agency->subdomain, false);
    }

    #[Test]
    public function agency_detail_loads_agency_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.show', $this->agency));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) use ($member) {
            return $users->contains('id', $member->id);
        });
    }

    #[Test]
    public function agency_user_create_creates_user_in_that_agency(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('agencies.users.store', $this->agency), [
                'name'      => 'New Agency Member',
                'email'     => 'member@example.com',
                'password'  => 'password',
                'user_type' => 'staff',
                'status'    => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'agency_id' => $this->agency->id,
            'name'      => 'New Agency Member',
            'email'     => 'member@example.com',
            'user_type' => 'staff',
        ]);
    }

    #[Test]
    public function agency_user_update_updates_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('agencies.users.update', [$this->agency, $member]), [
                'name'      => 'Renamed Member',
                'email'     => $member->email,
                'user_type' => 'billing',
                'status'    => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'        => $member->id,
            'name'      => 'Renamed Member',
            'user_type' => 'billing',
        ]);
    }

    #[Test]
    public function agency_user_delete_removes_user(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($superAdmin)
            ->delete(route('agencies.users.destroy', [$this->agency, $member]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    #[Test]
    public function staff_cannot_access_agency_detail(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('agencies.show', $this->agency));

        $response->assertForbidden();
    }

    // ─── AGENCY DETAIL: USER CRUD FORM PAGES RENDERABLE ────────────────

    #[Test]
    public function agency_detail_has_add_user_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.users.create', $this->agency));

        $response->assertOk();
    }

    #[Test]
    public function agency_user_edit_form_is_renderable(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $member = User::factory()->create(['agency_id' => $this->agency->id]);

        $response = $this->actingAs($superAdmin)
            ->get(route('agencies.users.edit', [$this->agency, $member]));

        $response->assertOk();
    }
}
