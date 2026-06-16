<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantEditTest extends TestCase
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
    public function unauthenticated_user_cannot_access_edit_form(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->get(route('applicants.edit', $applicant));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function edit_form_displays_correctly(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.edit', $applicant));

        $response->assertOk();
        $response->assertSee('Edit Applicant');
        $response->assertSee($applicant->first_name);
        $response->assertSee($applicant->last_name);
    }

    #[Test]
    public function update_requires_basic_fields(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name' => '',
                'last_name' => '',
            ]);

        $response->assertSessionHasErrors(['first_name', 'last_name']);
    }

    #[Test]
    public function update_changes_applicant(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Original',
            'last_name'  => 'Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name' => 'Updated',
                'last_name'  => 'Name',
                'email'      => 'updated@example.com',
                'status_code' => 1,
            ]);

        $response->assertRedirect(route('applicants.index'));
        $this->assertDatabaseHas('applicants', [
            'id'         => $applicant->id,
            'first_name' => 'Updated',
            'email'      => 'updated@example.com',
            'status_code' => 1,
        ]);
    }

    #[Test]
    public function update_preserves_agency_id(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'  => $this->agency->id,
            'first_name' => 'Same',
            'last_name'  => 'Agency',
        ]);

        $this->actingAs($this->user)
            ->put(route('applicants.update', $applicant), [
                'first_name' => 'Still',
                'last_name'  => 'SameAgency',
            ]);

        $this->assertDatabaseHas('applicants', [
            'id'         => $applicant->id,
            'agency_id'  => $this->agency->id,
        ]);
    }
}
