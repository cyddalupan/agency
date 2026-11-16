<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-17 (card 6afa0dae821b259e5f9fd4d4): what an audit turned up.
 *
 * The relationship to NonConformity is the card's acceptance criterion, so it
 * is a real foreign key in both directions, not a loose `source` label. An
 * observation or an opportunity normally does not warrant an NC; a minor or
 * major non-conformity usually does. We do not forbid the other combinations —
 * the auditor may have a reason — we only record what was chosen.
 */
class AuditFinding extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const TYPE_OBSERVATION = 'observation';
    public const TYPE_MINOR_NC    = 'minor_nc';
    public const TYPE_MAJOR_NC    = 'major_nc';
    public const TYPE_OPPORTUNITY = 'opportunity';

    public const TYPES = [
        self::TYPE_OBSERVATION => 'Observation',
        self::TYPE_MINOR_NC    => 'Minor non-conformity',
        self::TYPE_MAJOR_NC    => 'Major non-conformity',
        self::TYPE_OPPORTUNITY => 'Opportunity for improvement',
    ];

    public const TYPE_BADGES = [
        self::TYPE_OBSERVATION => 'badge-ghost',
        self::TYPE_MINOR_NC    => 'badge-warning',
        self::TYPE_MAJOR_NC    => 'badge-error',
        self::TYPE_OPPORTUNITY => 'badge-info',
    ];

    /**
     * Finding type -> the severity the spawned NC inherits. Observation and
     * opportunity have no natural severity, so they default to minor.
     */
    public const SEVERITY_FOR_TYPE = [
        self::TYPE_OBSERVATION => NonConformity::SEVERITY_MINOR,
        self::TYPE_MINOR_NC    => NonConformity::SEVERITY_MINOR,
        self::TYPE_MAJOR_NC    => NonConformity::SEVERITY_MAJOR,
        self::TYPE_OPPORTUNITY => NonConformity::SEVERITY_MINOR,
    ];

    protected $table = 'audit_findings';

    protected $fillable = [
        'agency_id',
        'internal_audit_id',
        'finding_no',
        'type',
        'clause_reference',
        'description',
        'evidence',
        'non_conformity_id',
        'created_by',
    ];

    public function internalAudit(): BelongsTo
    {
        return $this->belongsTo(InternalAudit::class);
    }

    public function nonConformity(): BelongsTo
    {
        return $this->belongsTo(NonConformity::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function getTypeBadgeAttribute(): string
    {
        return self::TYPE_BADGES[$this->type] ?? 'badge-ghost';
    }

    public function hasNonConformity(): bool
    {
        return $this->non_conformity_id !== null;
    }

    /**
     * F-1, F-2 ... within one audit. Numbered per audit rather than per agency
     * because a finding only means anything in the context of its audit.
     */
    public static function suggestNextNo(int $internalAuditId): string
    {
        $last = static::query()
            ->withTrashed()
            ->where('internal_audit_id', $internalAuditId)
            ->where('finding_no', 'like', 'F-%')
            ->pluck('finding_no')
            ->map(fn ($ref) => (int) preg_replace('/\D/', '', (string) $ref))
            ->max() ?? 0;

        return 'F-' . ($last + 1);
    }
}
