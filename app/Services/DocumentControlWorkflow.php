<?php

namespace App\Services;

use App\Models\ControlledDocument;
use App\Models\ControlledDocumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * QMS-10 (card 6afa0da73812c0fa2168d3b4): the document lifecycle.
 *
 * Spec §10.1/10.2 — preparer → reviewer → approver, and an approved document
 * is never overwritten. The lifecycle is deliberately a small set of explicit
 * transitions rather than a free-form status setter, because the whole point of
 * document control is that you cannot skip a step:
 *
 *   version: draft ──submit──> in_review ──approve──> approved
 *                                  │                     │
 *                                  └──return──> draft    └──(a new version
 *                                        (with a note)        supersedes it)
 *
 * Approving a version is the only thing that makes it effective. On approval:
 *   - the version becomes `approved` and gets approved_by / approval_date
 *   - the document's previous approved version flips to `superseded`, and the
 *     superseded version is recorded on the new version's
 *     `supersedes_version_id` (the audit chain QMS-07 built for)
 *   - the document points `current_version_id` at the newly approved version
 *     and its own status becomes `approved`
 *
 * Nothing here mutates an approved version's content: a correction is a new
 * version (QMS-07), never an edit.
 */
class DocumentControlWorkflow
{
    /** Roles that may approve a final version (spec §10.2). */
    public const APPROVER_ROLES = ['super_admin', 'admin', 'qms_manager'];

    /** Roles that may review (approve means "reviewer signs off"). */
    public const REVIEWER_ROLES = self::APPROVER_ROLES;

    /**
     * Preparer submits a draft version for review.
     *
     * Only a draft may be submitted; submitting an in-review version is a
     * double-click, and submitting an approved/ superseded one is nonsense.
     */
    public function submitForReview(ControlledDocumentVersion $version, User $actor): ControlledDocumentVersion
    {
        $this->assertStatus($version, [ControlledDocumentVersion::STATUS_DRAFT], 'submit for review');

        return DB::transaction(function () use ($version, $actor) {
            $version->update([
                'status' => ControlledDocumentVersion::STATUS_IN_REVIEW,
            ]);

            $document = $version->document;
            $document?->update(['status' => ControlledDocument::STATUS_IN_REVIEW]);

            return $version->fresh();
        });
    }

    /**
     * A reviewer returns the version to the preparer with a note.
     *
     * The note is mandatory: "returned" with no reason leaves the preparer
     * guessing, and the note is the record of why the document was not fit.
     */
    public function returnToPreparer(ControlledDocumentVersion $version, User $actor, string $note): ControlledDocumentVersion
    {
        $this->assertCanReview($actor);
        $this->assertStatus($version, [ControlledDocumentVersion::STATUS_IN_REVIEW], 'return');

        $note = trim($note);

        if ($note === '') {
            throw new RuntimeException('A note is required when returning a document.');
        }

        return DB::transaction(function () use ($version, $note) {
            $version->update([
                'status' => ControlledDocumentVersion::STATUS_DRAFT,
            ]);

            // Keep the reason on the record. The note is appended to the
            // revision summary rather than stored in a new column: the summary
            // is what the history timeline already renders, and a returned
            // document's "why" belongs next to what it says.
            $version->update([
                'revision_summary' => $this->appendNote($version->revision_summary, 'Returned for revision: ' . $note),
            ]);

            $version->document?->update(['status' => ControlledDocument::STATUS_DRAFT]);

            return $version->fresh();
        });
    }

