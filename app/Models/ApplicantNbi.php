<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantNbi extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'nbi_no', 'issue_date', 'expiry_date'];

    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
