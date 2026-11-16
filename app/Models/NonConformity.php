<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use App\Services\NonConformityClosure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

/**
 * QMS-15 (card 6afa0dac333d35f77d709e61): one non-conformity.
 *
 * Spec §14. A requirement that was not met, however it was found. Its
 * corrective actions are what the agency is doing about it, and `closed` is
 * only reachable once every one of them has been verified *and* had its
 * effectiveness reviewed.
 *
 * The gate is enforced in `saving`, not in a controller, for the same reason
 * the audit-immutability guard is a trait: any future screen, seeder or
 * command that touches this model must hit the same wall.
 */
class NonConformity extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_OPEN           = 'open';
    public const STATUS_CONTAINED      = 'contained';
    public const STATUS_ROOT_CAUSE     = 'root_cause';
    public const STATUS_ACTION_PLANNED = 'action_planned';
    public const STATUS_VERIFYING      = 'verifying';
    public const STATUS_CLOSED         = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CONTAINED,
        self::STATUS_ROOT_CAUSE,
        self::STATUS_ACTION_PLANNED,
        self::STATUS_VERIFYING,
        self::STATUS_CLOSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_OPEN           => 'Open',
        self::STATUS_CONTAINED      => 'Contained',
        self::STATUS_ROOT_CAUSE     => 'Root cause identified',
        self::STATUS_ACTION_PLANNED => 'Action planned',
        self::STATUS_VERIFYING      => 'Verifying',
        self::STATUS_CLOSED         => 'Closed',
    ];

    public const STATUS_BADGES = [
        self::STATUS_OPEN           => 'badge-error',
        self::STATUS_CONTAINED      => 'badge-warning',
        self::STATUS_ROOT_CAUSE     => 'badge-info',
        self::STATUS_ACTION_PLANNED => 'badge-info',
        self::STATUS_VERIFYING      => 'badge-warning',
        self::STATUS_CLOSED         => 'badge-success',
    ];

    public const SOURCE_AUDIT           = 'audit';
    public const SOURCE_COMPLAINT       = 'complaint';
    public const SOURCE_INCIDENT        = 'incident';
    public const SOURCE_SELF_IDENTIFIED = 'self_identified';

    public const SOURCES = [
        self::SOURCE_AUDIT           => 'Audit',
        self::SOURCE_COMPLAINT       => 'Complaint',
        self::SOURCE_INCIDENT        => 'Incident',
        self::SOURCE_SELF_IDENTIFIED => 'Self-identified',
    ];

    public const SEVERITY_MINOR    = 'minor';
    public const SEVERITY_MAJOR    = 'major';
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITIES = [
        self::SEVERITY_MINOR    => 'Minor',
        self::SEVERITY_MAJOR    => 'Major',
        self::SEVERITY_CRITICAL => 'Critical',
    ];

    public const SEVERITY_BADGES = [
        self::SEVERITY_MINOR    => 'badge-ghost',
        self::SEVERITY_MAJOR    => 'badge-warning',
        self::SEVERITY_CRITICAL => 'badge-error',
    ];

    protected $table = 'non_conformities';

    protected $fillable = [
        'agency_id',
        'ref_no',
        'source',
        'complaint_id',
        'detected_date',
        'process_area',
        'description',
        'requirement_breached',
        'severity',
        'status',
        'owner_id',
        'due_date',
        'closed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'detected_date' => 'date',
            'due_date'      => 'date',
            'closed_at'     => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (NonConformity $nc): void {
            // Closing stamps the time; opening it again clears the stamp, so the
            // column always means "when this was closed", never "was closed once".
            if ($nc->status === self::STATUS_CLOSED && $nc->closed_at === null) {
                $nc->closed_at = now();
            }

            if ($nc->status !== self::STATUS_CLOSED && $nc->closed_at !== null) {
                $nc->closed_at = null;
            }

            if ($nc->status !== self::STATUS_CLOSED) {
                return;
            }

            // The gate. Only enforced on a save that is actually trying to
            // close — an already-closed record being touched for another reason
            // would otherwise start failing once its actions change.
            if (! $nc->isDirtyStatusToClosed()) {
                return;
            }

            $blockers = app(NonConformityClosure::class)->closureBlockers($nc);

            if ($blockers !== []) {
                throw ValidationException::withMessages([
                    'status' => array_merge(
                        ['A non-conformity cannot be closed until every corrective action is verified and its effectiveness reviewed.'],
                        $blockers
                    ),
                ]);
            }
        });
    }

    /**
     * Is this save moving the status to closed?
     *
     * `wasRecentlyCreated` covers insert-as-closed; `isDirty('status')` covers
     * an update that flips it. A row already closed and re-saved for an
     * unrelated field is not a closure attempt and must not be re-judged.
     */
    private function isDirtyStatusToClosed(): bool
    {
        if ($this->wasRecentlyCreated) {
            return true;
        }

        return $this->isDirty('status');
    }

    // ---------------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------------

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'non_conformity_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---------------------------------------------------------------------
    // Presentation
    // ---------------------------------------------------------------------

    public function auditSubjectLabel(): ?string
    {
        return $this->ref_no !== null && $this->ref_no !== ''
            ? mb_substr((string) $this->ref_no, 0, 120)
            : parent::auditSubjectLabel();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::SEVERITIES[$this->severity] ?? ucfirst((string) $this->severity);
    }

    public function getSeverityBadgeAttribute(): string
    {
        return self::SEVERITY_BADGES[$this->severity] ?? 'badge-ghost';
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst(str_replace('_', ' ', (string) $this->source));
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /** Past its due date and not closed. */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->due_date === null || $this->is_closed) {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function getClosureBlockersAttribute(): array
    {
        return app(NonConformityClosure::class)->closureBlockers($this);
    }

    public function getCanCloseAttribute(): bool
    {
        return $this->closure_blockers === [];
    }
}
