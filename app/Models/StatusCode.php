<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusCode extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $fillable = ['code', 'label', 'label_saudi', 'description', 'color', 'sort_order'];

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'status_code', 'code');
    }

    /**
     * Get the display label for a given country.
     * Uses country-specific label (e.g., label_saudi) if available,
     * otherwise falls back to the default label.
     */
    public function labelForCountry(?string $countryName): string
    {
        if ($countryName && str_contains(strtolower($countryName), 'saudi')) {
            return $this->label_saudi ?? $this->label;
        }

        return $this->label;
    }
}
