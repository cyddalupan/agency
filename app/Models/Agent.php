<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'branch_id',
        'name',
        'email',
        'contact',
        'password',
        'commission_rate',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
