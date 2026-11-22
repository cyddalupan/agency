<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = \App\Models\Commission::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            // Card 6b0363676e4f389b5b3fd002: the employer lives in the commission's agency.
            'employer_id' => fn (array $a) => Employer::factory()->create(['agency_id' => $a['agency_id']])->id,
            'commissionable_type' => $this->faker->randomElement([
                'marketing_agency',
                'marketing_agent',
                'recruitment_agent',
            ]),
            'commissionable_id' => $this->faker->numberBetween(1, 100),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'paid_amount' => 0,
            'status' => 'pending',
            'due_date' => $this->faker->optional()->date(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
