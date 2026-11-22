<?php

namespace App\Support;

use App\Models\AuditEvent;
use Illuminate\Support\Facades\Log;

/**
 * The one explicit, recorded way to run tenant-scoped queries without the
 * tenant filter (card 6b008b8dc9020955b2cc16e1).
 *
 * TenantScope fails closed on the web, so a controller or command that
 * genuinely needs to read across agencies (an admin report, a reconciliation,
 * a one-off repair) must say so. `Tenant::bypass()` does three things:
 *
 *   1. disables the scope for the duration of the callable,
 *   2. writes exactly ONE `audit_events` row naming the actor, the callable's
 *      location and the host, so the bypass is visible after the fact. Where
 *      no one is authenticated yet (login lookups, registration brokers) there
 *      is no actor id to name, so use bypassFor() with the attempted
 *      identifier (email, subdomain) — the row then records what was tried
 *      instead of naming nobody. actor_user_id stays NULL; no id is invented.
 *   3. never suppresses the access silently — if the audit write fails, the
 *      callable still runs but the failure is reported.
 *
 * It deliberately does NOT need a container flag, a magic agency id, or a
 * static boolean of its own: the counter lives here and TenantScope reads it
 * via isBypassing(). Nested bypasses share one flag level and produce one
 * audit row per `bypass()` call, which is the intent — one row per decision.
 *
 * Prefer `Model::withoutGlobalScope(TenantScope::class)` for a single known
 * query that is already deliberate in code (that pattern is used in
 * AuditsChanges, ControlledDocumentVersion, Receivable, JobSlotService,
 * DocumentControlWorkflow, ReportController and Api\CaseController). Use
 * bypass() when you want the decision recorded.
 */
final class Tenant
{
    private static int $depth = 0;

    /** True while one or more bypass() calls are on the stack. */
    public static function isBypassing(): bool
    {
        return self::$depth > 0;
    }

    /**
     * Run $fn with TenantScope disabled, writing one audit_events row.
     *
     * @template T
     * @param  callable():T  $fn
     * @return T
     */
    public static function bypass(callable $fn, ?string $reason = null, array $context = [])
    {
        return self::bypassFor($fn, $reason, $context);
    }

    /**
     * Run $fn with TenantScope disabled and record WHAT WAS ATTEMPTED in the
     * audit row.
     *
     * For a bypass that happens before anyone is authenticated — a login
     * lookup, a broker call — there is no actor_user_id to write, and leaving
     * only a caller location says "the actor is logged" while the row names
     * nobody. The attempted identifier (the email being tried, the subdomain
     * asked for) is knowledge we DO have, so it goes in subject_label and
     * `after`, and actor_user_id stays NULL — we do not invent a user id.
     *
     * @template T
     * @param  callable():T  $fn
     * @return T
     */
    public static function bypassFor(callable $fn, ?string $reason = null, array $context = [])
    {
        self::$depth++;

        try {
            return $fn();
        } finally {
            self::$depth--;
            self::record($reason, $context);
        }
    }

    /**
     * Record the bypass. Never throws: an audit failure must not break the
     * caller (same rule as AuditsChanges::writeAuditEvent()).
     *
     * $context is whatever the caller knows about the attempt (an attempted
     * email, a requested subdomain). It is merged into `after` and summarised
     * in subject_label so an unauthenticated bypass still says who/what was
     * tried instead of naming nobody.
     */
    private static function record(?string $reason, array $context = []): void
    {
        try {
            // agency_id is NOT NULL on audit_events, and AuditsChanges skips the
            // write when there is no agency to attribute to (see :116-118).
            // When the host resolved no agency, at least try the actor's own.
            $agencyId = self::resolveAuditAgencyId();

            if ($agencyId === null) {
                // Card 6b008b8dc9020955b2cc16e1, defect C: this used to return
                // silently and the bypass left NO trace at all (a control-plane
                // registration's bypass wrote nothing, audit max unchanged).
                // audit_events cannot hold the row — agency_id is NOT NULL and
                // inventing an id would attribute one agency's access to
                // another — so the outcome goes to the application log at
                // warning level, with what was attempted.
                self::recordUattributableBypass($reason, $context);

                return;
            }

            $caller = self::callerLocation();
            $actorId = auth()->id();

            // A label that always says what was attempted, even when no one is
            // logged in yet: "reason (attempted X: y)", or "reason" alone.
            $attempt = self::attemptSummary($context);
            $label = $reason ?: $caller;
            if ($attempt !== null) {
                $label = mb_substr($label . ' [' . $attempt . ']', 0, 255);
            }

            AuditEvent::withoutGlobalScopes()->create([
                'agency_id'     => $agencyId,
                'actor_user_id' => $actorId,
                'action'        => AuditEvent::ACTION_TENANT_BYPASS,
                'subject_type'  => 'tenant_bypass',
                'subject_id'    => 0,
                'subject_label' => $label,
                'before'        => null,
                'after'         => array_merge([
                    'host'      => request()?->getHost(),
                    'url'       => request()?->fullUrl(),
                    'caller'    => $caller,
                    'reason'    => $reason,
                    // Null actor = nobody authenticated yet; the attempt below
                    // says what the bypass was for.
                    'actor_user_id' => $actorId,
                ], array_filter($context, static fn ($v) => $v !== null && $v !== '')),
                'ip_address'    => request()?->ip(),
                'user_agent'    => request()?->userAgent()
                    ? mb_substr(request()->userAgent(), 0, 512)
                    : null,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * A bypass with no agency to attribute it to: audit_events would need a
     * NOT NULL agency_id and there is none. Log it as an explicit outcome
     * instead of dropping it — the fact that a scope-free read happened must
     * survive somewhere. Never throws.
     */
    private static function recordUattributableBypass(?string $reason, array $context): void
    {
        try {
            Log::warning('tenant bypass without an agency could not be written to audit_events (agency_id is NOT NULL)', [
                'reason'     => $reason,
                'caller'     => self::callerLocation(),
                'host'       => request()?->getHost(),
                'url'        => request()?->fullUrl(),
                'actor_user_id' => auth()->id(),
                'identifier' => self::attemptSummary($context),
                'context'    => array_filter($context, static fn ($v) => $v !== null && $v !== ''),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * "attempted email: a@b.test" from the caller's context, or null when
     * nothing identifying was supplied.
     */
    private static function attemptSummary(array $context): ?string
    {
        $parts = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value) && (string) $value !== '') {
                $parts[] = str_replace('_', ' ', (string) $key) . ': ' . $value;
            }
        }

        return $parts === [] ? null : implode(', ', $parts);
    }

    private static function resolveAuditAgencyId(): ?int
    {
        if (app()->has('tenant_agency') && ($agency = app('tenant_agency'))) {
            return (int) $agency->id;
        }

        if ($sessionId = session('tenant_agency_id')) {
            return (int) $sessionId;
        }

        $user = auth()->user();

        return $user?->agency_id ? (int) $user->agency_id : null;
    }

    /** "App\Foo::bar (file:line)" — who decided to bypass, for the audit row. */
    private static function callerLocation(): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12) as $frame) {
            $class = $frame['class'] ?? '';
            if ($class === self::class || $class === 'App\Models\Scopes\TenantScope') {
                continue;
            }

            $fn = ($class !== '' ? $class . ($frame['type'] ?? '::') : '') . ($frame['function'] ?? '?');
            $file = isset($frame['file'])
                ? basename(dirname($frame['file'])) . '/' . basename($frame['file']) . ':' . ($frame['line'] ?? '?')
                : '?';

            return "{$fn} ({$file})";
        }

        return 'unknown caller';
    }
}
