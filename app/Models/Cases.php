<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cases extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'applicant_id',
        'employer_id',
        'case_number',
        'title',
        'description',
        'date_received',
        'date_hearing',
        'court',
        'status',
        'priority',
    ];

    protected $attributes = [
        'status' => 'open',
        'priority' => 'normal',
    ];

    protected function casts(): array
    {
        return [
            'date_received' => 'date',
            'date_hearing' => 'date',
        ];
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
