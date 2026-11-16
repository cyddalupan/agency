<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-15 (card 6afa0dac333d35f77d709e61): one corrective action against a
 * non-conformity.
 *
 * Carries two independent sign-offs, because they answer different questions:
 *
 *   - `verified_at` — was this action actually carried out?
 *   - `effectiveness_verified_at` — did carrying it out stop the problem
 *     recurring?
 *
 * Spec §14 requires both before the parent non-conformity may close, and
 * `NonConformityClosure` is what judges it. An action can be `completed` (the
 * work was done, `completed_date` set) without either sign-off: the person did
 * the thing, but nobody has confirmed it or tested whether it worked.
 */
class CorrectiveAction extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_VERIFIED    = 'verified';
    public const STATUS_EFFECTIVE   = 'effective';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_VERIFIED,
        self::STATUS_EFFECTIVE,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING     => 'Pending',
        self::STATUS_IN_PROGRESS => 'In progress',
        self::STATUS_COMPLETED   => 'Completed',
        self::STATUS_VERIFIED    => 'Verified',
        self::STATUS_EFFECTIVE   => 'Effective',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING     => 'badge-ghost',
        self::STATUS_IN_PROGRESS => 'badge-info',
        self::STATUS_COMPLETED   => 'badge-warning',
        self::STATUS_VERIFIED    => 'badge-info',
        self::STATUS_EFFECTIVE   => 'badge-success',
    ];

    protected $table = 'corrective_actions';

    protected $fillable = [
        'agency_id',
        'non_conformity_id',
        'action_text',
        'owner_id',
        'due_date',
        'completed_date',
        'verification_notes',
        'verified_by',
        'verified_at',
        'effectiveness_notes',
        'effectiveness_verified_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date'                   => 'date',
            'completed_date'             => 'date',
            'verified_at'                => 'datetime',
            'effectiveness_verified_at'  => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CorrectiveAction $action): void {
            // Status is derived from the sign-offs rather than typed in, so the
            // badge can never claim "effective" while a date is missing.
            $action->status = $action->deriveStatus();
        });
    }

    /**
     * The status implied by the recorded dates.
     *
     * Ordered most-final first: the last true statement wins, which is what
     * makes the badge trustworthy.
     */
    public function deriveStatus(): string
    {
        if ($this->effectiveness_verified_at !== null) {
            return self::STATUS_EFFECTIVE;
        }

        if ($this->verified_at !== null) {
            return self::STATUS_VERIFIED;
        }

        if ($this->completed_date !== null) {
            return self::STATUS_COMPLETED;
        }

        // Keep an explicit "in progress" if someone set it; otherwise pending.
        return $this->status === self::STATUS_IN_PROGRESS
            ? self::STATUS_IN_PROGRESS
            : self::STATUS_PENDING;
    }

    // ---------------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------------

    public function nonConformity(): BelongsTo
    {
        return $this->belongsTo(NonConformity::class, 'non_conformity_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---------------------------------------------------------------------
    // Presentation
    // ---------------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->verified_at !== null;
    }

    public function getIsEffectiveAttribute(): bool
    {
        return $this->effectiveness_verified_at !== null;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->due_date === null || $this->completed_date !== null) {
            return false;
        }

        return $this->due_date->isPast();
    }
}
