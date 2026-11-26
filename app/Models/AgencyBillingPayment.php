<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Card 6b07eb4f — a billing payment is a *fact*. Append-only: mistakes are
 * voided (soft-deleted), never edited in place.
 *
 * CONTROL-PLANE: no TenantScope. See AgencyBillingPlan for the reasoning.
 */
class AgencyBillingPayment extends Model
{
    use SoftDeletes;

    public const TYPE_INITIAL = 'initial';
    public const TYPE_MONTHLY = 'monthly';

    public const TYPES = [self::TYPE_INITIAL, self::TYPE_MONTHLY];

    protected $fillable = [
        'agency_id',
        'plan_id',
        'payment_type',
        'amount',
        'paid_on',
        'mode',
        'reference',
        'particular',
        'recorded_by',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_on' => 'date',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AgencyBillingPlan::class, 'plan_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
