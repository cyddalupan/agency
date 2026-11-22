<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceivableFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Card 6b0363676e4f389b5b3fd002: derive from the user who owns the
            // row, and keep the applicant in the receivable's own agency.
            'agency_id' => fn (array $a) => User::find($a['user_id'] ?? null)?->agency_id ?? Agency::factory(),
            'user_id'   => User::factory(),
            'agent_id'  => null,
            'applicant_id' => fn (array $a) => Applicant::factory()->create(['agency_id' => $a['agency_id']])->id,
            'code'      => str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'date'      => now()->toDateString(),
            'status'    => 'pending',
            'ref_ar'    => fake()->optional()->numerify('AR-####'),
            'amount'    => fake()->randomFloat(2, 1000, 100000),
            'account'   => 'Placement Fee',
            'debit_account' => 'Receivable',
            'type'      => 'Full Payment',
            'mode'      => 'GCash',
            'particular' => fake()->sentence(),
        ];
    }
}
