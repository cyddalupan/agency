<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Card 6b0383d516e52b9c418a9136 — the god-plane audit trail.
 *
 * One read-only screen, two tables: `audit_events` (the control-plane trail)
 * and `activity_logs` (the sensitive-actions log). This class owns the query
 * and the filter semantics of both, exactly as `GodReceivableService` owns
 * "collected". The screen, the sensitive tab and both CSV exports read the
 * same `filtered()` / `sensitiveFiltered()`, so their counts reconcile by
 * construction (§ acceptance 4 and 10).
 *
 * Load-bearing rules, each one easy to undo by accident:
 *
 *   1. NO `Tenant::bypass()`. `TenantScope` already drops the filter for a
 *      `super_admin`, and `godmode.only` only ever admits one. An explicit
 *      bypass would write a `tenant_bypass` audit row per page view — the
 *      audit trail auditing itself (acceptance 15).
 *
 *   2. No writes. Every table in `messages`/`tables` is append-only by model
 *      guard (`ImmutableAuditRecord`); this screen adds no edit, delete,
 *      retention or purge control (acceptance 13).
 *
 *   3. `agency_id NULL` rows are VISIBLE, never blank. They are the god
 *      plane's own rows: god logins, and — the reason this screen exists —
 *      the audit trail of an agency that `agencies:remove-empty` hard-deleted,
 *      re-homed to NULL so the delete left evidence behind. They render as
 *      `No agency` (acceptance 2, 9).
 *
 *   4. A NULL `actor_user_id` renders as `System`, not as a blank cell: 786
 *      live events have no actor, and the only clue to which command wrote
 *      one is `user_agent` (acceptance 6).
 *
 *   5. `q` is a LIKE with `\`, `%` and `_` escaped. `?q=%` must match nothing,
 *      not everything (acceptance 8).
 */
class GodAuditService
{
    /** Rows per page. Anything else — 37 included — is 25, never a 422. */
    public const PER_PAGE_OPTIONS = [25, 50, 100];

    public const DEFAULT_PER_PAGE = 25;

    /** What a NULL `agency_id` renders as. Never blank, never a crash. */
    public const NO_AGENCY = 'No agency';

    /** What a NULL actor renders as, in the dropdown and in the Actor cell. */
    public const SYSTEM_ACTOR = 'System';

    /** The `?agency=` value meaning "rows with `agency_id IS NULL`". */
    public const AGENCY_NONE = 'none';

    /** The `?actor=` value meaning "rows with `actor_user_id IS NULL`". */
    public const ACTOR_NONE = 'none';

    /** Default window when no `from`/`to` is given: the last 30 days. */
    public const DEFAULT_DAYS = 30;

    /**
     * Sentences appended in the detail panel for a row that has no agency AND
     * was written by the hard-delete command. Kept here, not in the view, so
     * the export and the screen say the same thing.
     */
    public const DELETED_AGENCY_NOTE = 'This agency no longer exists. The audit trail of a deleted agency is re-homed to agency_id NULL rather than cascaded away, so it stays readable here.';

    // ── filters ──────────────────────────────────────────────────────

    /**
     * Request query -> validated filter array. Everything the user typed is
     * re-checked here; the view never trusts the string it rendered.
     *
     * Inverted dates are SWAPPED, never rejected: the operator typed a window
     * backwards, they did not ask for a 422.
     *
     * @return array{from: string, to: string, actor: ?string, agency: ?string, action: ?string, subject_type: ?string, q: string, per: int}
     */
    public function filters(array $query): array
    {
        [$from, $to] = $this->dateBounds($query['from'] ?? null, $query['to'] ?? null);

        $actor = trim((string) ($query['actor'] ?? ''));
        $agency = trim((string) ($query['agency'] ?? ''));
        $action = trim((string) ($query['action'] ?? ''));
        $subject = trim((string) ($query['subject_type'] ?? ''));

        return [
            'from'         => $from,
            'to'           => $to,
            // A named actor is a user id, or the literal `none` for System.
            // Anything else is "all actors" — a bogus id narrows to nothing,
            // which is honest, but garbage like `abc` must not become id 0.
            'actor'        => $this->idOrNone($actor),
            'agency'       => $this->idOrNone($agency),
            'action'       => $action !== '' ? mb_substr($action, 0, 64) : null,
            'subject_type' => $subject !== '' && class_exists($subject) ? $subject : null,
            'q'            => mb_substr(trim((string) ($query['q'] ?? '')), 0, 120),
            'per'          => $this->perPage($query['per'] ?? null),
        ];
    }

    /**
     * The same filters with the record-type narrowing removed. The sensitive
     * tab offers no type filter, and its export must narrow exactly like its
     * screen, so the controller clears it for both.
     */
    public function withoutSubjectType(array $filters): array
    {
        $filters['subject_type'] = null;

        return $filters;
    }

    /** `none` -> `none`; a positive integer -> its digits; anything else -> null. */
    private function idOrNone(string $value): ?string
    {
        if ($value === self::AGENCY_NONE) {
            return self::AGENCY_NONE;
        }

        return ctype_digit($value) && (int) $value > 0 ? (string) (int) $value : null;
    }

    /**
     * `from`/`to` in, a sane `[start, end]` day pair out. Defaults: the last
     * 30 days. Unparseable dates fall back to the default, an inverted pair is
     * swapped.
     *
     * @return array{0: string, 1: string}
     */
    public function dateBounds($from, $to): array
    {
        $fromDate = $this->parseDate($from);
        $toDate = $this->parseDate($to);

        if ($fromDate === null && $toDate === null) {
            $toDate = now();
            $fromDate = $toDate->copy()->subDays(self::DEFAULT_DAYS - 1);
        } elseif ($fromDate === null) {
            $fromDate = $toDate->copy()->subDays(self::DEFAULT_DAYS - 1);
        } elseif ($toDate === null) {
            $toDate = $fromDate->copy()->addDays(self::DEFAULT_DAYS - 1);
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate->toDateString(), $toDate->toDateString()];
    }

    private function parseDate($value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function perPage($requested): int
    {
        $per = (int) $requested;

        return in_array($per, self::PER_PAGE_OPTIONS, true) ? $per : self::DEFAULT_PER_PAGE;
    }

    // ── the one builder: audit_events ────────────────────────────────

    /**
     * The filtered `audit_events` builder. Screen, count and export all use
     * it, so "matching" on screen is the number of CSV data rows.
     *
     * No `withoutGlobalScopes()`: the scope is `TenantScope`, which is already
     * open for a super_admin. `agency_id NULL` rows are NOT excluded — they
     * are the control-plane rows, and `?agency=none` is the only way to narrow
     * to them deliberately.
     */
    public function filtered(array $filters): Builder
    {
        return AuditEvent::query()
            ->when($filters['from'] ?? null, fn (Builder $q, $from) => $q->where('audit_events.created_at', '>=', $from.' 00:00:00'))
            ->when($filters['to'] ?? null, fn (Builder $q, $to) => $q->where('audit_events.created_at', '<=', $to.' 23:59:59'))
            ->when($filters['actor'] ?? null, fn (Builder $q, $actor) => $actor === self::ACTOR_NONE
                ? $q->whereNull('audit_events.actor_user_id')
                : $q->where('audit_events.actor_user_id', (int) $actor))
            ->when($filters['agency'] ?? null, fn (Builder $q, $agency) => $agency === self::AGENCY_NONE
                ? $q->whereNull('audit_events.agency_id')
                : $q->where('audit_events.agency_id', (int) $agency))
            ->when($filters['action'] ?? null, fn (Builder $q, $action) => $q->where('audit_events.action', $action))
            ->when($filters['subject_type'] ?? null, fn (Builder $q, $subject) => $q->where('audit_events.subject_type', $subject))
            ->when($filters['q'] ?? null, fn (Builder $q, $term) => $q->where('audit_events.subject_label', 'like', '%'.$this->escapeLike($term).'%'));
    }

    /** The "matching" number on the screen and the data-row count in the CSV. */
    public function count(array $filters): int
    {
        return $this->filtered($filters)->count();
    }

    /** Unfiltered total, for the "of N events" line. */
    public function totalCount(): int
    {
        return AuditEvent::query()->count();
    }

    /** How many events have no agency at all. The badge on the screen. */
    public function noAgencyCount(): int
    {
        return AuditEvent::query()->whereNull('agency_id')->count();
    }

    /** Rows per page, newest first, `?page=` preserved across filter links. */
    public function page(array $filters): LengthAwarePaginator
    {
        return $this->filtered($filters)
            ->with('actor')
            ->orderByDesc('audit_events.created_at')
            ->orderByDesc('audit_events.id')
            ->paginate($filters['per'] ?? self::DEFAULT_PER_PAGE)
            ->withQueryString();
    }

    // ── the one builder: activity_logs ───────────────────────────────

    /**
     * The filtered `activity_logs` builder for the sensitive tab.
     *
     * `ActivityLog` deliberately has no `TenantScope` (see the model): its
     * rows are read through `forAgency()`, which a NULL-agency actor resolves
     * to `whereNull`. On the god plane the operator sees all of it, so no
     * agency gate is applied at all — but the agency FILTER still works, with
     * `none` meaning the 416 god-plane rows.
     */
    public function sensitiveFiltered(array $filters): Builder
    {
        return ActivityLog::query()
            ->when($filters['from'] ?? null, fn (Builder $q, $from) => $q->where('activity_logs.created_at', '>=', $from.' 00:00:00'))
            ->when($filters['to'] ?? null, fn (Builder $q, $to) => $q->where('activity_logs.created_at', '<=', $to.' 23:59:59'))
            ->when($filters['actor'] ?? null, fn (Builder $q, $actor) => $actor === self::ACTOR_NONE
                ? $q->whereNull('activity_logs.user_id')
                : $q->where('activity_logs.user_id', (int) $actor))
            ->when($filters['agency'] ?? null, fn (Builder $q, $agency) => $agency === self::AGENCY_NONE
                ? $q->whereNull('activity_logs.agency_id')
                : $q->where('activity_logs.agency_id', (int) $agency))
            ->when($filters['action'] ?? null, fn (Builder $q, $action) => $q->where('activity_logs.action', $action))
            ->when($filters['subject_type'] ?? null, fn (Builder $q, $subject) => $q->where('activity_logs.subject_type', $subject))
            ->when($filters['q'] ?? null, fn (Builder $q, $term) => $q->where('activity_logs.description', 'like', '%'.$this->escapeLike($term).'%'));
    }

    public function sensitiveCount(array $filters): int
    {
        return $this->sensitiveFiltered($filters)->count();
    }

    public function sensitiveTotalCount(): int
    {
        return ActivityLog::query()->count();
    }

    public function sensitivePage(array $filters): LengthAwarePaginator
    {
        return $this->sensitiveFiltered($filters)
            ->with('user')
            ->orderByDesc('activity_logs.created_at')
            ->orderByDesc('activity_logs.id')
            ->paginate($filters['per'] ?? self::DEFAULT_PER_PAGE)
            ->withQueryString();
    }

    // ── dropdowns ────────────────────────────────────────────────────

    /**
     * Agency id => name, unscoped. `Agency` uses `HasTenant`, and on the god
     * plane the scope is already open for a super_admin — but a bound tenant
     * or a fail-closed scope would silently drop every agency from the
     * dropdown, so the filter is removed explicitly on this one query (same
     * pattern as `GodModeGuard::user()`), never through `Tenant::bypass()`.
     *
     * @return array<int, string>
     */
    public function agencies(): array
    {
        return Agency::withoutGlobalScope(TenantScope::class)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * User id => display name for the actor filter. Unscoped for the same
     * reason as `agencies()`, which is exactly what `GodModeGuard` does.
     *
     * @return array<int, string>
     */
    public function actors(): array
    {
        return User::withoutGlobalScope(TenantScope::class)
            ->orderBy('surname')
            ->orderBy('name')
            ->get(['id', 'name', 'surname', 'email', 'username'])
            ->mapWithKeys(fn (User $u) => [$u->id => $this->actorName($u)])
            ->all();
    }

    /** Distinct actions present in `audit_events`, with their labels. */
    public function actions(): array
    {
        return AuditEvent::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->mapWithKeys(fn (string $a) => [$a => AuditEvent::ACTION_LABELS[$a] ?? ucfirst(str_replace('_', ' ', $a))])
            ->all();
    }

    /** Distinct subject types across both tables, as class names. */
    public function subjectTypes(): array
    {
        $fromEvents = AuditEvent::query()->select('subject_type')->distinct()->pluck('subject_type')->all();
        $fromLogs = ActivityLog::query()->whereNotNull('subject_type')->select('subject_type')->distinct()->pluck('subject_type')->all();

        $types = array_values(array_unique(array_filter(array_merge($fromEvents, $fromLogs))));
        sort($types);

        return $types;
    }

    /** Distinct `activity_logs.action` slugs, with readable labels. */
    public function sensitiveActions(): array
    {
        return ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->mapWithKeys(fn (string $a) => [$a => (new ActivityLog(['action' => $a]))->action_label])
            ->all();
    }

    // ── presentation ─────────────────────────────────────────────────

    /**
     * The actor cell: a name, or `System` when the row has no actor. Never a
     * blank — 786 events have no actor and "nobody" is information.
     */
    public function actorName(?User $user): string
    {
        if ($user === null) {
            return self::SYSTEM_ACTOR;
        }

        $name = trim(implode(' ', array_filter([$user->surname, $user->name])));

        return $name !== '' ? $name : (string) ($user->username ?: $user->email ?: '#'.$user->id);
    }

    /**
     * The agency CELL: the name when the agency exists, `No agency` for NULL,
     * `#<id>` only when an id points at a row the unscoped lookup could not
     * find (which, per the card, is zero rows today — but a cell must never be
     * blank or a stack trace).
     *
     * @param  array<int, string>|null  $names  pre-fetched id => name map
     */
    public function agencyName(?int $agencyId, ?array $names = null): string
    {
        if ($agencyId === null) {
            return self::NO_AGENCY;
        }

        $names ??= $this->agencies();

        return $names[$agencyId] ?? '#'.$agencyId;
    }

    /** `App\Models\Agency` -> `Agency`; null stays null. */
    public function subjectLabel(?string $subjectType): string
    {
        return $subjectType === null || $subjectType === '' ? '—' : class_basename($subjectType);
    }

    /**
     * Escape LIKE metacharacters so `?q=%` matches a literal percent sign
     * (i.e. nothing), not every row. `\`, `%` and `_` are backslash-escaped,
     * which is the default escape character for `LIKE` in both MySQL and
     * SQLite, so the same bound value is correct on the live database and in
     * the suite's `:memory:` database.
     */
    public function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }

    /**
     * True when this event is the recorded hard-delete of an agency: no agency
     * left, action `deleted`, subject is an Agency. That combination is what
     * `agencies:remove-empty` writes, and it is the row the detail panel
     * explains in words.
     */
    public function isDeletedAgencyEvent(object $event): bool
    {
        return $event->agency_id === null
            && $event->action === AuditEvent::ACTION_DELETED
            && ltrim((string) $event->subject_type, '\\') === ltrim(Agency::class, '\\');
    }
}
