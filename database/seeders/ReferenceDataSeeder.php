<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        // === COUNTRIES ===
        $countries = [
            ['Saudi Arabia', 'SA', 'Saudi'],
            ['United Arab Emirates', 'AE', 'Emirati'],
            ['Kuwait', 'KW', 'Kuwaiti'],
            ['Qatar', 'QA', 'Qatari'],
            ['Bahrain', 'BH', 'Bahraini'],
            ['Oman', 'OM', 'Omani'],
            ['Hong Kong', 'HK', 'Hong Konger'],
            ['Singapore', 'SG', 'Singaporean'],
            ['Malaysia', 'MY', 'Malaysian'],
            ['Taiwan', 'TW', 'Taiwanese'],
            ['Japan', 'JP', 'Japanese'],
            ['South Korea', 'KR', 'South Korean'],
            ['Philippines', 'PH', 'Filipino'],
        ];
        foreach ($countries as $c) {
            DB::table('countries')->insert([
                'name'        => $c[0],
                'code'        => $c[1],
                'nationality' => $c[2],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // === POSITIONS ===
        $positions = [
            'Nurse', 'Caregiver', 'Driver', 'Domestic Worker', 'Housekeeper',
            'Waiter', 'Waitress', 'Chef', 'Cook', 'Baker',
            'Sales Clerk', 'Cashier', 'Customer Service', 'Receptionist',
            'Janitor', 'Cleaner', 'Laundry Worker', 'Maid',
            'Electrician', 'Plumber', 'Carpenter', 'Welder', 'Mechanic',
            'Engineer', 'Architect', 'IT Specialist', 'Accountant',
            'Teacher', 'Tutor', 'Babysitter', 'Nanny', 'Elderly Caregiver',
            'Pharmacist', 'Lab Technician', 'X-ray Technician',
            'Security Guard', 'Office Staff', 'Administrative Assistant',
            'Hairdresser', 'Beautician', 'Massage Therapist',
            'Seaman', 'Steward', 'Messman',
        ];
        foreach ($positions as $p) {
            DB::table('positions')->insert([
                'name'       => $p,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // === NATIONALITIES ===
        foreach (['Filipino', 'Indonesian', 'Vietnamese', 'Thai', 'Indian', 'Nepali', 'Bangladeshi', 'Sri Lankan', 'Pakistani', 'Chinese', 'Others'] as $n) {
            DB::table('nationalities')->insert(['name' => $n, 'created_at' => now(), 'updated_at' => now()]);
        }

        // === RELIGIONS ===
        foreach (['Roman Catholic', 'Christian', 'Muslim', 'Iglesia ni Cristo', 'Buddhist', 'Hindu', 'Others'] as $r) {
            DB::table('religions')->insert(['name' => $r, 'created_at' => now(), 'updated_at' => now()]);
        }

        // === CIVIL STATUSES ===
        foreach (['Single', 'Married', 'Divorced', 'Widowed', 'Separated', 'Annulled'] as $cs) {
            DB::table('civil_statuses')->insert(['name' => $cs, 'created_at' => now(), 'updated_at' => now()]);
        }

        $this->command->info('Reference data seeded.');
    }
}
