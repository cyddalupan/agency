<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantRequirement extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'type', 'reference_no', 'status', 'submitted_date', 'approved_date', 'file_path', 'remarks'];

    protected $casts = ['submitted_date' => 'date', 'approved_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
