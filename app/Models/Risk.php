<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * QMS-11 (card 6afa0da82ced12ad55cfdf0a): one entry in the risk register.
 *
 * Spec §12 — likelihood × impact, owner, treatment, review dates.
 *
 * Scoring is a 5×5 matrix: each axis runs 1-5, so the score runs 1-25. The
 * banding below is the default; the card notes per-agency scales as a later
 * feature, so the thresholds live in one place (`LEVEL_BANDS`) and one method
 * (`levelFor()`), ready to be swapped for a tenant setting without touching
 * any caller.
 *
 * `risk_score` and `risk_level` are recomputed on every save rather than being
 * left to the caller. A register whose score disagrees with its own likelihood
 * and impact is worse than no register: the whole point is that someone can
 * sort by exposure and trust the order.
 */
class Risk extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_OPEN         = 'open';
    public const STATUS_IN_TREATMENT = 'in_treatment';
    public const STATUS_CLOSED       = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_TREATMENT,
        self::STATUS_CLOSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_OPEN         => 'Open',
        self::STATUS_IN_TREATMENT => 'In Treatment',
        self::STATUS_CLOSED       => 'Closed',
    ];

    public const LEVEL_LOW      = 'low';
    public const LEVEL_MEDIUM   = 'medium';
    public const LEVEL_HIGH     = 'high';
    public const LEVEL_CRITICAL = 'critical';

    public const LEVELS = [
        self::LEVEL_LOW,
        self::LEVEL_MEDIUM,
        self::LEVEL_HIGH,
        self::LEVEL_CRITICAL,
    ];

    public const LEVEL_LABELS = [
        self::LEVEL_LOW      => 'Low',
        self::LEVEL_MEDIUM   => 'Medium',
        self::LEVEL_HIGH     => 'High',
        self::LEVEL_CRITICAL => 'Critical',
    ];

    /**
     * Score thresholds, inclusive lower bounds, lowest first.
     *
     * The bands the card's acceptance pins down:
     *   1×1  = 1   → low
     *   3×4  = 12  → high
     *   5×5  = 25  → critical
     *
     * A 5×5 matrix with three equal-width bands (1-8 / 9-16 / 17-25) would put
     * 12 in Medium, but the card says 12 is High, so the bands are adjusted:
     * anything from 12 up is High. That leaves Low at 1-4, Medium 5-11,
     * High 12-19, Critical 20-25.
     */
    public const LEVEL_BANDS = [
        self::LEVEL_LOW      => 0,   // 1-4
        self::LEVEL_MEDIUM   => 5,   // 5-11
        self::LEVEL_HIGH     => 12,  // 12-19
        self::LEVEL_CRITICAL => 20,  // 20-25
    ];

    /** The smallest and largest value on either axis. */
    public const SCALE_MIN = 1;
    public const SCALE_MAX = 5;

    /** Badge classes reused by the screens (same vocabulary as document control). */
    public const LEVEL_BADGES = [
        self::LEVEL_LOW      => 'badge-success',
        self::LEVEL_MEDIUM   => 'badge-warning',
        self::LEVEL_HIGH     => 'badge-error',
        self::LEVEL_CRITICAL => 'badge-error badge-outline',
    ];

    public const STATUS_BADGES = [
        self::STATUS_OPEN         => 'badge-ghost',
        self::STATUS_IN_TREATMENT => 'badge-warning',
        self::STATUS_CLOSED       => 'badge-success',
    ];

    protected $table = 'risks';

    protected $fillable = [
        'agency_id',
        'ref_no',
        'process_area',
        'title',
        'description',
        'cause',
        'consequence',
        'likelihood',
        'impact',
        'risk_score',
        'risk_level',
        'existing_controls',
        'treatment_action',
        'risk_owner_id',
        'target_date',
        'review_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'likelihood'  => 'integer',
            'impact'      => 'integer',
            'risk_score'  => 'integer',
            'target_date' => 'date',
            'review_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Risk $risk): void {
            $risk->applyScoring();
        });
    }

    // ---------------------------------------------------------------------
    // Scoring helper (the part QMS-12's matrix view and dashboard reuse)
    // ---------------------------------------------------------------------

    /** Score for a likelihood/impact pair: the product, 1-25. */
    public static function scoreFor(int $likelihood, int $impact): int
    {
        return $likelihood * $impact;
    }

    /**
     * The level band for a score.
     *
     * Bands are matched from the top down so a score above the declared
     * maximum still lands in Critical rather than falling off the end.
     */
    public static function levelFor(int $score): string
    {
        foreach (array_reverse(self::LEVEL_BANDS, true) as $level => $threshold) {
            if ($score >= $threshold) {
                return $level;
            }
        }

        return self::LEVEL_LOW;
    }

    /** Convenience: the level for a likelihood/impact pair. */
    public static function levelForPair(int $likelihood, int $impact): string
    {
        return self::levelFor(self::scoreFor($likelihood, $impact));
    }

    /**
     * Keep the stored score and level in step with the axes.
     *
     * Called from `saving`, so it covers create, update and mass-assignment
     * alike — a caller cannot write a stale score even by accident.
     */
    public function applyScoring(): void
    {
        $likelihood = (int) $this->likelihood;
        $impact     = (int) $this->impact;

        $score = self::scoreFor($likelihood, $impact);

        $this->risk_score = $score;
        $this->risk_level = self::levelFor($score);
    }

    // ---------------------------------------------------------------------
    // Relations and presentation
    // ---------------------------------------------------------------------

    public function riskOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'risk_owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Short label for the audit trail. The shared trait looks for code /
     * reference_no / name; this register's human key is `ref_no`, so say so
     * rather than letting the label fall back to the row id.
     */
    public function auditSubjectLabel(): ?string
    {
        return $this->ref_no !== null && $this->ref_no !== ''
            ? mb_substr((string) $this->ref_no, 0, 120)
            : parent::auditSubjectLabel();
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVEL_LABELS[$this->risk_level] ?? ucfirst((string) $this->risk_level);
    }

    public function getLevelBadgeAttribute(): string
    {
        return self::LEVEL_BADGES[$this->risk_level] ?? 'badge-ghost';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }

    /** True when the risk is still live and its review date has passed. */
    public function getIsReviewOverdueAttribute(): bool
    {
        return $this->status !== self::STATUS_CLOSED
            && $this->review_date !== null
            && $this->review_date->isPast();
    }
}
