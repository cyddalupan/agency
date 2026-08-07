<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantOwWa extends Model
{
    use HasFactory, HasTenant;

    /**
     * Explicit table name — Eloquent otherwise pluralizes to applicant_ow_was.
     */
    protected $table = 'applicant_owwas';

    protected $fillable = ['agency_id', 'applicant_id', 'from_date', 'to_date', 'released_date', 'local_flight_date'];

    protected $casts = ['from_date' => 'date', 'to_date' => 'date', 'released_date' => 'date', 'local_flight_date' => 'date'];

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }
}
