<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    #[Test]
    public function agency_user_can_view_company_profile_page(): void
    {
        $this->agency->update(['company_profile' => '<h2>Our Process</h2><p>Step one.</p>']);

        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $response = $this->actingAs($user)->get(route('company-profile.show'));

        $response->assertOk();
        $response->assertViewIs('company-profile.show');
        $response->assertSee('Our Process');
        $response->assertSee('Step one.');
    }

    #[Test]
    public function agency_admin_can_open_editor(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('company-profile.edit'));

        $response->assertOk();
        $response->assertViewIs('company-profile.edit');
    }

    #[Test]
    public function staff_cannot_open_editor(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)->get(route('company-profile.edit'))->assertForbidden();
    }

    #[Test]
    public function admin_can_save_and_content_is_sanitized(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->withoutMiddleware();
        $response = $this->actingAs($admin)
            ->post(route('company-profile.update'), [
                'content' => '<h2>Process</h2><script>alert("x")</script><p onclick="bad()">Clean text</p><a href="javascript:alert(1)">bad link</a>',
            ]);

        $response->assertRedirect(route('company-profile.show'));

        $this->agency->refresh();
        $saved = $this->agency->company_profile;

        $this->assertStringContainsString('<h2>Process</h2>', $saved);
        $this->assertStringContainsString('Clean text', $saved);
        $this->assertStringNotContainsString('<script', $saved);
        $this->assertStringNotContainsString('javascript:', $saved);
        $this->assertStringNotContainsString('onclick', $saved);
    }

    #[Test]
    public function agency_content_is_isolated_per_agency(): void
    {
        $other = Agency::factory()->create(['company_profile' => '<p>Other agency content</p>']);

        $user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('company-profile.show'));

        $response->assertOk();
        $response->assertDontSee('Other agency content');
        $response->assertSee('Nothing here yet');
    }

    #[Test]
    public function saving_twice_does_not_double_escape_the_content(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        Storage::fake('public');

        $png = base64_encode((string) file_get_contents(base_path('public/favicon.ico')) ?: 'fake-image-bytes');
        $html = '<h2>Our Process</h2><p>Step one &amp; two</p><img src="data:image/png;base64,'.$png.'">';

        $this->withoutMiddleware();
        $this->actingAs($admin)->post(route('company-profile.update'), ['content' => $html]);
        $first = $this->agency->fresh()->company_profile;

        // Inline base64 images are moved to disk instead of being stored as blobs.
        $this->assertStringContainsString('<h2>Our Process</h2>', $first);
        $this->assertStringContainsString('/storage/company-profile/'.$this->agency->id.'/', $first);
        $this->assertStringNotContainsString('base64,', $first);

        // Second save, exactly like re-opening the editor and pressing Save again.
        $this->actingAs($admin)->post(route('company-profile.update'), ['content' => $first]);
        $second = $this->agency->fresh()->company_profile;

        $this->assertStringNotContainsString('&lt;', $second, 'Content was HTML-escaped on save.');
        $this->assertStringContainsString('<img src="', $second, 'The image lost its src on re-save.');
        $this->assertSame($first, $second, 'Re-saving unchanged content must be a no-op.');
    }

    #[Test]
    public function editor_loads_raw_html_once_escaped(): void
    {
        $this->agency->update(['company_profile' => '<p>Plain <strong>bold</strong></p>']);

        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('company-profile.edit'));

        $response->assertOk();
        // Escaped exactly once for the textarea, never twice.
        $response->assertSee('&lt;p&gt;Plain &lt;strong&gt;bold&lt;/strong&gt;&lt;/p&gt;', false);
        $response->assertDontSee('&amp;lt;p&amp;gt;', false);
    }

    #[Test]
    public function page_renders_the_four_tabs(): void
    {
        $user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $response = $this->actingAs($user)->get(route('company-profile.show'));

        $response->assertOk();
        $response->assertSee('Company Profile');
        $response->assertSee('Organizational Chart');
        $response->assertSee('Recruitment Process');
        $response->assertSee('System User Guide');
    }

    #[Test]
    public function each_tab_keeps_its_own_content(): void
    {
        $admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);

        $this->withoutMiddleware();
        $this->actingAs($admin)->post(route('company-profile.tab.update', ['tab' => 'company-profile']), [
            'content' => '<p>Company block</p>',
        ]);
        $this->actingAs($admin)->post(route('company-profile.tab.update', ['tab' => 'organizational-chart']), [
            'content' => '<p>Org chart block</p>',
        ]);

        $agency = $this->agency->fresh();

        $this->assertSame('<p>Company block</p>', $agency->pageTabContent('company-profile'));
        $this->assertSame('<p>Org chart block</p>', $agency->pageTabContent('organizational-chart'));
        $this->assertSame('', $agency->pageTabContent('recruitment-process'));
    }

    #[Test]
    public function org_chart_tab_content_is_not_leaked_into_other_tabs(): void
    {
        $this->agency->update([
            'page_tabs' => [
                'company-profile' => '<p>Profile only</p>',
                'organizational-chart' => '<p>Chart only</p>',
            ],
        ]);

        $user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->actingAs($user)->get(route('company-profile.tab', ['tab' => 'company-profile']))
            ->assertOk()->assertSee('Profile only')->assertDontSee('Chart only');

        $this->actingAs($user)->get(route('company-profile.tab', ['tab' => 'organizational-chart']))
            ->assertOk()->assertSee('Chart only')->assertDontSee('Profile only');
    }

    #[Test]
    public function legacy_company_profile_content_still_shows_in_the_first_tab(): void
    {
        $this->agency->update(['company_profile' => '<p>Legacy content</p>']);
        $this->agency->update(['page_tabs' => null]);

        $user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->actingAs($user)->get(route('company-profile.show'))
            ->assertOk()->assertSee('Legacy content');
    }

    #[Test]
    public function user_guide_tab_starts_with_a_guide(): void
    {
        $user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $response = $this->actingAs($user)->get(route('company-profile.tab', ['tab' => 'system-user-guide']));

        $response->assertOk();
        $response->assertSee('Landas System User Guide');
        $response->assertSee('Applicants');
    }

    #[Test]
    public function unknown_tab_is_not_found(): void
    {
        $user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->actingAs($user)->get('/company-profile/not-a-tab')->assertNotFound();
    }

    #[Test]
    public function admin_can_upload_an_editor_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);

        $response = $this->actingAs($admin)->post(route('company-profile.image'), [
            'image' => UploadedFile::fake()->image('chart.png', 800, 600),
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['url']);
        $url = $response->json('url');
        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($path);
    }

    #[Test]
    public function non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);

        $this->actingAs($admin)->postJson(route('company-profile.image'), [
            'image' => UploadedFile::fake()->create('guide.pdf', 100, 'application/pdf'),
        ])->assertStatus(422);
    }

    #[Test]
    public function staff_cannot_upload_editor_images(): void
    {
        Storage::fake('public');

        $staff = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'staff']);

        $this->actingAs($staff)->postJson(route('company-profile.image'), [
            'image' => UploadedFile::fake()->image('chart.png'),
        ])->assertForbidden();
    }

    #[Test]
    public function edit_link_opens_the_tab_that_was_clicked(): void
    {
        // Josh report 2026-10-22: "Hndi ko maedit ibang tab" — the "Edit this
        // tab" button produced /company-profile/edit?tab=..., which the
        // controller ignored, so every tab opened the Company Profile editor.
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        foreach (['organizational-chart', 'recruitment-process', 'system-user-guide'] as $tab) {
            $response = $this->actingAs($admin)->get(route('company-profile.edit', ['tab' => $tab]));
            $response->assertOk();
            $response->assertViewIs('company-profile.edit');
            $this->assertSame($tab, $response->viewData('activeTab'), "edit() opened the wrong tab for {$tab}");
        }
    }

    #[Test]
    public function edit_button_and_tab_links_point_at_the_tab_edit_routes(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('company-profile.tab', ['tab' => 'organizational-chart']));
        $response->assertOk();
        $response->assertSee(route('company-profile.tab.edit', ['tab' => 'organizational-chart']), false);
    }

    #[Test]
    public function saving_via_the_tab_route_stores_that_tab_only(): void
    {
        $admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->agency->update(['company_profile' => '<p>Tab one body</p>']);

        $this->actingAs($admin)
            ->post(route('company-profile.tab.update', ['tab' => 'recruitment-process']), [
                'content' => '<p>Five step process</p>',
            ])
            ->assertRedirect(route('company-profile.tab', ['tab' => 'recruitment-process']));

        $this->agency->refresh();
        $this->assertStringContainsString('Five step process', (string) $this->agency->pageTabContent('recruitment-process'));
        $this->assertStringContainsString('Tab one body', (string) $this->agency->company_profile);
    }
}
