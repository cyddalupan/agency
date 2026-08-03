<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantContract extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'rfp', 'sponsor', 'sponsor_id', 'contact', 'address', 'contract_received', 'contract_signed'];

    protected $casts = ['contract_received' => 'date', 'contract_signed' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
