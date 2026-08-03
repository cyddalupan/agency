<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantTicket extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['agency_id', 'applicant_id', 'airline', 'flight_date', 'flight_time', 'flight_remarks'];

    protected $casts = ['flight_date' => 'date'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
