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
            'agency_id' => Agency::factory(),
            'user_id'   => User::factory(),
            'agent_id'  => null,
            'applicant_id' => Applicant::factory(),
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
