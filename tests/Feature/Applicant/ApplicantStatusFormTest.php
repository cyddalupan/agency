<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantStatusFormTest extends TestCase
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
    public function create_form_shows_status_dropdown(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertStatus(200);
        $response->assertSee('status_code');
        $response->assertSee('Pending');
        $response->assertSee('For Interview');
        $response->assertSee('Deployed');
    }

    #[Test]
    public function create_form_defaults_status_to_pending(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('applicants.create'));

        $response->assertStatus(200);
        // Check that the Pending option is selected by default (code=0)
        $response->assertSee('selected');
    }

    #[Test]
    public function edit_form_shows_status_dropdown(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 5, // Selected
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertStatus(200);
        $response->assertSee('status_code');
        $response->assertSee('Selected');
        $response->assertSee('Deployed');
        $response->assertSee('For Interview');
    }

    #[Test]
    public function edit_form_preselects_current_status(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 8, // Deployed
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertStatus(200);
        $response->assertSee('Deployed');
    }

    #[Test]
    public function status_can_be_set_during_creation(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'  => 'Test',
                'last_name'   => 'User',
                'status_code' => 8, // Deployed
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name'  => 'Test',
            'last_name'   => 'User',
            'status_code' => 8,
        ]);
    }

    #[Test]
    public function status_can_be_set_to_pending_explicitly(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('applicants.store'), [
                'first_name'  => 'Another',
                'last_name'   => 'Person',
                'status_code' => 0, // Pending
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applicants', [
            'first_name'  => 'Another',
            'last_name'   => 'Person',
            'status_code' => 0,
        ]);
    }
}
