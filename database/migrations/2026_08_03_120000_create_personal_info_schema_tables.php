<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LANDAS "Personal Information" — PI:8 missing schema tables.
 *
 * Creates the sub-entity tables for spouse, family members, emergency
 * contacts, NBI, OEC, visa, contract, and ticket. Mirrors the column style
 * used by the existing applicant sub-tables (agency_id + applicant_id FKs,
 * indexed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_spouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('partner_name')->nullable();
            $table->integer('number_of_children')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('relation')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('contact')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_nbis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('nbi_no')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_oecs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('oec_no')->nullable();
            $table->date('oec_release')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('visa_no')->nullable();
            $table->string('visa_type')->nullable();
            $table->date('received_date')->nullable();
            $table->date('stamped_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('approved_musaned')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('rfp')->nullable();
            $table->string('sponsor')->nullable();
            $table->string('sponsor_id')->nullable();
            $table->string('contact')->nullable();
            $table->string('address')->nullable();
            $table->date('contract_received')->nullable();
            $table->date('contract_signed')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->string('airline')->nullable();
            $table->date('flight_date')->nullable();
            $table->string('flight_time')->nullable();
            $table->text('flight_remarks')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_tickets');
        Schema::dropIfExists('applicant_contracts');
        Schema::dropIfExists('applicant_visas');
        Schema::dropIfExists('applicant_oecs');
        Schema::dropIfExists('applicant_nbis');
        Schema::dropIfExists('applicant_emergency_contacts');
        Schema::dropIfExists('applicant_family_members');
        Schema::dropIfExists('applicant_spouses');
    }
};
