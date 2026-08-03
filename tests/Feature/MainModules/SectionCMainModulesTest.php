<?php

namespace Tests\Feature\MainModules;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Section C — Main Modules.
 *
 * C1: Rename 'Employers' -> 'FRA' (menu, labels, copy, view text)
 * C2: Move 'Reports' to top-level MAIN menu
 *
 * The rename is presentational: routes/controllers/tables stay under `employers`.
 * Tests assert the rendered UI shows the new "FRA" branding in the main nav and
 * key views, and that Reports is a top-level Main link.
 */
class SectionCMainModulesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['name' => 'Demo Agency']);
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function sidebar_shows_fra_instead_of_employers_in_main_menu(): void
    {
        $this->actingAs($this->admin)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->assertSee('FRA')
            ->assertDontSee('Employers');
    }

    #[Test]
    public function sidebar_reports_is_a_top_level_main_link(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();

        // The sidebar renders a "📋 Main" header block with Dashboard/Applicants/Employers(Main).
        // Reports must appear BEFORE the "⚙️ System" separator so it is top-level Main, not System.
        $mainPos   = strpos($html, '📋 Main');
        $reportsPos = strpos($html, route('reports.index'));
        $systemPos = strpos($html, '⚙️ System');

        $this->assertNotFalse($mainPos, 'Main menu header present');
        $this->assertNotFalse($reportsPos, 'Reports route link present in sidebar');
        $this->assertNotNull($systemPos, 'System section present');

        $this->assertLessThan($systemPos, $reportsPos, 'Reports appears before System section => top-level Main');
    }

    #[Test]
    public function employers_index_page_uses_fra_label_and_add_button(): void
    {
        $this->actingAs($this->admin)
            ->get(route('employers.index'))
            ->assertOk()
            ->assertSee('FRA')
            ->assertSee('Add FRA')
            ->assertDontSee('Add Employer');
    }

    #[Test]
    public function employers_create_page_uses_fra_labels(): void
    {
        $this->actingAs($this->admin)
            ->get(route('employers.create'))
            ->assertOk()
            ->assertSee('Add New FRA')
            ->assertSee('Save FRA')
            ->assertDontSee('Add New Employer')
            ->assertDontSee('Save Employer');
    }

    #[Test]
    public function employers_edit_page_uses_fra_label(): void
    {
        $employer = \App\Models\Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Tester Corp',
        ]);

        $this->actingAs($this->admin)
            ->get(route('employers.edit', $employer))
            ->assertOk()
            ->assertSee('Edit FRA')
            ->assertDontSee('Edit Employer');
    }

    #[Test]
    public function employers_show_page_back_button_uses_fra(): void
    {
        $employer = \App\Models\Employer::factory()->create([
            'agency_id' => $this->agency->id,
            'name'      => 'Tester Corp',
        ]);

        $this->actingAs($this->admin)
            ->get(route('employers.show', $employer))
            ->assertOk()
            ->assertSee('Back to FRAs')
            ->assertDontSee('Back to Employers');
    }
}
