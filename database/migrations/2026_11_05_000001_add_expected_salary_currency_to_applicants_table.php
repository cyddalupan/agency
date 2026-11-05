<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Card "LANDAS: Add Applicant 'Currency'" (Kchanz Josh, 2026-11-05):
 * "Add select currency to the salary".
 *
 * Applicants already store expected_salary but the currency was implicit
 * (the resume printed a hard-coded "USD", the portal printed "₱"), so an
 * overseas applicant asking for SAR 1,500 read as ₱1,500.00.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            if (! Schema::hasColumn('applicants', 'expected_salary_currency')) {
                $table->string('expected_salary_currency', 3)
                    ->nullable()
                    ->default('PHP')
                    ->after('expected_salary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            if (Schema::hasColumn('applicants', 'expected_salary_currency')) {
                $table->dropColumn('expected_salary_currency');
            }
        });
    }
};
