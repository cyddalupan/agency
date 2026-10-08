<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * LANDAS "Personal Information > Requirements" — MEDICAL record.
 *
 * Added under the NBI section (Add Applicant - MEDICAL): Clinic Name,
 * Issue Date, Expire Date, Remarks, Upload Medical.
 */
class ApplicantMedical extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'clinic_name', 'issue_date', 'expiry_date', 'remarks', 'file_path'];

    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
