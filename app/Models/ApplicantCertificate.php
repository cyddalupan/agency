<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantCertificate extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'type', 'name', 'certificate_no', 'certificate_name', 'institution', 'issued_by', 'issue_date', 'issued_date', 'date_obtained', 'expiry_date', 'file_path', 'remarks'];

    protected $casts = ['issue_date' => 'date', 'date_obtained' => 'date', 'expiry_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    // Allow setting via issued_date (alias for issue_date)
    public function setIssuedDateAttribute($value)
    {
        $this->attributes['issue_date'] = $value;
    }

    public function getIssuedDateAttribute()
    {
        return $this->issue_date;
    }
}
