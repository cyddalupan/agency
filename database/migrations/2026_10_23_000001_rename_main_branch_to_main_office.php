<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * (Cyd 2026-10-23) Head office is called "Main Office", not "Main Branch".
 *
 * Agencies were created with a branch literally named "Main Branch" (one has it
 * typed as "mAIN BRANCH"), which leaks into every branch dropdown, report and
 * applicant row. Rename those rows to "Main Office". Case/whitespace is
 * normalised for the match, and an agency that already has a "Main Office"
 * branch is left alone so we never create two.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agenciesWithMainOffice = DB::table('branches')
            ->whereRaw("LOWER(TRIM(name)) = 'main office'")
            ->pluck('agency_id')
            ->all();

        DB::table('branches')
            ->whereRaw("LOWER(TRIM(REPLACE(name, '  ', ' '))) = 'main branch'")
            ->whereNotIn('agency_id', $agenciesWithMainOffice ?: [0])
            ->update([
                'name'       => 'Main Office',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('branches')
            ->where('name', 'Main Office')
            ->update([
                'name'       => 'Main Branch',
                'updated_at' => now(),
            ]);
    }
};
