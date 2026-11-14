<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    public const CATEGORY_HOUSEHOLD = 'household';
    public const CATEGORY_SKILLED = 'skilled';
    // Card 6af82438: new Position category for the PROFESSIONAL applicant tab.
    public const CATEGORY_PROFESSIONAL = 'professional';

    protected $fillable = ['name', 'description', 'category'];

    public static function categories(): array
    {
        return [self::CATEGORY_SKILLED, self::CATEGORY_HOUSEHOLD, self::CATEGORY_PROFESSIONAL];
    }

    public function scopeHousehold($query)
    {
        return $query->where('category', self::CATEGORY_HOUSEHOLD);
    }

    public function scopeProfessional($query)
    {
        return $query->where('category', self::CATEGORY_PROFESSIONAL);
    }

    public function scopeSkilled($query)
    {
        return $query->where(function ($q) {
            $q->where('category', self::CATEGORY_SKILLED)->orWhereNull('category');
        });
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    public function jobPositions()
    {
        return $this->hasMany(JobPosition::class);
    }
}
