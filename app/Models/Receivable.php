<?php

namespace App\Models;

use App\Models\Traits\AuditsChanges;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receivable extends Model
{
    use HasFactory, HasTenant, SoftDeletes, AuditsChanges;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_RECEIVED  = 'received';
    // Card 6af8296b (2026-11-14): Cancel is a terminal state; a cancelled
    // receivable keeps its row for the audit trail but no longer counts as money.
    public const STATUS_CANCELLED = 'cancelled';

    /** Every status a receivable may hold, in tab order. */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_RECEIVED,
        self::STATUS_CANCELLED,
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING   => 'Pending',
            self::STATUS_RECEIVED  => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Accounts that may be filed without an Agent or an Applicant
     * (card 6af8296b): office returns, monthly collection, other collection.
     * These are internal money movements, not tied to a specific worker.
     */
    public const STANDALONE_ACCOUNTS = [
        'Office Returns',
        'Monthly Collection',
        'Other Collection',
    ];

    public static function allowsStandalone(?string $account): bool
    {
        return $account !== null && in_array($account, self::STANDALONE_ACCOUNTS, true);
    }

    public const ACCOUNTS = [
        'Agents & Applicant Payment',
        'Applicant Expenses',
        'Commission Returns',
        'Monthly Collection',
        'Office Returns',
        'Other Collection',
        'Placement Fee',
        'Remittance Fee',
    ];

    public const DEBIT_ACCOUNTS = ['Receivable', 'Dollar Request'];

    public const TYPES = ['Partial', 'Full Payment'];

    public const MODES = ['Cash', 'Cheque', 'Fund Transfer', 'Credit', 'GCash'];

    protected $fillable = [
        'agency_id',
        'user_id',
        'agent_id',
        'applicant_id',
        'code',
        'date',
        'status',
        'ref_ar',
        'amount',
        'account',
        'debit_account',
        'type',
        'mode',
        'particular',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ReceivableHistory::class)->latest();
    }

    /**
     * Generate the next 6-digit receipt code unique within this agency.
     */
    public static function nextCode(int $agencyId): string
    {
        $max = static::withoutGlobalScopes()
            ->where('agency_id', $agencyId)
            ->max('code');

        $next = $max ? ((int) $max) + 1 : 1;

        return str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Multi-file attachments (Cyd 2026-11-09, "Upload and Edit").
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ReceivableAttachment::class);
    }
}
