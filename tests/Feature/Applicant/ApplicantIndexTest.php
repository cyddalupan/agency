<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantIndexTest extends TestCase
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
    public function unauthenticated_user_cannot_access_applicants(): void
    {
        $response = $this->get(route('applicants.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_applicants_list(): void
    {
        Applicant::factory()->count(3)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertOk();
        $response->assertViewHas('applicants');
    }

    #[Test]
    public function applicant_list_paginates(): void
    {
        Applicant::factory()->count(25)->create([
            'agency_id' => $this->agency->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertOk();
        $this->assertCount(15, $response->viewData('applicants'));
    }

    #[Test]
    public function applicant_list_shows_full_name(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertSee('Juan Dela Cruz');
    }

    #[Test]
    public function applicant_list_shows_status_badge(): void
    {
        Applicant::factory()->create([
            'agency_id' => $this->agency->id,
            'status_code' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertSee('Pending');
    }

    #[Test]
    public function tenant_cannot_see_other_tenants_applicants(): void
    {
        $otherAgency = Agency::factory()->create();
        Applicant::factory()->count(3)->create([
            'agency_id' => $otherAgency->id,
        ]);
        Applicant::factory()->count(2)->create([
            'agency_id' => $this->agency->id,
        ]);

        // Simulate tenant context so the TenantScope applies
        app()->instance('tenant_agency', $this->agency);

        $response = $this->actingAs($this->user)
            ->get(route('applicants.index'));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('applicants'));
    }
}
