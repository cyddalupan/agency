<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-17 (card 6afa0dae821b259e5f9fd4d4): one planned internal audit.
 *
 * The audit is the event; its findings are the output. `status` moves
 * planned -> in_progress -> completed -> closed and nothing in this model
 * enforces that order — the screens do. The model only guarantees the
 * reference numbering and that the audit belongs to a tenant.
 */
class InternalAudit extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_PLANNED     = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CLOSED      = 'closed';

    public const STATUSES = [
        self::STATUS_PLANNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CLOSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PLANNED     => 'Planned',
        self::STATUS_IN_PROGRESS => 'In progress',
        self::STATUS_COMPLETED   => 'Completed',
        self::STATUS_CLOSED      => 'Closed',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PLANNED     => 'badge-ghost',
        self::STATUS_IN_PROGRESS => 'badge-warning',
        self::STATUS_COMPLETED   => 'badge-info',
        self::STATUS_CLOSED      => 'badge-success',
    ];

    protected $table = 'internal_audits';

    protected $fillable = [
        'agency_id',
        'ref_no',
        'title',
        'scope',
        'criteria',
        'scheduled_date',
        'conducted_date',
        'lead_auditor_id',
        'status',
        'summary',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'conducted_date' => 'date',
        ];
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AuditFinding::class);
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * AUD-001, AUD-002 ... per agency, ignoring soft-deleted rows so a
     * restored audit cannot collide with a fresh one.
     */
    public static function suggestNextRef(?int $agencyId = null): string
    {
        $last = static::query()
            ->withTrashed()
            ->when($agencyId !== null, fn ($q) => $q->where('agency_id', $agencyId))
            ->where('ref_no', 'like', 'AUD-%')
            ->pluck('ref_no')
            ->map(fn ($ref) => (int) preg_replace('/\D/', '', (string) $ref))
            ->max() ?? 0;

        return 'AUD-' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }
}
