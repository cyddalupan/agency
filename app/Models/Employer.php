<?php

namespace App\Models;

use App\Models\Traits\HasCustomFields;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;
    use HasTenant;
    use HasCustomFields;

    protected $fillable = [
        'agency_id', 'company_no', 'name', 'contact_person', 'contact',
        'email', 'address', 'country_id', 'commission', 'agent_commission',
        'commission_type', 'status',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class);
    }

    public function jobPositions()
    {
        return $this->hasMany(JobPosition::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }
}
