<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * QMS-03 (card 6afa0da0a3bf2c5d4e09e0cf): read-only Audit Trail screen.
 *
 * Spec §27 places this under ADMINISTRATION. It answers the auditor's first
 * question — who changed what, and when — from the `audit_events` spine that
 * QMS-01 created and QMS-02 populates.
 *
 * Read-only by design: there is no create, edit or delete route here, and the
 * underlying table has no update/delete path through the application.
 */
class AuditTrailController extends Controller
{
    /** Roles allowed to read the audit trail. */
    private const ALLOWED_ROLES = ['super_admin', 'admin'];

    /**
     * Agency-scope list with filters: date range, actor, action, subject type
     * and a free-text match on the subject label.
     */
    public function index(Request $request): View
    {
        $this->authorise($request);

        $filters = $request->validate([
            'from'         => ['nullable', 'date'],
            'to'           => ['nullable', 'date'],
            'actor'        => ['nullable', 'integer'],
            'action'       => ['nullable', 'string', 'max:32'],
            'subject_type' => ['nullable', 'string', 'max:191'],
            'q'            => ['nullable', 'string', 'max:120'],
        ]);

        $events = AuditEvent::query()
            ->with('actor')
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['actor'] ?? null, fn ($q, $actor) => $q->where('actor_user_id', $actor))
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($filters['subject_type'] ?? null, fn ($q, $type) => $q->where('subject_type', $type))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('subject_label', 'like', '%' . $term . '%'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('audit_trail.index', [
            'events'          => $events,
            'filters'         => $filters,
            'actions'         => AuditEvent::ACTION_LABELS,
            'subjectTypes'    => $this->subjectTypeOptions(),
            'actors'          => $this->actorOptions(),
            'totalCount'      => AuditEvent::query()->count(),
            'filteredCount'   => $events->total(),
            'activeFilterCnt' => count(array_filter($filters, fn ($v) => $v !== null && $v !== '')),
        ]);
    }

    /** Distinct subject types present in this agency's trail, as short names. */
    private function subjectTypeOptions(): array
    {
        return AuditEvent::query()
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->mapWithKeys(fn (string $type) => [$type => class_basename($type)])
            ->all();
    }

    /** Users who appear as an actor, for the actor dropdown. */
    private function actorOptions(): array
    {
        $ids = AuditEvent::query()
            ->whereNotNull('actor_user_id')
            ->distinct()
            ->pluck('actor_user_id');

        if ($ids->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('surname')
            ->get(['id', 'name', 'middle_name', 'surname'])
            ->mapWithKeys(fn (User $u) => [$u->id => trim(implode(', ', array_filter([$u->surname, $u->name]))) ?: ('User #' . $u->id)])
            ->all();
    }

    /**
     * Only super_admin and admin may read the trail. Everyone else gets 403 —
     * not a redirect, so a probe reading the status code sees the refusal.
     */
    private function authorise(Request $request): void
    {
        abort_unless(
            in_array((string) $request->user()?->user_type, self::ALLOWED_ROLES, true),
            403,
            'You are not allowed to view the audit trail.'
        );
    }
}
