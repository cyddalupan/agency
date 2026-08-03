<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
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

        Storage::fake('public');
    }

    #[Test]
    public function agency_admin_can_open_company_branding_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('agencies.branding', $this->agency))
            ->assertOk()
            ->assertSee('Company Name')
            ->assertSee('Company Address');
    }

    #[Test]
    public function branding_update_saves_company_name_and_address(): void
    {
        $this->actingAs($this->admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'name'    => 'Landas Recruitment Co.',
                'address' => '123 Rizal Avenue, Manila',
            ])
            ->assertRedirect(route('agencies.branding', $this->agency))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('agencies', [
            'id'      => $this->agency->id,
            'name'    => 'Landas Recruitment Co.',
            'address' => '123 Rizal Avenue, Manila',
        ]);
    }

    #[Test]
    public function branding_update_accepts_logo_upload(): void
    {
        $file = UploadedFile::fake()->image('logo.png', 100, 100);

        $this->actingAs($this->admin)
            ->put(route('agencies.branding.update', $this->agency), [
                'name' => 'Logo Company',
                'logo' => $file,
            ])
            ->assertRedirect(route('agencies.branding', $this->agency));

        $agency = $this->agency->fresh();
        $this->assertEquals('Logo Company', $agency->name);
        $this->assertNotNull($agency->logo);
        Storage::disk('public')->assertExists($agency->logo);
    }

    #[Test]
    public function non_admin_agency_user_cannot_access_branding(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)
            ->get(route('agencies.branding', $this->agency))
            ->assertForbidden(403);
    }
}
