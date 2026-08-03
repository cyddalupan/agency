<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** End-to-end: the Basic tab "Save Update" persists number_of_siblings. */
class PersonalInformationBasicSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create();
        $this->user = User::factory()->create(['agency_id' => $this->agency->id, 'user_type' => 'admin']);
        $this->applicant = Applicant::factory()->create(['agency_id' => $this->agency->id]);
        app()->instance('tenant_agency', $this->agency);
    }

    #[Test]
    public function save_update_persists_number_of_siblings(): void
    {
        $r = $this->actingAs($this->user)
            ->patch(route('applicants.basic.update', $this->applicant), ['number_of_siblings' => 4]);
        $r->assertRedirect(route('applicants.show', $this->applicant));
        $this->assertDatabaseHas('applicants', ['id' => $this->applicant->id, 'number_of_siblings' => 4]);
    }

    #[Test]
    public function save_update_redirects_back_without_errors(): void
    {
        $this->actingAs($this->user)
            ->patch(route('applicants.basic.update', $this->applicant), [])
            ->assertRedirect(route('applicants.show', $this->applicant))
            ->assertSessionHasNoErrors();
    }
}
