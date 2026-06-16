<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantWorkExperience extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'company', 'position', 'from_date', 'to_date', 'responsibilities'];

    protected $casts = ['from_date' => 'date', 'to_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
