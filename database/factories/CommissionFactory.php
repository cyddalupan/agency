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
            'employer_id' => Employer::factory(),
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
