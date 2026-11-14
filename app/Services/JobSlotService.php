<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\JobPosition;

/**
 * Keeps job_positions.occupied in step with reality.
 *
 * The FRA "Total Slot" card: a slot is consumed when an applicant under the job
 * order becomes DEPLOYED (status_code 8). The occupied column used to be
 * written as 0 on every create/update and never touched again, so every job
 * order claimed to have all slots free no matter how many people were deployed.
 *
 * We RECALCULATE instead of incrementing/decrementing. Applicants get moved back
 * off Deployed all the time (backout, cancel, correction), and a counter that
 * only ever goes up would drift within a day. The count is cheap — one indexed
 * COUNT per job order.
 */
class JobSlotService
{
    /** Status code that consumes a slot. */
    public const DEPLOYED = 8;

    /**
     * Recompute occupied for one job order.
     */
    public static function syncJobPosition(?int $jobPositionId): void
    {
        if (! $jobPositionId) {
            return;
        }

        $job = JobPosition::withoutGlobalScopes()->find($jobPositionId);
        if (! $job) {
            return;
        }

        self::apply($job);
    }

    /**
     * Recompute occupied for the job orders touching one applicant.
     *
     * Pass the job id the applicant had BEFORE the change: if they were
     * deployed under job A and then moved to job B (or off a job entirely),
     * job A only gets its slot back if we resync it too.
     */
    public static function syncForApplicant(int $applicantId, ?int $previousJobId = null): void
    {
        $applicant = Applicant::withoutGlobalScopes()->find($applicantId);

        self::syncJobPosition($applicant?->job_id);

        if ($previousJobId && $previousJobId !== $applicant?->job_id) {
            self::syncJobPosition($previousJobId);
        }
    }

    /**
     * "Free slots" the way every view computes it.
     */
    public static function free(JobPosition $job): int
    {
        return max(0, (int) $job->total_slots - (int) $job->occupied);
    }

    private static function apply(JobPosition $job): void
    {
        $deployed = Applicant::withoutGlobalScopes()
            ->where('job_id', $job->id)
            ->where('status_code', self::DEPLOYED)
            ->count();

        // Never let the counter exceed the advertised slots; the view prints
        // "occupied / total_slots" and an overrun reads as a data error.
        $occupied = min((int) $deployed, (int) $job->total_slots);

        if ((int) $job->occupied !== $occupied) {
            $job->occupied = $occupied;
            $job->save();
        }
    }
}
