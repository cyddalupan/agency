<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-07 (card 6afa0da4a0f4ba9d42c0560e): one version of a controlled document.
 *
 * Versions are immutable in spirit — a correction is a new version, not an
 * edit — but unlike audit events they are ordinary records, so no hard guard.
 *
 * Numbering is major.minor:
 *   - a revision that changes the *substance* bumps major and resets minor (2.0)
 *   - an editorial correction bumps minor (1.1)
 * The caller decides which; `nextVersionNo()` computes the label given a kind.
 */
class ControlledDocumentVersion extends Model
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

    /** A substantive change: bump the major number, reset minor to 0. */
    public const BUMP_MAJOR = 'major';

    /** An editorial correction: bump the minor number. */
    public const BUMP_MINOR = 'minor';

    protected $fillable = [
        'controlled_document_id',
        'agency_id',
        'version_no',
        'effective_date',
        'revision_summary',
        'supersedes_version_id',
        'file_path',
        'file_name',
        'prepared_by',
        'reviewed_by',
        'approved_by',
        'approval_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'approval_date'  => 'date',
        ];
    }

    /** The document this version belongs to. */
    public function document(): BelongsTo
    {
        return $this->belongsTo(ControlledDocument::class, 'controlled_document_id');
    }

    /** The version this one replaced, if any. */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_version_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    /**
     * The document's display name including its version, e.g.
     * "QMS-001 Leave Policy — v1.1".
     */
    public function getFullNameAttribute(): string
    {
        $code  = $this->document?->code ?? '';
        $title = $this->document?->title ?? '';

        $name = trim($code . ' ' . $title);

        if ($this->version_no === null || $this->version_no === '') {
            return $name;
        }

        return trim($name . ' — v' . $this->version_no);
    }

    /**
     * Compute a version label.
     *
     * Given "1.0":  minor bump => "1.1",  major bump => "2.0".
     * With no previous version: starts at "1.0".
     */
    public static function nextVersionNo(?string $current, string $bump = self::BUMP_MINOR): string
    {
        if ($current === null || trim($current) === '') {
            return '1.0';
        }

        $parts = explode('.', trim($current));
        $major = (int) ($parts[0] ?? 0);
        $minor = (int) ($parts[1] ?? 0);

        return $bump === self::BUMP_MAJOR
            ? ($major + 1) . '.0'
            : $major . '.' . ($minor + 1);
    }

    /**
     * The next version label for a document, based on its highest existing
     * version (not merely `current_version_id`, which can lag behind drafts).
     */
    public static function nextVersionNoFor(ControlledDocument $document, string $bump = self::BUMP_MINOR): string
    {
        $highest = static::withoutGlobalScopes()
            ->where('controlled_document_id', $document->id)
            ->orderByDesc('id')
            ->value('version_no');

        return self::nextVersionNo($highest, $bump);
    }
}
