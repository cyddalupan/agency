<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EMPLOYEE module (Mjolnir card 6af6fe94, 2026-11-13).
 *
 * A standalone HR record for CSDI and FINAS. It is deliberately isolated from
 * the applicant/agent core data: no foreign keys into applicants or agents, so
 * adding or removing an employee never touches the rest of the system.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_id')->index();

            // * Employee ID - unique within the agency (open field)
            $table->string('employee_no', 64)->nullable();
            // * Full Name
            $table->string('full_name');
            $table->string('position', 191)->nullable();       // Position / Designation
            $table->string('department', 191)->nullable();
            $table->unsignedBigInteger('branch_id')->nullable(); // Branch Assignment
            $table->string('employment_status', 32)->default('Active'); // Active / Inactive / Resigned
            $table->date('date_hired')->nullable();
            $table->text('educational_background')->nullable();
            $table->text('job_description')->nullable();

            // Appointment Document
            $table->string('appointment_document', 255)->nullable();
            $table->string('appointment_document_name', 255)->nullable();
            // NBI Clearance - reference, issue date, expiry, file
            $table->string('nbi_reference', 191)->nullable();
            $table->date('nbi_issued_at')->nullable();
            $table->date('nbi_expires_at')->nullable();
            $table->string('nbi_document', 255)->nullable();
            $table->string('nbi_document_name', 255)->nullable();
            // Training Records - training attended + certificate
            $table->text('training_records')->nullable();
            $table->string('training_document', 255)->nullable();
            $table->string('training_document_name', 255)->nullable();
            // Competency Assessment
            $table->text('competency_assessment')->nullable();
            // QMS Orientation - attendance and acknowledgment
            $table->text('qms_orientation')->nullable();
            $table->string('qms_document', 255)->nullable();
            $table->string('qms_document_name', 255)->nullable();

            // Document Status - Complete / Incomplete / Expired
            $table->string('document_status', 32)->default('Incomplete');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['agency_id', 'employee_no']);
            $table->index(['agency_id', 'employment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
