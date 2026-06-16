<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantCertificate extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'type', 'certificate_no', 'issue_date', 'expiry_date', 'file_path', 'remarks'];

    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
