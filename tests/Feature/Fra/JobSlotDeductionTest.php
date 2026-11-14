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
 * Card 6af822bc: "Total Slot - should deduct when the STATUS of the applicant
 * under the FRA is DEPLOYED".
 *
 * occupied used to be hardcoded to 0 on create/update and never touched, so
 * every job order advertised all its slots as free no matter how many people
 * were actually deployed against it.
 */
class JobSlotDeductionTest extends TestCase
{
    use RefreshDatabase;

    private function agency(): Agency
    {
        return Agency::factory()->create(['subdomain' => 'finas', 'name' => 'FINAS']);
    }

    private function job(Agency $agency, int $slots = 5): JobPosition
    {
        return JobPosition::create([
            'agency_id' => $agency->id,
            'employer_id' => Employer::factory()->create([
                'agency_id' => $agency->id,
                'name' => 'Acme Manpower',
            ])->id,
            'name' => 'Factory Worker',
            'total_slots' => $slots,
            'occupied' => 0,
            'status' => 'open',
        ]);
    }

    private function applicant(Agency $agency, JobPosition $job, int $status): Applicant
    {
        return Applicant::factory()->create([
            'agency_id' => $agency->id,
            'job_id' => $job->id,
            'status_code' => $status,
        ]);
    }

    public function test_deploying_an_applicant_consumes_a_slot(): void
    {
        $agency = $this->agency();
        $job = $this->job($agency);
        $applicant = $this->applicant($agency, $job, 0);

        $this->assertSame(0, $job->fresh()->occupied);

        $applicant->update(['status_code' => JobSlotService::DEPLOYED]);
        JobSlotService::syncForApplicant($applicant->id);

        $this->assertSame(1, $job->fresh()->occupied);
    }

    public function test_moving_off_deployed_gives_the_slot_back(): void
    {
        $agency = $this->agency();
        $job = $this->job($agency);
        $applicant = $this->applicant($agency, $job, JobSlotService::DEPLOYED);

        JobSlotService::syncForApplicant($applicant->id);
        $this->assertSame(1, $job->fresh()->occupied);

        // e.g. a backout or a correction
        $applicant->update(['status_code' => 50]);
        JobSlotService::syncForApplicant($applicant->id);

        $this->assertSame(0, $job->fresh()->occupied);
    }

    public function test_only_deployed_applicants_are_counted(): void
    {
        $agency = $this->agency();
        $job = $this->job($agency);

        $this->applicant($agency, $job, 0);   // Pending
        $this->applicant($agency, $job, 6);   // Selected
        $this->applicant($agency, $job, JobSlotService::DEPLOYED);
        $this->applicant($agency, $job, JobSlotService::DEPLOYED);

        JobSlotService::syncJobPosition($job->id);

        $this->assertSame(2, $job->fresh()->occupied);
    }

    public function test_a_different_job_orders_deployed_applicants_are_not_counted(): void
    {
        $agency = $this->agency();
        $mine = $this->job($agency);
        $other = $this->job($agency);

        $this->applicant($agency, $mine, JobSlotService::DEPLOYED);
        $this->applicant($agency, $other, JobSlotService::DEPLOYED);
        $this->applicant($agency, $other, JobSlotService::DEPLOYED);

        JobSlotService::syncJobPosition($mine->id);

        $this->assertSame(1, $mine->fresh()->occupied);
        $this->assertSame(0, $other->fresh()->occupied); // not synced yet
    }

    public function test_occupied_never_exceeds_total_slots(): void
    {
        $agency = $this->agency();
        $job = $this->job($agency, 2);

        for ($i = 0; $i < 4; $i++) {
            $this->applicant($agency, $job, JobSlotService::DEPLOYED);
        }

        JobSlotService::syncJobPosition($job->id);

        $this->assertSame(2, $job->fresh()->occupied);
        $this->assertSame(0, JobSlotService::free($job->fresh()));
    }

    public function test_moving_a_deployed_applicant_to_another_job_frees_the_first(): void
    {
        $agency = $this->agency();
        $from = $this->job($agency);
        $to = $this->job($agency);
        $applicant = $this->applicant($agency, $from, JobSlotService::DEPLOYED);

        JobSlotService::syncJobPosition($from->id);
        $this->assertSame(1, $from->fresh()->occupied);

        $previousJobId = $applicant->job_id;
        $applicant->update(['job_id' => $to->id]);
        JobSlotService::syncForApplicant($applicant->id, $previousJobId);

        $this->assertSame(0, $from->fresh()->occupied);
        $this->assertSame(1, $to->fresh()->occupied);
    }
}
