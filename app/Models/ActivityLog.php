<?php

namespace App\Models;

use App\Models\Traits\ImmutableAuditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Application activity log (login, role changes, exports, applicant status
 * history).
 *
 * QMS-05: append-only. Rows are created freely but can no longer be updated or
 * deleted through the model; see ImmutableAuditRecord for why and for the
 * deliberate DB-level escape hatch.
 *
 * TENANT — deliberate exception, card 6b008b8d3ca32bfcb58ce970.
 *
 * `agency_id` on this table is nullable (migration 2026_06_18_124607) and 262
 * of 3,650 live rows carry NULL: failed logins for unknown addresses, requests
 * on a host that named no agency, and rows written before the column was
 * populated. Those rows are legitimate history and belong to NO agency, so a
 * blanket `use HasTenant` would either silently hide them from every screen or
 * make a TenantScope-shaped `where agency_id = ?` hide them by accident while
 * looking like it worked.
 *
 * Option chosen (card §B, second alternative): this model does NOT join the
 * shared scope. Reads are gated at the query layer through `scopeForAgency()`
 * below, which a null-agency actor resolves to `whereNull('agency_id')` — the
 * rows are then visible to exactly the actor who owns them (none) and never to
 * a tenant. Every application read (AuditTrailController sensitive-actions
 * screen + CSV export, the applicant status history, the AI daily rate limit)
 * already calls `forAgency()`. Writes are unchanged: SensitiveActionLogger
 * attributes each row to the current user's agency.
 *
 * The alternative — backfilling the 262 — was rejected because the failed
 * logins do not record which agency the address was meant for, so any backfill
 * would be a guess written into an audit trail. An audit trail that invents
 * provenance is worse than one that says "unattributed".
 */
class ActivityLog extends Model
{
    use HasFactory;
    use ImmutableAuditRecord;

    protected $fillable = [
        'agency_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Query-layer tenant gate. The Sensitive Actions screen must never show
     * another agency's rows. agency_id is nullable on this table (see the
     * 2026-06-18 migration), and a null row belongs to nobody - so it only
     * shows for an actor with no agency of their own.
     *
     * This is a mandatory filter, not a convenience: see the class docblock
     * for why the model is deliberately outside `TenantScope`.
     */
    public function scopeForAgency(Builder $query, ?int $agencyId): Builder
    {
        return $agencyId === null
            ? $query->whereNull('agency_id')
            : $query->where('agency_id', $agencyId);
    }

    /**
     * Human label for the action. action values here are free-form slugs
     * (`user_password_reset_by_admin`, `bulk_applicant_import`), not the fixed
     * six-value audit_events vocabulary, so this renders a readable sentence
     * fragment rather than looking up a fixed map.
     */
    public function getActionLabelAttribute(): string
    {
        $raw = (string) $this->action;

        if ($raw === '') {
            return '—';
        }

        return ucfirst(str_replace('_', ' ', $raw));
    }
}
