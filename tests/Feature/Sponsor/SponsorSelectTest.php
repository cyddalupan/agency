<?php

namespace Tests\Feature\Sponsor;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SponsorSelectTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $sponsorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create(['status' => 'active']);

        // Create sponsor with matching id_number = username
        $this->sponsorUser = User::create([
            'agency_id' => $this->agency->id,
            'name'      => 'Test Sponsor',
            'email'     => 'sponsor@test.com',
            'username'  => 'SPONSOR-001',
            'password'  => bcrypt('password'),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        // The Sponsor record must have id_number = user's username
        Sponsor::create([
            'agency_id'    => $this->agency->id,
            'id_number'    => 'SPONSOR-001',
            'company_name' => 'Test Sponsor Co',
            'email'        => 'sponsor@test.com',
            'status'       => 'active',
        ]);
    }

    #[Test]
    public function sponsor_can_select_applicant_from_lineup(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 0,
            'status'      => 'active',
        ]);

        $this->actingAs($this->sponsorUser);

        $response = $this->post(route('sponsor.select'), [
            'applicant_id' => $applicant->id,
        ]);

        $response->assertRedirect(route('sponsor.my-applicants'));
        $response->assertSessionHas('success');

        $sponsor = Sponsor::where('id_number', 'SPONSOR-001')->first();

        $this->assertDatabaseHas('sponsor_applicant', [
            'sponsor_id'    => $sponsor->id,
            'applicant_id'  => $applicant->id,
            'status'        => 'active',
        ]);
    }

    #[Test]
    public function sponsor_cannot_select_same_applicant_twice(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 0,
            'status'      => 'active',
        ]);

        $this->actingAs($this->sponsorUser);

        $this->post(route('sponsor.select'), ['applicant_id' => $applicant->id]);

        $response = $this->post(route('sponsor.select'), ['applicant_id' => $applicant->id]);

        $response->assertSessionHasErrors('applicant_id');
    }

    #[Test]
    public function sponsor_cannot_select_nonexistent_applicant(): void
    {
        $this->actingAs($this->sponsorUser);

        $response = $this->post(route('sponsor.select'), [
            'applicant_id' => 99999,
        ]);

        $response->assertSessionHasErrors('applicant_id');
    }

    #[Test]
    public function sponsor_can_unselect_applicant(): void
    {
        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 0,
            'status'      => 'active',
        ]);

        $sponsor = Sponsor::where('id_number', 'SPONSOR-001')->first();

        $sponsor->applicants()->attach($applicant->id, [
            'selected_at' => now(),
            'status'      => 'active',
        ]);

        $this->actingAs($this->sponsorUser);

        $response = $this->post(route('sponsor.unselect'), [
            'applicant_id' => $applicant->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sponsor_applicant', [
            'sponsor_id'   => $sponsor->id,
            'applicant_id' => $applicant->id,
            'status'       => 'removed',
        ]);
    }

    #[Test]
    public function my_applicants_shows_selected_applicants(): void
    {
        $sponsor = Sponsor::where('id_number', 'SPONSOR-001')->first();

        $applicant = Applicant::factory()->create([
            'agency_id'   => $this->agency->id,
            'status_code' => 0,
            'status'      => 'active',
        ]);

        $sponsor->applicants()->attach($applicant->id, [
            'selected_at' => now(),
            'status'      => 'active',
        ]);

        $this->actingAs($this->sponsorUser);

        $response = $this->get(route('sponsor.my-applicants'));

        $response->assertOk();
        $response->assertSee($applicant->first_name ?? 'Applicant');
    }
}
