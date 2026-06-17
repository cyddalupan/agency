<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'level', 'school', 'degree', 'course', 'year_start', 'year_end', 'year_graduated', 'remarks'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
