<?php

declare(strict_types=1);

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Services\GodMode\GodAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Card 6b0383d516e52b9c418a9136 — the god-plane audit trail.
 *
 * Three GET routes, no writes. There is no store, update, destroy, bulk or
 * "mark reviewed" action here and there must never be one (§ Out of scope):
 * the trail is evidence, and QMS-05 (`ImmutableAuditRecord`) already forbids
 * amending a row at the model.
 *
 * ── The gate, and why `module.access` is NOT here ────────────────────────
 * `['auth:godmode', 'godmode.only']` is the whole entitlement. `module.access`
 * is deliberately absent:
 *
 *   - `EnsureModuleAccess` only runs inside the tenant stack
 *     (`Route::middleware(['auth:web','module.access'])` in routes/web.php);
 *     the `godmode` group sits outside it.
 *   - `ModuleAccess::moduleFor()` reads `auth()->user()` on the DEFAULT guard.
 *     On a god session that is nobody, so adding the middleware here would
 *     either no-op (if it tolerates anonymous) or guard the wrong actor.
 *   - `godmode.only` / `EnsureIsGodMode` already proves `super_admin` +
 *     `active` + a fresh session. That is the whole entitlement.
 *
 * Do not "fix" this by adding `module.access`: it would 403 the exact operator
 * this screen exists for (a `super_admin` whose `agency_id` is NULL), which is
 * consequence 1 in the card.
 *
 * No `Tenant::bypass()` either: `TenantScope` is already open for a
 * super_admin, and a bypass writes one `tenant_bypass` event per page view —
 * the trail recording itself (acceptance 15).
 */
class GodAuditController extends Controller
{
    public function __construct(private readonly GodAuditService $audit)
    {
    }

    /** The audit trail: `audit_events`, unscoped, read-only. */
    public function index(Request $request): View
    {
        return $this->render($request, sensitive: false);
    }

    /** The sensitive-actions tab: `activity_logs`, its own columns. */
    public function sensitive(Request $request): View
    {
        return $this->render($request, sensitive: true);
    }

    /**
     * CSV export of the current tab's filters (QMS-04 rule): the file honours
     * exactly what the screen shows, so its data-row count equals the
     * "matching" number. `tab=sensitive` exports the second table with its own
     * headers; anything else is the audit trail.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->audit->filters($request->query());

        if ($request->query('tab') === 'sensitive') {
            return $this->exportSensitive($this->audit->withoutSubjectType($filters));
        }

        $headers = [
            'Timestamp', 'Actor', 'Action', 'Record type', 'Record ID', 'Reference',
            'Agency', 'Changed fields', 'Before', 'After', 'IP address', 'User agent',
        ];

        return $this->stream(
            'audit_trail_'.now()->format('Ymd_His').'.csv',
            $headers,
            function (callable $row) use ($filters) {
                $this->audit->filtered($filters)
                    ->with('actor')
                    ->orderByDesc('audit_events.created_at')
                    ->orderByDesc('audit_events.id')
                    ->chunk(500, function ($events) use ($row) {
                        foreach ($events as $event) {
                            $before = $event->before ?? [];
                            $after = $event->after ?? [];
                            $changed = [];
                            foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
                                if (($before[$key] ?? null) !== ($after[$key] ?? null)) {
                                    $changed[] = $key;
                                }
                            }
                            sort($changed);

                            $row([
                                $event->created_at?->format('Y-m-d H:i:s') ?? '',
                                $this->audit->actorName($event->actor),
                                $event->action_label,
                                $this->audit->subjectLabel($event->subject_type),
                                $event->subject_id,
                                $event->subject_label ?? '',
                                $this->audit->agencyName($event->agency_id, $this->audit->agencies()),
                                implode(', ', $changed),
                                $this->csvJson($before),
                                $this->csvJson($after),
                                $event->ip_address ?? '',
                                $event->user_agent ?? '',
                            ]);
                        }
                    });
            }
        );
    }

    private function render(Request $request, bool $sensitive): View
    {
        $filters = $this->audit->filters($request->query());
        $agencyNames = $this->audit->agencies();

        if ($sensitive) {
            // The sensitive tab offers no record-type filter, so it must not
            // narrow by one either: a `subject_type` left in the URL from the
            // audit trail is dropped here, for the screen and its export alike.
            $filters = $this->audit->withoutSubjectType($filters);
        }

        return view($sensitive ? 'godmode.audit.sensitive' : 'godmode.audit.index', [
            'filters'       => $filters,
            'rows'          => $sensitive ? $this->audit->sensitivePage($filters) : $this->audit->page($filters),
            'matching'      => $sensitive ? $this->audit->sensitiveCount($filters) : $this->audit->count($filters),
            'total'         => $sensitive ? $this->audit->sensitiveTotalCount() : $this->audit->totalCount(),
            'noAgency'      => $this->audit->noAgencyCount(),
            // The selector's options and the row labels are the SAME map, so a
            // row can never say `#7` while the dropdown above says "Gulf".
            'agencies'      => $agencyNames,
            'agencyNames'   => $agencyNames,
            'actors'        => $this->audit->actors(),
            'actions'       => $sensitive ? $this->audit->sensitiveActions() : $this->audit->actions(),
            'subjectTypes'  => $this->audit->subjectTypes(),
            'perOptions'    => GodAuditService::PER_PAGE_OPTIONS,
            'noAgencyLabel' => GodAuditService::NO_AGENCY,
            'systemLabel'   => GodAuditService::SYSTEM_ACTOR,
            'agencyNoneValue' => GodAuditService::AGENCY_NONE,
            'actorNoneValue'  => GodAuditService::ACTOR_NONE,
            // One instance for the view's per-row label lookups; without it
            // every row would resolve the service from the container twice.
            'audit'           => $this->audit,
        ]);
    }

    private function exportSensitive(array $filters): StreamedResponse
    {
        $headers = [
            'Timestamp', 'Actor', 'Action', 'Description', 'Record type', 'Record ID',
            'Agency', 'IP', 'User agent',
        ];

        return $this->stream(
            'sensitive_actions_'.now()->format('Ymd_His').'.csv',
            $headers,
            function (callable $row) use ($filters) {
                $this->audit->sensitiveFiltered($filters)
                    ->with('user')
                    ->orderByDesc('activity_logs.created_at')
                    ->orderByDesc('activity_logs.id')
                    ->chunk(500, function ($logs) use ($row) {
                        foreach ($logs as $log) {
                            $meta = $log->metadata ?? [];

                            $row([
                                $log->created_at?->format('Y-m-d H:i:s') ?? '',
                                $this->audit->actorName($log->user),
                                $log->action,
                                (string) $log->description,
                                $this->audit->subjectLabel($log->subject_type),
                                $log->subject_id ?? '',
                                $this->audit->agencyName($log->agency_id, $this->audit->agencies()),
                                $meta['ip'] ?? '',
                                $meta['user_agent'] ?? '',
                            ]);
                        }
                    });
            }
        );
    }

    /**
     * Stream a CSV with a UTF-8 BOM (so Excel reads it as UTF-8) and a header
     * row, then let the caller push rows through the `$row` callable.
     */
    private function stream(string $filename, array $headers, callable $write): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $write) {
            $out = fopen('php://output', 'w');

            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headers);

            $write(function (array $cells) use ($out) {
                fputcsv($out, $cells);
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
}