    /**
     * A reviewer approves the version. This is the transition that makes a
     * document effective, so it carries the most rules.
     */
    public function approve(ControlledDocumentVersion $version, User $actor): ControlledDocumentVersion
    {
        $this->assertCanReview($actor);
        $this->assertStatus($version, [ControlledDocumentVersion::STATUS_IN_REVIEW], 'approve');

        return DB::transaction(function () use ($version, $actor) {
            $document = $version->document;

            // The previously effective version, if any, is what this approval
            // supersedes. Read it before we move the pointer.
            $previous = $document?->current_version_id
                ? ControlledDocumentVersion::withoutGlobalScopes()->find($document->current_version_id)
                : null;

            // A version cannot approve itself as its own predecessor, and we do
            // not re-supersede an already superseded version.
            if ($previous && $previous->id === $version->id) {
                $previous = null;
            }

            $version->update([
                'status'               => ControlledDocumentVersion::STATUS_APPROVED,
                'reviewed_by'          => $version->reviewed_by ?? $actor->id,
                'approved_by'          => $actor->id,
                'approval_date'        => now()->toDateString(),
                'effective_date'       => $version->effective_date ?? now()->toDateString(),
                'supersedes_version_id' => $previous?->id,
            ]);

            if ($previous) {
                $previous->update(['status' => ControlledDocumentVersion::STATUS_SUPERSEDED]);
            }

            $document?->update([
                'status'             => ControlledDocument::STATUS_APPROVED,
                'current_version_id' => $version->id,
            ]);

            return $version->fresh();
        });
    }

    /**
     * Create the next draft version of an approved document.
     *
     * This is how a correction happens: the approved version is untouched, a
     * new draft at the next version label is opened alongside it, and the
     * document stays `approved` on its current version until the new one is
     * approved in turn.
     */
    public function openNextVersion(
        ControlledDocument $document,
        User $actor,
        string $bump = ControlledDocumentVersion::BUMP_MINOR,
        ?string $revisionSummary = null
    ): ControlledDocumentVersion {
        $this->assertCanManage($actor);

        return DB::transaction(function () use ($document, $actor, $bump, $revisionSummary) {
            $version = ControlledDocumentVersion::create([
                'controlled_document_id' => $document->id,
                'agency_id'              => $document->agency_id,
                'version_no'             => ControlledDocumentVersion::nextVersionNoFor($document, $bump),
                'revision_summary'       => $revisionSummary,
                'prepared_by'            => $actor->id,
                'status'                 => ControlledDocumentVersion::STATUS_DRAFT,
            ]);

            // A new draft does not demote an approved document: the approved
            // version stays effective (and `current_version_id` untouched)
            // until the draft is approved in its turn. A document still in
            // draft just keeps tracking the newest draft.
            if ($document->status !== ControlledDocument::STATUS_APPROVED) {
                $document->update([
                    'status'             => ControlledDocument::STATUS_DRAFT,
                    'current_version_id' => $version->id,
                ]);
            }

            return $version->fresh();
        });
    }

    /** True when this user may approve / return versions. */
    public function userCanReview(?User $user): bool
    {
        return $user !== null
            && in_array((string) $user->user_type, self::REVIEWER_ROLES, true);
    }

    /** True when this user may manage documents at all. */
    public function userCanManage(?User $user): bool
    {
        return $user !== null
            && in_array((string) $user->user_type, self::APPROVER_ROLES, true);
    }

    private function assertCanReview(User $actor): void
    {
        if (! $this->userCanReview($actor)) {
            throw new RuntimeException('You are not allowed to approve or return controlled documents.');
        }
    }

    private function assertCanManage(User $actor): void
    {
        if (! $this->userCanManage($actor)) {
            throw new RuntimeException('You are not allowed to manage controlled documents.');
        }
    }

    /** @param string[] $allowed */
    private function assertStatus(ControlledDocumentVersion $version, array $allowed, string $action): void
    {
        if (! in_array((string) $version->status, $allowed, true)) {
            throw new RuntimeException(sprintf(
                'Cannot %s: version %s is %s.',
                $action,
                $version->version_no,
                $version->status_label
            ));
        }
    }

    /** Append a note to the revision summary without losing the original text. */
    private function appendNote(?string $summary, string $note): string
    {
        $summary = trim((string) $summary);

        return $summary === '' ? $note : $summary . "\n\n" . $note;
    }
}
