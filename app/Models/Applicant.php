<?php

namespace App\Models;

use App\Models\Traits\HasCustomFields;
use App\Models\Traits\HasTenant;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Applicant extends Model implements AuthenticatableContract
{
    use HasFactory, HasTenant, HasCustomFields, AuthenticatableTrait;

    protected $fillable = [
        'agency_id', 'first_name', 'middle_name', 'last_name', 'suffix',
        'birthdate', 'gender', 'has_passport', 'contact', 'email', 'address', 'photo', 'full_body_photo',
        'remarks', 'source', 'nationality_id', 'religion_id', 'civil_status_id',
        'country_id', 'position_id', 'expected_salary', 'employer_id', 'agent_id',
        'job_id', 'status_code', 'password', 'status', 'firstimer_type',
        'applicant_no', 'fra', 'status_date', 'repat', 'repat_date',
        'branch_id', 'encoder', 'contract', 'contract_received_date',
        'created_by',
        'number_of_siblings',
        'mother_name', 'mother_occupation', 'father_name', 'father_occupation',
        'e_reg', 'peos', 'info_sheet', 'birth_certificate', 'marriage_certificate',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected $casts = [
        'birthdate' => 'date',
        'contract_received_date' => 'date',
        'number_of_siblings' => 'integer',
        'expected_salary' => 'decimal:2',
        'e_reg' => 'boolean',
        'peos' => 'boolean',
        'info_sheet' => 'boolean',
        'birth_certificate' => 'boolean',
        'marriage_certificate' => 'boolean',
        'status_date' => 'date',
        'repat' => 'boolean',
        'repat_date' => 'date',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function statusCode()
    {
        return $this->belongsTo(StatusCode::class, 'status_code', 'code');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }

    public function civilStatus()
    {
        return $this->belongsTo(CivilStatus::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function job()
    {
        return $this->belongsTo(JobPosition::class, 'job_id');
    }

    // === SUB-TABLE RELATIONSHIPS ===

    public function education()
    {
        return $this->hasMany(ApplicantEducation::class);
    }

    public function passport()
    {
        return $this->hasOne(ApplicantPassport::class);
    }

    public function certificates()
    {
        return $this->hasMany(ApplicantCertificate::class);
    }

    public function requirements()
    {
        return $this->hasMany(ApplicantRequirement::class);
    }

    public function workExperiences()
    {
        return $this->hasMany(ApplicantWorkExperience::class);
    }

    public function skills()
    {
        return $this->hasMany(ApplicantSkill::class);
    }

    public function languages()
    {
        return $this->hasMany(ApplicantLanguage::class);
    }

    public function contacts()
    {
        return $this->hasMany(ApplicantContact::class);
    }

    public function references()
    {
        return $this->hasMany(ApplicantReference::class);
    }

    public function salaryRecords()
    {
        return $this->hasMany(ApplicantSalaryRecord::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicantDocument::class);
    }

    // === LANDAS PERSONAL INFORMATION (PI:8) SUB-TABLE RELATIONSHIPS ===

    public function spouse()
    {
        return $this->hasMany(ApplicantSpouse::class);
    }

    public function family()
    {
        return $this->hasMany(ApplicantFamilyMember::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(ApplicantEmergencyContact::class);
    }

    public function nbi()
    {
        return $this->hasMany(ApplicantNbi::class);
    }

    public function oec()
    {
        return $this->hasMany(ApplicantOec::class);
    }

    public function visa()
    {
        return $this->hasMany(ApplicantVisa::class);
    }

    public function contract()
    {
        return $this->hasMany(ApplicantContract::class);
    }

    public function tickets()
    {
        return $this->hasMany(ApplicantTicket::class);
    }

    public function oma()
    {
        return $this->hasMany(ApplicantOma::class);
    }

    public function owwa()
    {
        return $this->hasMany(ApplicantOwWa::class);
    }

    public function logs()
    {
        return $this->hasMany(ApplicantLog::class);
    }

    // === HELPERS ===

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Agent::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }
        // If it's already a full URL (http/https), use it directly
        if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
            return $this->photo;
        }
        // Otherwise resolve through storage (local file)
        return Storage::url($this->photo);
    }

    public function getFullBodyPhotoUrlAttribute(): ?string
    {
        if (! $this->full_body_photo) {
            return null;
        }
        if (str_starts_with($this->full_body_photo, 'http://') || str_starts_with($this->full_body_photo, 'https://')) {
            return $this->full_body_photo;
        }
        return Storage::url($this->full_body_photo);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name} {$this->suffix}");
    }

    public function getAgeAttribute(): ?int
    {
        if (! $this->birthdate) {
            return null;
        }
        return (int) \Illuminate\Support\Carbon::parse($this->birthdate)->age;
    }
}
