<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Welfare / monitoring record for an OFW applicant
 * (FINAS card: PERSONAL INFORMATION "WELFARE Tab", 2026-11-06).
 */
class ApplicantWelfareRecord extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    /** Mandatory monitoring milestones (key => label). */
    public const MILESTONES = [
        'day_3'          => 'Day 3 (Arrival Check)',
        'month_1'        => 'Month 1 (Adjustment Check)',
        'month_3'        => 'Month 3 (Quarterly Review)',
        'month_6'        => 'Month 6 (Semi-Annual Review)',
        'incident_driven' => 'Incident-Driven Call (Emergency/Dispute)',
    ];

    /** Assessment indicators (field prefix => label). */
    public const INDICATORS = [
        'wages_paid'          => 'Wages paid on time and in full?',
        'food_accommodation'  => 'Food and accommodation satisfactory?',
        'medical_status'      => 'Medical/health status normal?',
        'contract_provisions' => 'Contract provisions followed?',
        'working_environment' => 'Working environment safe?',
    ];

    protected $fillable = [
        'agency_id',
        'applicant_id',
        'logged_by',
        'date_of_contact',
        'ofw_full_name',
        'passport_number',
        'dmw_ereg_id',
        'country_of_destination',
        'job_site_city',
        'position_designation',
        'contact_no_abroad',
        'foreign_recruitment_agency',
        'direct_employer_name',
        'contact_person_phone',
        'milestones',
        'wages_paid',
        'wages_paid_specify',
        'food_accommodation',
        'food_accommodation_specify',
        'medical_status',
        'medical_status_specify',
        'contract_provisions',
        'contract_provisions_specify',
        'working_environment',
        'working_environment_specify',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date_of_contact' => 'date',
            'milestones'      => 'array',
        ];
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    /** Human label for a milestone key. */
    public static function milestoneLabel(string $key): string
    {
        return self::MILESTONES[$key] ?? $key;
    }
}
