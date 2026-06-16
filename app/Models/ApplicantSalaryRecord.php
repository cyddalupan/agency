<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantSalaryRecord extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'amount', 'currency', 'type', 'notes'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
