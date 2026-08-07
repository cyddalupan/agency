<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PI: 6. Status — add status-tab fields to the applicants table:
     * Applicant#, FRA (dropdown), Status Date, Repat (tick box) + Repat Date.
     * (The Status dropdown already lives in existing `status_code` column.)
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('applicant_no')->nullable()->after('status_code');
            $table->string('fra')->nullable()->after('applicant_no');
            $table->date('status_date')->nullable()->after('fra');
            $table->boolean('repat')->default(false)->after('status_date');
            $table->date('repat_date')->nullable()->after('repat');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['applicant_no', 'fra', 'status_date', 'repat', 'repat_date']);
        });
    }
};
