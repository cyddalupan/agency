<?php

namespace Tests\Feature\Print;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "LANDAS: Print Page 'Missing Data'" (2026-11-05).
 *
 * Module index pages are paginated for the screen but the browser print
 * dialog only ever printed the current page, so printed output "missed" most
 * of the data. `?print=1` must render every filtered row so the operator can
 * print all entries of any module.
 */
class PrintAllEntriesTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);

        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        // 60 applicants > the 50-row screen page size (->
        // ApplicantController::index paginates at print_per_page(50)).
        foreach (range(1, 60) as $i) {
            Applicant::factory()->create([
                'agency_id'  => $this->agency->id,
                'first_name' => 'Print' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'last_name'  => 'Probe',
            ]);
        }
    }

    #[Test]
    public function screen_view_still_paginates_and_shows_a_print_link(): void
    {
        $response = $this->actingAs($this->user)->get(route('applicants.index'));

        $response->assertOk();
        $response->assertSee('print=1', false);
    }

    #[Test]
    public function print_mode_renders_every_row_not_just_the_first_page(): void
    {
        $screen = $this->actingAs($this->user)->get(route('applicants.index'));
        $screen->assertOk();

        $printed = $this->actingAs($this->user)->get(route('applicants.index', ['print' => 1]));
        $printed->assertOk();

        // "Probe" shows twice per row (initials + full name), so 50 rows => 100.
        $screenRows  = substr_count($screen->getContent(), 'Probe');
        $printedRows = substr_count($printed->getContent(), 'Probe');

        $this->assertSame(100, $screenRows, 'Screen view should stay on 50 rows.');
        $this->assertSame(120, $printedRows, 'Print mode must render all 60 entries.');
        $this->assertGreaterThan($screenRows, $printedRows);
    }

    #[Test]
    public function print_mode_includes_a_printed_on_header_with_active_filters(): void
    {
        $printed = $this->actingAs($this->user)
            ->get(route('applicants.index', ['print' => 1, 'type' => 'all']));

        $printed->assertOk();
        $printed->assertSee('Printed', false);
        $printed->assertSee('Filters', false);
        $printed->assertSee('Type: all', false);
    }
}
