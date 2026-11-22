<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * EMPLOYEE module record (Mjolnir card 6af6fe94, 2026-11-13).
 *
 * Standalone HR data for CSDI and FINAS — intentionally not linked to
 * applicants or agents.
 */
class Employee extends Model
{
    use HasFactory;
    use HasTenant;

    public const EMPLOYMENT_STATUSES = ['Active', 'Inactive', 'Resigned'];
    public const DOCUMENT_STATUSES = ['Complete', 'Incomplete', 'Expired'];

    protected $fillable = [
        'agency_id', 'employee_no', 'full_name', 'position', 'department',
        'branch_id', 'employment_status', 'date_hired',
        'educational_background', 'job_description',
        'appointment_document', 'appointment_document_name',
        'nbi_reference', 'nbi_issued_at', 'nbi_expires_at',
        'nbi_document', 'nbi_document_name',
        'training_records', 'training_document', 'training_document_name',
        'competency_assessment',
        'qms_orientation', 'qms_document', 'qms_document_name',
        'document_status', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'date_hired'    => 'date',
        'nbi_issued_at' => 'date',
        'nbi_expires_at'=> 'date',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
