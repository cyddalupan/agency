<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cyd 2026-10-07 (Gulf + FINAS) — "Civil Status - Add 'Single with Children'".
     *
     * `civil_statuses` is a global reference table seeded once from
     * ReferenceDataSeeder; existing databases never re-run the seeder, so the
     * new option is added here too. Idempotent so a fresh `migrate --seed`
     * (migration + seeder) can't create a duplicate row.
     */
    public function up(): void
    {
        if (! DB::table('civil_statuses')->where('name', 'Single with Children')->exists()) {
            DB::table('civil_statuses')->insert([
                'name'       => 'Single with Children',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('civil_statuses')->where('name', 'Single with Children')->delete();
    }
};
