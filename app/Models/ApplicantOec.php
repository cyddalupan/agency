<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantOec extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'oec_no', 'oec_release'];

    protected $casts = ['oec_release' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
