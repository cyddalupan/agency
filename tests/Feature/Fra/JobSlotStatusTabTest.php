<?php

namespace Tests\Feature\Fra;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\User;
use App\Services\JobSlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The card is about the user-facing flow: a recruiter sets the Status tab to
 * DEPLOYED and the job order's slot count must drop. The service works in
 * isolation (JobSlotDeductionTest); this proves the controller calls it.
 */
class JobSlotStatusTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_deployed_on_the_status_tab_deducts_the_slot(): void
    {
        $agency = Agency::factory()->create(['subdomain' => 'finas', 'name' => 'FINAS']);

        $user = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);

        $job = JobPosition::create([
            'agency_id' => $agency->id,
            'employer_id' => Employer::factory()->create([
                'agency_id' => $agency->id,
                'name' => 'Acme Manpower',
            ])->id,
            'name' => 'Factory Worker',
            'total_slots' => 3,
            'occupied' => 0,
            'status' => 'open',
        ]);

        $applicant = Applicant::factory()->create([
            'agency_id' => $agency->id,
            'job_id' => $job->id,
            'status_code' => 0,
        ]);

        $this->actingAs($user)
            ->patch(route('applicants.status', $applicant), [
                'status_code' => JobSlotService::DEPLOYED,
            ])
            ->assertRedirect();

        $this->assertSame(JobSlotService::DEPLOYED, (int) $applicant->fresh()->status_code);
        $this->assertSame(1, $job->fresh()->occupied, 'Slot was not deducted when status became Deployed.');
    }
}
