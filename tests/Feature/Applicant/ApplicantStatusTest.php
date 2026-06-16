<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantStatusTest extends TestCase
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
    public function new_applicant_defaults_to_pending(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $this->assertEquals(0, $applicant->status_code);
    }

    #[Test]
    public function status_can_be_updated_via_edit(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 0,
        ]);

        $statusPipeline = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

        foreach ($statusPipeline as $code) {
            $this->actingAs($this->user)
                ->put(route('applicants.update', $applicant), [
                    'first_name'  => $applicant->first_name,
                    'last_name'   => $applicant->last_name,
                    'status_code' => $code,
                ]);

            $applicant->refresh();
            $this->assertEquals($code, $applicant->status_code);
        }
    }

    #[Test]
    public function status_badge_shows_on_index(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 5, // Selected
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertSee('Selected');
    }

    #[Test]
    public function status_badge_shows_on_show(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 8, // Deployed
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertSee('Deployed');
    }
}
