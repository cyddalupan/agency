<?php

namespace App\Models;

use App\Models\Traits\ImmutableAuditRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Application activity log (login, role changes, exports, applicant status
 * history).
 *
 * QMS-05: append-only. Rows are created freely but can no longer be updated or
 * deleted through the model; see ImmutableAuditRecord for why and for the
 * deliberate DB-level escape hatch.
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Tenant scope. The Sensitive Actions screen must never show another
     * agency's rows. agency_id is nullable on this table (see the 2026-06-18
     * migration), and a null row belongs to nobody - so it only shows for an
     * actor with no agency of their own.
     */
    public function scopeForAgency($query, ?int $agencyId)
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
