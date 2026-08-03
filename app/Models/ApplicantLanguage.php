<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantLanguage extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'name', 'proficiency'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
