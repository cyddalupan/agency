<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ApplicantEducation: add course alias for degree
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->string('course')->nullable()->after('school');
        });

        // ApplicantPassports: add passport_number, place_issue aliases
        Schema::table('applicant_passports', function (Blueprint $table) {
            $table->string('passport_number')->nullable()->after('passport_no');
            $table->string('place_issue')->nullable()->after('place_of_issue');
        });

        // ApplicantCertificates: add certificate_name, institution, date_obtained
        Schema::table('applicant_certificates', function (Blueprint $table) {
            $table->string('certificate_name')->nullable()->after('type');
            $table->string('institution')->nullable()->after('certificate_name');
            $table->date('date_obtained')->nullable()->after('institution');
            $table->string('type')->nullable()->change();
        });

        // ApplicantWorkExperiences: add start_date, end_date aliases
        Schema::table('applicant_work_experiences', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('position');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_education', function (Blueprint $table) {
            $table->dropColumn('course');
        });

        Schema::table('applicant_passports', function (Blueprint $table) {
            $table->dropColumn(['passport_number', 'place_issue']);
        });

        Schema::table('applicant_certificates', function (Blueprint $table) {
            $table->dropColumn(['certificate_name', 'institution', 'date_obtained']);
            $table->string('type')->nullable(false)->change();
        });

        Schema::table('applicant_work_experiences', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
