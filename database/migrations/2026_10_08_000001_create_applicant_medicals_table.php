<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LANDAS "Personal Information > Requirements" — MEDICAL sub-entity.
 *
 * Sits under the NBI section. Mirrors the applicant_nbis column style
 * (agency_id + applicant_id FKs, indexed). Fields per spec:
 * Clinic Name - Issue Date - Expire Date - Remarks - Upload Medical.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_medicals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('clinic_name')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_medicals');
    }
};
