<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantPassport extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'passport_no', 'issue_date', 'expiry_date', 'place_of_issue'];

    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
