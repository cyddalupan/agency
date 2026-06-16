<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingAgency extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'agency_id', 'name', 'contact_person', 'contact', 'email',
        'address', 'commission_rate', 'status',
    ];

    public function marketingAgents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketingAgent::class);
    }
}
