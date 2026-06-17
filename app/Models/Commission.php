<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Commission extends Model
{
    use HasFactory, HasTenant;

    protected $appends = ['balance'];

    protected $fillable = [
        'agency_id',
        'employer_id',
        'commissionable_type',
        'commissionable_id',
        'amount',
        'paid_amount',
        'status',
        'due_date',
        'notes',
    ];

    public function getBalanceAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function commissionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commissionPayments(): HasMany
    {
        return $this->hasMany(CommissionPayment::class);
    }
}
