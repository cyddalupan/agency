<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * QMS-14 (card 6afa0dab2a673e08b88cdd67): complaint lifecycle.
 *
 * The intake → acknowledge → investigate → resolve → close path lives here
 * rather than in the controller, for the same reason the document control
 * workflow does: a rule that can only be enforced by whoever remembers to
 * call the right controller method is not a rule. Every screen that moves a
 * complaint goes through these methods.
 *
 * The transitions are deliberately narrow:
 *
 *   open ──acknowledge──► acknowledged ──investigate──► investigating
 *    │                          │                            │
 *    └──────────resolve─────────┴────────────resolve─────────┘
 *                                    ▼
 *                                 resolved ──close──► closed
 *
 * A complaint cannot be resolved without resolution text: "resolved" with no
 * statement of what was done is a status flag, not a record of the outcome,
 * and an auditor cannot test it. Closing likewise requires closure notes.
 *
 * Investigation notes are not persisted: QMS-13's `complaints` schema has no
 * notes column, and folding free text into `category` would corrupt a real
 * classification field. The detail screen records the transition and the
 * auditor sees it in the audit trail; a dedicated notes column is QMS-14+ work
 * if it is wanted.
 */
class ComplaintWorkflow
{
    /**
     * Record that the agency has acknowledged the complaint to the complainant.
     *
     * `acknowledged_at` is the moment the clock stopped for the ack SLA, so it
     * is set from the clock rather than accepted from the form. Re-acknowledging
     * an already-acknowledged complaint is allowed (the first timestamp wins):
     * people do click the button twice, and moving the timestamp later would
     * make a late acknowledgement look timely.
     */
    public function acknowledge(Complaint $complaint): Complaint
    {
        if ($complaint->is_closed) {
            throw new InvalidArgumentException('A closed complaint cannot be acknowledged.');
        }

        return DB::transaction(function () use ($complaint) {
            $attributes = [
                'status' => Complaint::STATUS_ACKNOWLEDGED,
            ];

            if ($complaint->acknowledged_at === null) {
                $attributes['acknowledged_at'] = now();
            }

            $complaint->update($attributes);

            return $complaint;
        });
    }

    /**
     * Mark the complaint as being investigated.
     *
     * Investigating is not required before resolving: a complaint that is
     * acknowledged and resolved the same afternoon should not need a
     * ceremonial extra click.
     */
    public function investigate(Complaint $complaint): Complaint
    {
        if ($complaint->is_closed) {
            throw new InvalidArgumentException('A closed complaint cannot be re-opened for investigation.');
        }

        return DB::transaction(function () use ($complaint) {
            $attributes = [
                'status' => Complaint::STATUS_INVESTIGATING,
            ];

            // Investigating implies the complaint reached someone, so if it was
            // never acknowledged, stamp that now rather than leaving a live ack
            // deadline that has plainly been met.
            if ($complaint->acknowledged_at === null) {
                $attributes['acknowledged_at'] = now();
            }

            $complaint->update($attributes);

            return $complaint;
        });
    }

    /**
     * Resolve the complaint with the outcome that the complainant was told.
     *
     * Resolution text is required by the caller and enforced again here, so a
     * future screen cannot bypass the rule by calling the service directly.
     */
    public function resolve(Complaint $complaint, string $resolution): Complaint
    {
        if ($complaint->is_closed) {
            throw new InvalidArgumentException('A closed complaint cannot be resolved again.');
        }

        if (trim($resolution) === '') {
            throw new InvalidArgumentException('A complaint cannot be resolved without resolution text.');
        }

        return DB::transaction(function () use ($complaint, $resolution) {
            $attributes = [
                'status'     => Complaint::STATUS_RESOLVED,
                'resolution' => trim($resolution),
            ];

            if ($complaint->resolved_at === null) {
                $attributes['resolved_at'] = now();
            }

            // Resolving is also the acknowledgement if none was recorded: the
            // complainants whose complaint got solved never need a separate
            // "we received it" step to be true.
            if ($complaint->acknowledged_at === null) {
                $attributes['acknowledged_at'] = now();
            }

            $complaint->update($attributes);

            return $complaint;
        });
    }

    /**
     * Close a resolved complaint with the notes that explain the closure.
     *
     * Only a resolved complaint can be closed. Closing straight from open
     * would hide an unresolved grievance behind a terminal status, which is
     * exactly the failure an audit is meant to catch.
     */
    public function close(Complaint $complaint, string $closureNotes): Complaint
    {
        if ($complaint->status !== Complaint::STATUS_RESOLVED) {
            throw new InvalidArgumentException('Only a resolved complaint can be closed.');
        }

        if (trim($closureNotes) === '') {
            throw new InvalidArgumentException('A complaint cannot be closed without closure notes.');
        }

        return DB::transaction(function () use ($complaint, $closureNotes) {
            $complaint->update([
                'status'        => Complaint::STATUS_CLOSED,
                'closure_notes' => trim($closureNotes),
            ]);

            return $complaint;
        });
    }

}
