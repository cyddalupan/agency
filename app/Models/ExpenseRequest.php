<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PENDING       = 'pending';
    public const STATUS_APPROVED      = 'approved';
    public const STATUS_FOR_RELEASING = 'for_releasing';
    public const STATUS_RELEASED      = 'released';
    public const STATUS_CANCELLED     = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_FOR_RELEASING,
        self::STATUS_RELEASED,
        self::STATUS_CANCELLED,
    ];

    public const STATUS_LABELS = [
        'pending'       => 'Pending',
        'approved'      => 'Approved',
        'for_releasing' => 'For Releasing',
        'released'      => 'Released',
        'cancelled'     => 'Cancelled',
    ];

    public const STATUS_BADGES = [
        'pending'       => 'badge-warning',
        'approved'      => 'badge-info',
        'for_releasing' => 'badge-primary',
        'released'      => 'badge-success',
        'cancelled'     => 'badge-error',
    ];

    protected $fillable = [
        'agency_id',
        'user_id',
        'reference_no',
        'date',
        'status',
        'branch_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseRequestItem::class)->orderBy('id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ExpenseRequestStatusHistory::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-ghost';
    }
}
