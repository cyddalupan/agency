<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Applicants: composite indices for filtered queries (status, country, date range)
        Schema::table('applicants', function (Blueprint $table) {
            $table->index(['agency_id', 'status_code', 'created_at'], 'idx_applicants_agency_status_date');
            $table->index(['agency_id', 'country_id'], 'idx_applicants_agency_country');
            $table->index(['agency_id', 'created_at'], 'idx_applicants_agency_date');
        });

        // Bills: composite indices for SOA queries
        Schema::table('bills', function (Blueprint $table) {
            $table->index(['agency_id', 'employer_id', 'created_at'], 'idx_bills_agency_employer_date');
            $table->index(['agency_id', 'applicant_id', 'created_at'], 'idx_bills_agency_applicant_date');
            $table->index(['employer_id', 'status'], 'idx_bills_employer_status');
            $table->index(['applicant_id', 'status'], 'idx_bills_applicant_status');
        });

        // Payments: composite index for bill-based lookups
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['bill_id', 'category', 'status'], 'idx_payments_bill_cat_status');
        });

        // Employers: common queries
        Schema::table('employers', function (Blueprint $table) {
            $table->index(['agency_id', 'created_at'], 'idx_employers_agency_date');
        });

        // Job positions: employer lookups
        Schema::table('job_positions', function (Blueprint $table) {
            $table->index(['employer_id', 'status'], 'idx_job_positions_employer_status');
        });

        // Commissions: polymorphic lookups
        Schema::table('commissions', function (Blueprint $table) {
            $table->index(['employer_id', 'status'], 'idx_commissions_employer_status');
            $table->index(['commissionable_type', 'commissionable_id', 'status'], 'idx_commissions_poly_status');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropIndex('idx_applicants_agency_status_date');
            $table->dropIndex('idx_applicants_agency_country');
            $table->dropIndex('idx_applicants_agency_date');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('idx_bills_agency_employer_date');
            $table->dropIndex('idx_bills_agency_applicant_date');
            $table->dropIndex('idx_bills_employer_status');
            $table->dropIndex('idx_bills_applicant_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_bill_cat_status');
        });

        Schema::table('employers', function (Blueprint $table) {
            $table->dropIndex('idx_employers_agency_date');
        });

        Schema::table('job_positions', function (Blueprint $table) {
            $table->dropIndex('idx_job_positions_employer_status');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropIndex('idx_commissions_employer_status');
            $table->dropIndex('idx_commissions_poly_status');
        });
    }
};
