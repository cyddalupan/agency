<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantWorkExperience extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'company', 'position', 'from_date', 'start_date', 'date_from', 'date_to', 'to_date', 'end_date', 'responsibilities'];

    protected $casts = ['from_date' => 'date', 'start_date' => 'date', 'to_date' => 'date', 'end_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
