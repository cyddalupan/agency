<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ApplicantLog extends Model
{
    use HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'user_id', 'old_status', 'new_status', 'notes'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
