<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantVisa extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'visa_no', 'visa_type', 'received_date', 'stamped_date', 'expiry_date', 'approved_musaned'];

    protected $casts = ['received_date' => 'date', 'stamped_date' => 'date', 'expiry_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
