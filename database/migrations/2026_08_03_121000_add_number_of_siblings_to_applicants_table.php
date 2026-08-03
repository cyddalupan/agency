<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LANDAS "Personal Information" — PI:1 Basic Information.
 *
 * Adds an applicant-level "Number of Siblings" field used by the Family
 * Information section of the Basic Information tab.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->integer('number_of_siblings')->nullable()->after('civil_status_id');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('number_of_siblings');
        });
    }
};
