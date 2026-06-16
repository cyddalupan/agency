<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingAgent extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'agency_id', 'marketing_agency_id', 'name', 'contact', 'email', 'status',
    ];

    public function marketingAgency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MarketingAgency::class);
    }
}
