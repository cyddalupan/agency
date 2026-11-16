<?php

namespace App\Services;

use App\Models\AuditFinding;
use App\Models\NonConformity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * QMS-17 (card 6afa0dae821b259e5f9fd4d4): turning a finding into a
 * non-conformity.
 *
 * A one-click action from the finding screen, but the rule is kept here rather
 * than in the controller so a future bulk action or console command cannot
 * create the link without going through the same checks.
 */
class AuditFindingToNonConformity
{
    /**
     * Create the NC for a finding and link the two.
     *
     * The finding's own text carries across, because re-typing what the
     * auditor already wrote is how the two records end up disagreeing.
     */
    public function spawn(AuditFinding $finding, ?int $userId = null): NonConformity
    {
        if ($finding->hasNonConformity()) {
            throw ValidationException::withMessages([
                'non_conformity_id' => 'This finding already has a non-conformity linked to it.',
            ]);
        }

        $finding->loadMissing('internalAudit');

        return DB::transaction(function () use ($finding, $userId) {
            $audit = $finding->internalAudit;

            $nc = new NonConformity;

            $nc->forceFill([
                'agency_id'            => $finding->agency_id,
                'ref_no'               => $this->nextNcRef(),
                'source'               => NonConformity::SOURCE_AUDIT,
                'detected_date'        => $audit?->conducted_date ?? now()->toDateString(),
                'process_area'         => $audit?->scope,
                'description'          => $finding->description,
                'requirement_breached' => $finding->clause_reference,
                'severity'             => AuditFinding::SEVERITY_FOR_TYPE[$finding->type]
                    ?? NonConformity::SEVERITY_MINOR,
                'status'               => NonConformity::STATUS_OPEN,
                'created_by'           => $userId,
            ]);

            $nc->save();

            $finding->forceFill(['non_conformity_id' => $nc->id])->save();

            return $nc;
        });
    }

    /**
     * Same numbering rule the NC screens use, kept in step deliberately: if it
     * drifts, a spawned NC collides with a hand-entered one.
     */
    private function nextNcRef(): string
    {
        $last = NonConformity::withTrashed()
            ->where('ref_no', 'like', 'NC-%')
            ->pluck('ref_no')
            ->map(fn ($ref) => (int) preg_replace('/\D/', '', (string) $ref))
            ->max() ?? 0;

        return 'NC-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }
}
