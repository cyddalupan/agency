<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialReceipt extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'agency_id',
        'payment_id',
        'or_no',
        'amount',
        'issue_date',
        'issued_to',
        'issued_to_name',
        'notes',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
