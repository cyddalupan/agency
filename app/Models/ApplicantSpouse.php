<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantSpouse extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'partner_name', 'number_of_children'];

    protected $casts = ['number_of_children' => 'integer'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
