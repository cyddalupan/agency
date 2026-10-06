<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mjolnir card "Skilled Applicants" (2026-10-06):
 *  - Add `applicants.applicant_type` (household | skilled) so the Applicant and
 *    Backout/Repat modules can split into HOUSEHOLD and SKILLED tabs.
 *  - Add `positions.category` (household | skilled) so the Add Applicant form
 *    can restrict positions by type: Household => Domestic Helper only,
 *    Skilled => every other position.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('applicants', 'applicant_type')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->string('applicant_type', 20)->nullable()->index()->after('firstimer_type');
            });
        }

        if (! Schema::hasColumn('positions', 'category')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->string('category', 20)->default('skilled')->index()->after('description');
            });
        }

        // Ensure the single Household position exists and is categorised correctly.
        $exists = DB::table('positions')->whereRaw('LOWER(name) = ?', ['domestic helper'])->exists();
        if (! $exists) {
            DB::table('positions')->insert([
                'name' => 'Domestic Helper',
                'description' => null,
                'category' => 'household',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('positions')->whereRaw('LOWER(name) = ?', ['domestic helper'])->update(['category' => 'household']);
        }

        // Everything else defaults to Skilled; make sure no other row sits in household.
        DB::table('positions')->whereRaw('LOWER(name) <> ?', ['domestic helper'])->update(['category' => 'skilled']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('positions', 'category')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        if (Schema::hasColumn('applicants', 'applicant_type')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->dropColumn('applicant_type');
            });
        }
    }
};
