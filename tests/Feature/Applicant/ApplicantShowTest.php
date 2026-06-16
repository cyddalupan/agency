<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantShowTest extends TestCase
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
    public function unauthenticated_user_cannot_view_applicant(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->get(route('applicants.show', $applicant));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function show_displays_applicant_details(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'email'      => 'maria@example.com',
            'contact'    => '09171234567',
            'gender'     => 'female',
            'birthdate'  => '1995-06-15',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertOk();
        $response->assertSee('Maria');
        $response->assertSee('Santos');
        $response->assertSee('maria@example.com');
        $response->assertSee('09171234567');
    }

    #[Test]
    public function show_shows_status_badge(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertSee('Pending');
    }

    #[Test]
    public function show_has_edit_button(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertSee('Edit');
    }

    #[Test]
    public function show_has_back_button(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.show', $applicant));

        $response->assertSee('Back to Applicants');
    }
}
