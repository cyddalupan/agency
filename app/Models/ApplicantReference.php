<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantReference extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'name', 'contact', 'relation', 'position', 'company'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
