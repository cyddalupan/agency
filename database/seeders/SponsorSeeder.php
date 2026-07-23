<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SponsorSeeder extends Seeder
{
    public function run(): void
    {
        $agency = Agency::where('status', 'active')->first()
            ?? Agency::first();

        if (! $agency) {
            $this->command->warn('No agency found. Skipping SponsorSeeder.');
            return;
        }

        $idNumber = 'SP20240001';
        $companyName = 'Demo Sponsor Corp';
        $email = 'sponsor@demo.com';
        $password = 'password123';

        // Create or update sponsor record
        Sponsor::updateOrCreate(
            ['id_number' => $idNumber],
            [
                'agency_id'    => $agency->id,
                'company_name' => $companyName,
                'contact_person' => 'John Demo',
                'email'        => $email,
                'contact_no'   => '+639123456789',
                'address'      => '123 Business Ave',
                'city'         => 'Manila',
                'status'       => 'active',
            ]
        );

        // Create or update user account
        User::updateOrCreate(
            ['email' => $email],
            [
                'agency_id' => $agency->id,
                'name'      => $companyName,
                'username'  => $idNumber,
                'password'  => Hash::make($password),
                'user_type' => 'sponsor',
                'status'    => 'active',
            ]
        );

        $this->command->info("Sponsor test account created:");
        $this->command->info("  ID Number: {$idNumber}");
        $this->command->info("  Email:     {$email}");
        $this->command->info("  Password:  {$password}");
    }
}
