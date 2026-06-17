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
        'title',
        'description',
        'status',
        'priority',
    ];

    protected $attributes = [
        'status' => 'open',
        'priority' => 'normal',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
