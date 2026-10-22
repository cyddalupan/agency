<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Toybits filtering fix (2026-10-22).
 *
 * The 2026-10-13 bulk upload created rows with applicant_type = NULL. Those
 * rows were counted by the status chips (all types) but resolved to the
 * HOUSEHOLD tab at query time, so "For Passporting 694" opened the SKILLED
 * tab with 4 rows.
 *
 * Backfill the remaining NULL rows using the same rule scopeOfType() uses,
 * so the stored type and the visible tab agree. Idempotent: only NULL rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        $householdIds = DB::table('positions')
            ->where('category', 'household')
            ->pluck('id')
            ->all();

        if ($householdIds) {
            DB::table('applicants')
                ->whereNull('applicant_type')
                ->whereIn('position_id', $householdIds)
                ->update(['applicant_type' => 'household', 'updated_at' => now()]);
        }

        DB::table('applicants')
            ->whereNull('applicant_type')
            ->update(['applicant_type' => 'skilled', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Data-only backfill; nothing safe to revert.
    }
};
