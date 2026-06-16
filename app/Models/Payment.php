<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'agency_id',
        'bill_id',
        'amount',
        'category',
        'type',
        'reference_no',
        'status',
        'payment_date',
        'notes',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function officialReceipt(): HasOne
    {
        return $this->hasOne(OfficialReceipt::class);
    }
}
