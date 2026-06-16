<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\JobPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortalJobListingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function unauthenticated_applicant_is_redirected_to_login_for_job_listings(): void
    {
        $response = $this->get(route('portal.jobs.index'));

        $response->assertRedirect(route('portal.login'));
    }

    #[Test]
    public function unauthenticated_applicant_is_redirected_to_login_for_job_details(): void
    {
        $agency = Agency::factory()->create();
        $job = JobPosition::factory()->for($agency)->create(['status' => 'open']);

        $response = $this->get(route('portal.jobs.show', $job));

        $response->assertRedirect(route('portal.login'));
    }

    #[Test]
    public function job_listings_shows_open_positions_for_the_applicants_agency(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        $job = JobPosition::factory()->for($agency)->create([
            'status' => 'open',
            'name' => 'Software Engineer',
            'salary' => 50000,
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.index'));

        $response->assertOk();
        $response->assertSee('Software Engineer');
        $response->assertSee('50,000');
    }

    #[Test]
    public function job_listings_hides_closed_and_filled_positions(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        JobPosition::factory()->for($agency)->create(['status' => 'open', 'name' => 'Open Position']);
        JobPosition::factory()->for($agency)->create(['status' => 'closed', 'name' => 'Closed Position']);
        JobPosition::factory()->for($agency)->create(['status' => 'filled', 'name' => 'Filled Position']);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.index'));

        $response->assertOk();
        $response->assertSee('Open Position');
        $response->assertDontSee('Closed Position');
        $response->assertDontSee('Filled Position');
    }

    #[Test]
    public function job_listings_does_not_show_positions_from_other_agencies(): void
    {
        $agency = Agency::factory()->create();
        $otherAgency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        JobPosition::factory()->for($agency)->create(['status' => 'open', 'name' => 'My Agency Job']);
        JobPosition::factory()->for($otherAgency)->create(['status' => 'open', 'name' => 'Other Agency Job']);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.index'));

        $response->assertOk();
        $response->assertSee('My Agency Job');
        $response->assertDontSee('Other Agency Job');
    }

    #[Test]
    public function job_details_shows_specific_position(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        $job = JobPosition::factory()->for($agency)->create([
            'status' => 'open',
            'name' => 'Senior Developer',
            'content' => 'We need an experienced developer.',
            'salary' => 80000,
        ]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.show', $job));

        $response->assertOk();
        $response->assertSee('Senior Developer');
        $response->assertSee('We need an experienced developer.');
    }

    #[Test]
    public function job_details_returns_404_for_nonexistent_job(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.show', 99999));

        $response->assertNotFound();
    }

    #[Test]
    public function job_details_returns_404_for_job_from_other_agency(): void
    {
        $agency = Agency::factory()->create();
        $otherAgency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        $otherJob = JobPosition::factory()->for($otherAgency)->create(['status' => 'open']);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.show', $otherJob));

        $response->assertNotFound();
    }

    #[Test]
    public function job_details_returns_404_for_closed_or_filled_position(): void
    {
        $agency = Agency::factory()->create();
        $applicant = Applicant::factory()->for($agency)->create(['password' => bcrypt('password')]);

        $closedJob = JobPosition::factory()->for($agency)->create(['status' => 'closed', 'name' => 'Closed Position']);

        $response = $this->actingAs($applicant, 'applicant')
            ->get(route('portal.jobs.show', $closedJob));

        $response->assertNotFound();
    }
}
