<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ReferenceDataSeeder::class,
            StatusCodesSeeder::class,
            StatusTransitionSeeder::class,
            // InitialAgencySeeder::class, // DISABLED — was overwriting existing users
            SponsorSeeder::class,
            AccountSeeder::class, // default chart of accounts per agency (idempotent)
        ]);
    }
}
