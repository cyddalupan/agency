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

        $events = $this->filteredQuery($filters)
            ->with('actor')
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

    /**
     * QMS-04 (card 6afa0da181cbe9341f046f60): CSV export of the trail.
     *
     * Honours exactly the filters the screen applies, so the row count of the
     * file equals the on-screen count. Same role gate as the screen.
     */
    public function export(Request $request)
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

        $query = $this->filteredQuery($filters);

        $headers = [
            'Timestamp', 'Actor', 'Action', 'Record type', 'Record ID',
            'Reference', 'Changed fields', 'Before', 'After', 'IP address',
        ];

        $filename = 'audit_trail_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $headers) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel reads the file as UTF-8.
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headers);

            // Stream in chunks: the trail can be large, and a single get()
            // would hydrate the whole range into memory.
            $query->with('actor')->chunk(500, function ($events) use ($out) {
                foreach ($events as $event) {
                    $before = $event->before ?? [];
                    $after  = $event->after ?? [];

                    // A field "changed" when it is present on either side with a
                    // different value — array_diff_key alone misses a key that
                    // exists in both, which is the common case (status).
                    $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
                    $changed = array_values(array_filter($keys, function ($key) use ($before, $after) {
                        return ($before[$key] ?? null) !== ($after[$key] ?? null);
                    }));
                    sort($changed);

                    fputcsv($out, [
                        $event->created_at?->format('Y-m-d H:i:s') ?? '',
                        $event->actor
                            ? trim(implode(', ', array_filter([$event->actor->surname, $event->actor->name])))
                            : 'system',
                        $event->action_label,
                        class_basename($event->subject_type),
                        $event->subject_id,
                        $event->subject_label ?? '',
                        implode(', ', $changed),
                        $this->csvJson($before),
                        $this->csvJson($after),
                        $event->ip_address ?? '',
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /** Compact JSON for a diff payload, safe to sit in one CSV cell. */
    private function csvJson(array $payload): string
    {
        return $payload === [] ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** The one filter definition shared by the screen and the export. */
    private function filteredQuery(array $filters)
    {
        return AuditEvent::query()
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['actor'] ?? null, fn ($q, $actor) => $q->where('actor_user_id', $actor))
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($filters['subject_type'] ?? null, fn ($q, $type) => $q->where('subject_type', $type))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('subject_label', 'like', '%' . $term . '%'))
            ->orderByDesc('created_at')
            ->orderByDesc('id');
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
