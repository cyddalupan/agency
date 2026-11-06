<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FINAS card: PERSONAL INFORMATION "WELFARE Tab" (2026-11-06).
 *
 * One row per welfare/monitoring contact with an OFW applicant:
 * migrant-worker profile, foreign principal details, the mandatory
 * monitoring milestones and the welfare & working-conditions assessment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_welfare_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();

            // Logged By (user complete name) + Date of Contact
            $table->string('logged_by')->nullable();
            $table->date('date_of_contact')->nullable();

            // Migrant worker profile
            $table->string('ofw_full_name')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('dmw_ereg_id')->nullable();
            $table->string('country_of_destination')->nullable();
            $table->string('job_site_city')->nullable();
            $table->string('position_designation')->nullable();
            $table->string('contact_no_abroad')->nullable();

            // Foreign principal & employer details
            $table->string('foreign_recruitment_agency')->nullable();
            $table->string('direct_employer_name')->nullable();
            $table->string('contact_person_phone')->nullable();

            // Mandatory monitoring milestones (json array of keys)
            $table->json('milestones')->nullable();

            // Welfare & working conditions assessment (yes/no + specify)
            $table->string('wages_paid', 3)->nullable();
            $table->text('wages_paid_specify')->nullable();
            $table->string('food_accommodation', 3)->nullable();
            $table->text('food_accommodation_specify')->nullable();
            $table->string('medical_status', 3)->nullable();
            $table->text('medical_status_specify')->nullable();
            $table->string('contract_provisions', 3)->nullable();
            $table->text('contract_provisions_specify')->nullable();
            $table->string('working_environment', 3)->nullable();
            $table->text('working_environment_specify')->nullable();

            // Remarks / issues identified / actions taken
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_welfare_records');
    }
};
