<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'agency_id', 'employer_id', 'position_id', 'name', 'content',
        'gender_preference', 'salary', 'salary_currency', 'total_slots',
        'occupied', 'status',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function position()
    {
        return $this->belongsTo(\App\Models\Position::class);
    }
}
