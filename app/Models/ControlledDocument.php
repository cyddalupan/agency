<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-06 (card 6afa0da3de9f235c9653dab7): one controlled document.
 *
 * Part of document control (spec §26): every procedure, policy, form and work
 * instruction an agency relies on is registered here, reviewed on a cadence,
 * and only ever approved through a version (QMS-07/QMS-10).
 *
 * `code` is unique per agency, so two agencies may each have "QMS-001".
 */
class ControlledDocument extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_DRAFT      = 'draft';
    public const STATUS_IN_REVIEW  = 'in_review';
    public const STATUS_APPROVED   = 'approved';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_OBSOLETE   = 'obsolete';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_IN_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_SUPERSEDED,
        self::STATUS_OBSOLETE,
    ];

    public const STATUS_LABELS = [
        self::STATUS_DRAFT      => 'Draft',
        self::STATUS_IN_REVIEW  => 'In Review',
        self::STATUS_APPROVED   => 'Approved',
        self::STATUS_SUPERSEDED => 'Superseded',
        self::STATUS_OBSOLETE   => 'Obsolete',
    ];

    public const STATUS_BADGES = [
        self::STATUS_DRAFT      => 'badge-ghost',
        self::STATUS_IN_REVIEW  => 'badge-warning',
        self::STATUS_APPROVED   => 'badge-success',
        self::STATUS_SUPERSEDED => 'badge-info',
        self::STATUS_OBSOLETE   => 'badge-error',
    ];

    public const DOCUMENT_TYPES = [
        'procedure'         => 'Procedure',
        'policy'            => 'Policy',
        'form'              => 'Form',
        'work_instruction'  => 'Work Instruction',
        'manual'            => 'Manual',
    ];

    public const CONFIDENTIALITY_LEVELS = [
        'public'       => 'Public',
        'internal'     => 'Internal',
        'confidential' => 'Confidential',
        'restricted'   => 'Restricted',
    ];

    public const REVIEW_FREQUENCIES = [
        'monthly'     => 'Monthly',
        'quarterly'   => 'Quarterly',
        'semi_annual' => 'Semi-Annual',
        'annual'      => 'Annual',
        'biennial'    => 'Biennial',
    ];

    protected $fillable = [
        'agency_id',
        'code',
        'title',
        'document_type',
        'department',
        'process_owner_id',
        'confidentiality',
        'status',
        'current_version_id',
        'next_review_date',
        'review_frequency',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'next_review_date' => 'date',
        ];
    }

    public function processOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'process_owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Human label for the status, falling back to the raw value. */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    /** Badge class for the status, falling back to a neutral badge. */
    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    /** True when the document is approved and its review date has passed. */
    public function getIsReviewOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->next_review_date !== null
            && $this->next_review_date->isPast();
    }
}
