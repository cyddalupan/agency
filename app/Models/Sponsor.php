<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'id_number',
        'company_name',
        'contact_person',
        'email',
        'contact_no',
        'viber',
        'address',
        'city',
        'status',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function applicants(): BelongsToMany
    {
        return $this->belongsToMany(Applicant::class, 'sponsor_applicant')
            ->withPivot(['selected_at', 'status'])
            ->withTimestamps();
    }

    public function activeApplicants(): BelongsToMany
    {
        return $this->belongsToMany(Applicant::class, 'sponsor_applicant')
            ->withPivot(['selected_at', 'status'])
            ->withTimestamps()
            ->wherePivot('status', 'active');
    }
}
