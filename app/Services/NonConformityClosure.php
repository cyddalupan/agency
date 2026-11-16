<?php

namespace App\Services;

use App\Models\CorrectiveAction;
use App\Models\NonConformity;
use Illuminate\Support\Collection;

/**
 * QMS-15 (card 6afa0dac333d35f77d709e61): the closure gate for non-conformities.
 *
 * Spec §14 requires *verification* and *effectiveness review* before closure,
 * and notes that this is the most commonly skipped control. Skipping it is
 * easy to do quietly: mark the action done, close the ticket, never check
 * whether the fix worked. So the rule lives here rather than in a screen,
 * and `NonConformity` refuses to be saved as `closed` unless this class agrees.
 *
 * The two questions it asks are deliberately separate:
 *
 *   - **Verification** (`verified_at`): was the action actually carried out
 *     as described? An action can be "done" — `completed_date` set — and never
 *     be checked by anyone.
 *   - **Effectiveness** (`effectiveness_verified_at`): did carrying it out
 *     stop the problem recurring? A verified action can still be useless.
 *
 * Closure needs both, on every action.
 */
class NonConformityClosure
{
    /**
     * Reasons the non-conformity may not be closed. Empty means it may.
     *
     * Returning a list rather than a bool so a screen can tell the user every
     * reason at once instead of making them fix one, retry, and discover the
     * next.
     *
     * @return list<string>
     */
    public function closureBlockers(NonConformity $nonConformity): array
    {
        $blockers = [];

        $actions = $this->actionsFor($nonConformity);

        // A non-conformity with no corrective action at all is the purest form
        // of the failure this gate exists to catch: nothing was ever done, so
        // there is nothing to verify and the record would close silently.
        if ($actions->isEmpty()) {
            $blockers[] = 'There are no corrective actions recorded, so the fix has never been tested.';

            return $blockers;
        }

        foreach ($actions as $index => $action) {
            $label = 'Action ' . ($index + 1);
            $summary = mb_substr(trim((string) $action->action_text), 0, 60);

            if ($action->verified_at === null) {
                $blockers[] = "{$label} ({$summary}) has not been verified.";
            }

            if ($action->effectiveness_verified_at === null) {
                $blockers[] = "{$label} ({$summary}) has not had its effectiveness reviewed.";
            }
        }

        return $blockers;
    }

    /** True when every corrective action is both verified and effectiveness-reviewed. */
    public function canClose(NonConformity $nonConformity): bool
    {
        return $this->closureBlockers($nonConformity) === [];
    }

    /**
     * The actions this gate judges.
     *
     * Soft-deleted actions are excluded: removing an action is how a duplicate
     * or a mistake is corrected, and the gate should not demand verification of
     * something that no longer exists.
     */
    private function actionsFor(NonConformity $nonConformity): Collection
    {
        $relation = $nonConformity->relationLoaded('correctiveActions')
            ? $nonConformity->correctiveActions
            : $nonConformity->correctiveActions()->get();

        return $relation->filter(
            fn (CorrectiveAction $action) => $action->deleted_at === null
        )->values();
    }
}
