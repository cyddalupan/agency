<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ApplicantEducation: year_start, year_end
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->year('year_start')->nullable()->after('degree');
            $table->year('year_end')->nullable()->after('year_start');
        });

        // ApplicantWorkExperiences: date_from, date_to
        Schema::table('applicant_work_experiences', function (Blueprint $table) {
            $table->date('date_from')->nullable()->after('position');
            $table->date('date_to')->nullable()->after('date_from');
        });

        // ApplicantCertificates: name, issued_by
        Schema::table('applicant_certificates', function (Blueprint $table) {
            $table->string('name')->nullable()->after('type');
            $table->string('issued_by')->nullable()->after('name');
        });

        // ApplicantReferences: company
        Schema::table('applicant_references', function (Blueprint $table) {
            $table->string('company')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->dropColumn(['year_start', 'year_end']);
        });

        Schema::table('applicant_work_experiences', function (Blueprint $table) {
            $table->dropColumn(['date_from', 'date_to']);
        });

        Schema::table('applicant_certificates', function (Blueprint $table) {
            $table->dropColumn(['name', 'issued_by']);
        });

        Schema::table('applicant_references', function (Blueprint $table) {
            $table->dropColumn('company');
        });
    }
};
