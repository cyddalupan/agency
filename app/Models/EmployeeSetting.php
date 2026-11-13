<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One agency-maintained option for the Employee form
 * (Position / Designation, Department, Job Description).
 */
class EmployeeSetting extends Model
{
    use HasFactory;

    public const TYPES = [
        'position'        => 'Position / Designation',
        'department'      => 'Department',
        'job_description' => 'Job Description',
    ];

    protected $fillable = ['agency_id', 'type', 'name', 'description', 'sort_order', 'status'];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public static function optionsFor(int $agencyId, string $type): array
    {
        return static::where('agency_id', $agencyId)
            ->where('type', $type)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
