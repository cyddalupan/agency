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

        // Create a super admin (no agency_id)
        User::create([
            'name'      => 'Super Admin',
            'email'     => 'admin@agency.com',
            'password'  => bcrypt('password'),
            'user_type' => 'super_admin',
            'status'    => 'active',
        ]);

        // Create an agency admin
        User::create([
            'agency_id' => $agency->id,
            'name'      => 'Agency Admin',
            'email'     => 'agency@demo.com',
            'password'  => bcrypt('password'),
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->command->info('Initial agency + users seeded.');
    }
}
