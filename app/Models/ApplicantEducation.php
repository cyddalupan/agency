<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'level', 'school', 'degree', 'year_graduated', 'remarks'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
