<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\CivilStatus;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Cyd 2026-10-07 — "Civil Status - Add 'Single with Children'".
 *
 * The civil statuses live in the global `civil_statuses` reference table
 * (seeded + added by migration 2026_10_07_000001).
 */
class CivilStatusSingleWithChildrenTest extends TestCase
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
    }

    #[Test]
    public function single_with_children_exists_in_reference_data(): void
    {
        $this->assertDatabaseHas('civil_statuses', ['name' => 'Single with Children']);
        $this->assertNotNull(CivilStatus::where('name', 'Single with Children')->first());
    }

    #[Test]
    public function migration_does_not_duplicate_an_existing_row(): void
    {
        $status = CivilStatus::where('name', 'Single with Children')->firstOrFail();

        // Re-running the migration's insert guard must not add a second row.
        $migration = require database_path('migrations/2026_10_07_000001_add_single_with_children_civil_status.php');
        $migration->up();

        $this->assertSame(
            1,
            CivilStatus::where('name', 'Single with Children')->count(),
            'The data migration must be idempotent.'
        );
        $this->assertSame($status->id, CivilStatus::where('name', 'Single with Children')->first()->id);
    }

    #[Test]
    public function create_form_offers_single_with_children(): void
    {
        $response = $this->actingAs($this->user)->get(route('applicants.create'));

        $response->assertOk();
        $response->assertSee('Single with Children');
    }

    #[Test]
    public function applicant_can_be_saved_with_single_with_children(): void
    {
        $status = CivilStatus::where('name', 'Single with Children')->firstOrFail();

        $response = $this->actingAs($this->user)->post(route('applicants.store'), [
            'first_name'      => 'Liza',
            'last_name'       => 'Mendoza',
            'email'           => 'liza@example.com',
            'contact'         => '09171234567',
            'gender'          => 'female',
            'birthdate'       => '1993-04-02',
            'civil_status_id' => $status->id,
        ]);

        $response->assertRedirect(route('applicants.index'));

        $applicant = Applicant::where('first_name', 'Liza')->first();
        $this->assertNotNull($applicant);
        $this->assertSame($status->id, $applicant->civil_status_id);
        $this->assertSame('Single with Children', $applicant->civilStatus->name);
    }
}
