<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Card 6b07eb4f — a billing plan is a *definition* of the commercial
 * agreement between an agency and Landas.
 *
 * CONTROL-PLANE: no TenantScope on purpose. These rows belong to Landas, not
 * to a tenant, so they must never be swept into a tenant query. If you add a
 * scope here, check every tenant-facing query first.
 *
 * There is no `status` column. The village's four states (Paid / Paying in /
 * Delayed / No billing) are derived from plans + payments and split across
 * TWO axes (initial_state x monthly_state). Storing one enum would lose the
 * "paid up but this month is late" case that Cyd's worked example requires.
 */
class AgencyBillingPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'agency_id',
        'initial_payment',
        'initial_terms',
        'monthly_amount',
        'terms',
        'currency',
        'starts_on',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'initial_payment' => 'decimal:2',
        'initial_terms'   => 'integer',
        'monthly_amount'  => 'decimal:2',
        'terms'           => 'integer',
        'starts_on'       => 'date',
        'is_active'       => 'boolean',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AgencyBillingPayment::class, 'plan_id');
    }

    /**
     * Derived on read, never stored: storing it would let it drift from the
     * two numbers it is made of.
     */
    public function getTotalContractValueAttribute(): string
    {
        $initial = (float) $this->initial_payment;
        $monthly = (float) $this->monthly_amount * (int) $this->terms;

        return number_format($initial + $monthly, 2, '.', '');
    }
}
