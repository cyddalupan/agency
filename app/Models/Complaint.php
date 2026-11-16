<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-13 (card 6afa0daa5b58fd9984641757): one complaint or grievance.
 *
 * Spec §13 QMS. Separate from `Cases`, which is legal case tracking; a
 * complaint here is a service grievance handled to a service-level deadline.
 *
 * **The SLA.** Each agency configures its own response windows in
 * `agencies.settings['complaint_sla']` as
 * `{"ack_days": 2, "resolve_days": 15}`. Anything missing or nonsensical falls
 * back to those same 2 / 15 defaults, so the register is never unusable
 * because nobody has configured it yet.
 *
 * **The dates are frozen at creation.** A complaint that arrived under a
 * 2-day ack window keeps that 2-day deadline even if the agency later widens
 * the window to 5 days. The alternative — recomputing deadlines from the
 * current setting — would silently move the goalposts on complaints already
 * being worked. Only a complaint without a stored target is backfilled.
 */
class Complaint extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_OPEN          = 'open';
    public const STATUS_ACKNOWLEDGED  = 'acknowledged';
    public const STATUS_INVESTIGATING = 'investigating';
    public const STATUS_RESOLVED      = 'resolved';
    public const STATUS_CLOSED        = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_ACKNOWLEDGED,
        self::STATUS_INVESTIGATING,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_OPEN          => 'Open',
        self::STATUS_ACKNOWLEDGED  => 'Acknowledged',
        self::STATUS_INVESTIGATING => 'Investigating',
        self::STATUS_RESOLVED      => 'Resolved',
        self::STATUS_CLOSED        => 'Closed',
    ];

    public const STATUS_BADGES = [
        self::STATUS_OPEN          => 'badge-error',
        self::STATUS_ACKNOWLEDGED  => 'badge-warning',
        self::STATUS_INVESTIGATING => 'badge-info',
        self::STATUS_RESOLVED      => 'badge-success',
        self::STATUS_CLOSED        => 'badge-ghost',
    ];

    public const TYPE_APPLICANT = 'applicant';
    public const TYPE_EMPLOYER  = 'employer';
    public const TYPE_INTERNAL  = 'internal';

    public const COMPLAINANT_TYPES = [
        self::TYPE_APPLICANT => 'Applicant',
        self::TYPE_EMPLOYER  => 'Employer',
        self::TYPE_INTERNAL  => 'Internal',
    ];

    /** Sensible fallback when an agency has not configured its own SLA. */
    public const DEFAULT_ACK_DAYS     = 2;
    public const DEFAULT_RESOLVE_DAYS = 15;

    /** The key this model reads from agencies.settings. */
    public const SLA_SETTINGS_KEY = 'complaint_sla';

    /** Statuses that no longer count as live work. */
    public const CLOSED_STATUSES = [self::STATUS_RESOLVED, self::STATUS_CLOSED];

    protected $table = 'complaints';

    protected $fillable = [
        'agency_id',
        'ref_no',
        'complainant_name',
        'complainant_type',
        'applicant_id',
        'category',
        'description',
        'date_received',
        'acknowledged_at',
        'target_ack_date',
        'target_resolve_date',
        'resolved_at',
        'status',
        'assigned_to',
        'resolution',
        'closure_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_received'       => 'date',
            'acknowledged_at'     => 'datetime',
            'target_ack_date'     => 'date',
            'target_resolve_date' => 'date',
            'resolved_at'         => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Complaint $complaint): void {
            $complaint->applyTargetDates();
        });
    }

    // ---------------------------------------------------------------------
    // SLA resolution
    // ---------------------------------------------------------------------

    /**
     * The SLA windows for an agency: `['ack_days' => int, 'resolve_days' => int]`.
     *
     * Reads `agencies.settings['complaint_sla']` and coerces anything missing,
     * non-numeric or below 1 back to the default for that field. A 0-day
     * window is treated as "not configured" rather than "acknowledge
     * instantly", because a zero here is far more likely a half-filled form
     * than a policy decision.
     */
    public static function slaFor(?Agency $agency): array
    {
        $settings = $agency?->settings;
        $settings = is_object($settings) ? $settings->toArray() : (array) ($settings ?? []);

        $configured = $settings[self::SLA_SETTINGS_KEY] ?? [];
        $configured = is_array($configured) ? $configured : [];

        return [
            'ack_days'     => self::slaValue($configured['ack_days'] ?? null, self::DEFAULT_ACK_DAYS),
            'resolve_days' => self::slaValue($configured['resolve_days'] ?? null, self::DEFAULT_RESOLVE_DAYS),
        ];
    }

    /** The SLA for this complaint's own agency. */
    public function sla(): array
    {
        return self::slaFor($this->agency);
    }

    private static function slaValue($value, int $default): int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return $default;
        }

        $days = (int) $value;

        return $days >= 1 ? $days : $default;
    }

    /**
     * Fill in the target dates that have not been set yet.
     *
     * Called from `saving`, so a complaint created through any path gets its
     * deadlines. Existing target dates are left alone: they are the record of
     * what the complainant was promised.
     */
    public function applyTargetDates(): void
    {
        $receivedAt = $this->date_received
            ? \Illuminate\Support\Carbon::parse($this->date_received)
            : null;

        if ($receivedAt === null) {
            return;
        }

        $sla = $this->sla();

        if ($this->target_ack_date === null) {
            $this->target_ack_date = $receivedAt->copy()->addDays($sla['ack_days'])->toDateString();
        }

        if ($this->target_resolve_date === null) {
            $this->target_resolve_date = $receivedAt->copy()->addDays($sla['resolve_days'])->toDateString();
        }
    }

    // ---------------------------------------------------------------------
    // Relations and presentation
    // ---------------------------------------------------------------------

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Short label for the audit trail; the human key is `ref_no`. */
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

    public function getIsClosedAttribute(): bool
    {
        return in_array((string) $this->status, self::CLOSED_STATUSES, true);
    }

    /** Acknowledged late (or still unacknowledged past the ack deadline). */
    public function getIsAckOverdueAttribute(): bool
    {
        if ($this->target_ack_date === null || $this->acknowledged_at !== null) {
            return false;
        }

        return $this->target_ack_date->isPast();
    }

    /** Resolved late (or still unresolved past the resolve deadline). */
    public function getIsResolveOverdueAttribute(): bool
    {
        if ($this->target_resolve_date === null || $this->resolved_at !== null || $this->is_closed) {
            return false;
        }

        return $this->target_resolve_date->isPast();
    }

    /** Days left until the resolve deadline; negative when overdue. */
    public function getDaysToResolveAttribute(): ?int
    {
        if ($this->target_resolve_date === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->target_resolve_date->copy()->startOfDay(), false);
    }
}
