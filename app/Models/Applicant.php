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
        'job_id', 'status_code', 'password', 'status',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'expected_salary' => 'decimal:2',
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

    public function logs()
    {
        return $this->hasMany(ApplicantLog::class);
    }

    // === HELPERS ===

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Agent::class);
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
}
