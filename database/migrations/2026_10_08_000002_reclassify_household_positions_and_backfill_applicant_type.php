<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mjolnir card "Skilled Applicants" — follow-up (2026-10-08).
 *
 * Cyd: "Napunta ung mga DH sa Skilled" — the domestic-worker positions were
 * categorised as "skilled", and legacy applicants carry applicant_type = NULL
 * (treated as Skilled), so real domestic helpers landed on the Skilled tab and
 * the HOUSEHOLD tab showed 0.
 *
 * This migration:
 *  1. reclassifies the domestic roster to the "household" category, and
 *  2. backfills applicants.applicant_type from the applicant's position
 *     category for existing (legacy / NULL) rows.
 */
return new class extends Migration
{
    /**
     * Position titles that belong to the Household (domestic worker) roster.
     * Matched case-insensitively against positions.name.
     */
    private const HOUSEHOLD_POSITIONS = [
        'domestic helper',
        'domestic worker',
        'household worker',
        'housekeeper',
        'maid',
        'houseboy',
        'nanny',
        'babysitter',
        'cleaner',
        'female cleaner',
    ];

    public function up(): void
    {
        // 1) Domestic roster -> Household category.
        DB::table('positions')->where(function ($q) {
            foreach (self::HOUSEHOLD_POSITIONS as $name) {
                $q->orWhereRaw('LOWER(name) = ?', [$name]);
            }
        })->update(['category' => 'household', 'updated_at' => now()]);

        // 2) Backfill legacy applicants from their position's category.
        $householdIds = DB::table('positions')->where('category', 'household')->pluck('id')->all();
        $skilledIds = DB::table('positions')->where('category', 'skilled')->pluck('id')->all();

        if ($householdIds) {
            DB::table('applicants')
                ->whereNull('applicant_type')
                ->whereIn('position_id', $householdIds)
                ->update(['applicant_type' => 'household', 'updated_at' => now()]);
        }

        if ($skilledIds) {
            DB::table('applicants')
                ->whereNull('applicant_type')
                ->whereIn('position_id', $skilledIds)
                ->update(['applicant_type' => 'skilled', 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Best-effort revert: everything but Domestic Helper goes back to Skilled.
        DB::table('positions')
            ->where(function ($q) {
                foreach (array_diff(self::HOUSEHOLD_POSITIONS, ['domestic helper']) as $name) {
                    $q->orWhereRaw('LOWER(name) = ?', [$name]);
                }
            })
            ->update(['category' => 'skilled', 'updated_at' => now()]);
    }
};
