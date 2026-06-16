<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === MAIN APPLICANT TABLE ===
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->text('remarks')->nullable();
            $table->string('source')->nullable(); // walk-in, referral, online, marketing
            $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->nullOnDelete();
            $table->foreignId('religion_id')->nullable()->constrained('religions')->nullOnDelete();
            $table->foreignId('civil_status_id')->nullable()->constrained('civil_statuses')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete(); // preferred country
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete(); // preferred position
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->foreignId('employer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('job_positions')->nullOnDelete();
            $table->integer('status_code')->default(0);
            $table->string('password')->nullable(); // applicant self-service password
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('agency_id');
            $table->index('status_code');
            $table->index(['last_name', 'first_name']);
        });

        // === APPLICANT SUB-TABLES ===

        Schema::create('applicant_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('level')->nullable(); // mba, college, high_school
            $table->string('school')->nullable();
            $table->string('degree')->nullable();
            $table->string('year_graduated')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_passports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('passport_no')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('place_of_issue')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // tesda, medical, insurance, pdos, nbi, etc.
            $table->string('certificate_no')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // visa, oec, owwa, contract, mofa, job_offer, etc.
            $table->string('reference_no')->nullable();
            $table->string('status')->default('pending'); // pending, submitted, approved, rejected
            $table->date('submitted_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->string('file_path')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->text('responsibilities')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->string('proficiency')->nullable(); // beginner, intermediate, expert
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('relation')->nullable();
            $table->string('position')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_salary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('PHP');
            $table->string('type')->nullable(); // basic, allowance, other
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // photo, passport_copy, cert, resume, etc.
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        // === AUDIT LOGS ===

        Schema::create('applicant_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('old_status')->nullable();
            $table->integer('new_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_logs');
        Schema::dropIfExists('applicant_documents');
        Schema::dropIfExists('applicant_salary_records');
        Schema::dropIfExists('applicant_references');
        Schema::dropIfExists('applicant_skills');
        Schema::dropIfExists('applicant_work_experiences');
        Schema::dropIfExists('applicant_requirements');
        Schema::dropIfExists('applicant_certificates');
        Schema::dropIfExists('applicant_passports');
        Schema::dropIfExists('applicant_education');
        Schema::dropIfExists('applicants');
    }
};
