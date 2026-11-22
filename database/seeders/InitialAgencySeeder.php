<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitialAgencySeeder extends Seeder
{
    public function run(): void
    {
        // Create a test agency
        $agency = Agency::create([
            'name'      => 'Demo Agency',
            'subdomain' => 'demo',
            'status'    => 'active',
        ]);

        // Card 6b0341cf42a2071fc8b38ea5: there is NO seeded super admin.
        //
        // `admin@agency.com` / `password` was a live known-password account with
        // cross-agency read. It is gone: god accounts are minted only by
        // `php artisan godmode:create`, with a password typed at a hidden
        // prompt. Do not add one back here — a seeder password is a password in
        // the repository.
        //
        // This edits the seeder, not a live row: on an existing deployment the
        // operator must also set the old super admin to `status = 'pending'`
        // (see the PR notes).

        // Create an agency admin
        User::create([
            'agency_id' => $agency->id,
            'name'      => 'Agency Admin',
            'email'     => 'agency@demo.com',
            'password'  => 'password',
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->command->info('Initial agency + users seeded.');
    }
}
