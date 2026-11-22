<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\ImmutableAuditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * QMS-01 (card 6afa0d9e19ea247279f0a1d8): one append-only audit event.
 *
 * Spec §26 requires that *every* material create / update / approve / archive
 * action produces an audit event, and that such events cannot be edited or
 * deleted through ordinary application functions.
 *
 * Writing is done by QMS-02 (the AuditsChanges trait). This model exists first
 * so the call sites have somewhere to write to.
 *
 * Deliberately not using the `updated_at` column: the table has no such column
 * and events are immutable.
 *
 * QMS-05: the immutability is now enforced in code, not just by convention —
 * `ImmutableAuditRecord` makes save()-on-existing, delete() and forceDelete()
 * throw RuntimeException. Creating is still allowed; only amending is not.
 */
class AuditEvent extends Model
{
    use HasTenant;
    use ImmutableAuditRecord;

    /** The material actions an event can record (spec §26). */
    public const ACTION_CREATED  = 'created';
    public const ACTION_UPDATED  = 'updated';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_ARCHIVED = 'archived';
    public const ACTION_DELETED  = 'deleted';

    /**
     * Card 6b008b8dc9020955b2cc16e1: an intentional cross-agency read through
     * App\Support\Tenant::bypass(). Recorded so a bypass is visible after the
     * fact rather than silent. Not part of ACTIONS: that list is the material
     * create/update/approve/reject/archive/delete vocabulary (spec §26), and
     * this is an access event, not a record change.
     */
    public const ACTION_TENANT_BYPASS = 'tenant_bypass';

    public const ACTIONS = [
        self::ACTION_CREATED,
        self::ACTION_UPDATED,
        self::ACTION_APPROVED,
        self::ACTION_REJECTED,
        self::ACTION_ARCHIVED,
        self::ACTION_DELETED,
    ];

    public const ACTION_LABELS = [
        self::ACTION_CREATED  => 'Created',
        self::ACTION_UPDATED  => 'Updated',
        self::ACTION_APPROVED => 'Approved',
        self::ACTION_REJECTED => 'Rejected',
        self::ACTION_ARCHIVED => 'Archived',
        self::ACTION_DELETED  => 'Deleted',
        self::ACTION_TENANT_BYPASS => 'Tenant bypass',
    ];

    protected $table = 'audit_events';

    /**
     * The table has no `updated_at`, and an audit event is never amended.
     * Eloquent would otherwise try to write both timestamps on insert.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'agency_id',
        'actor_user_id',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'before',
        'after',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'before'     => 'array',
            'after'      => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /** Human label for the action, falling back to the raw value. */
    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst((string) $this->action);
    }
}
