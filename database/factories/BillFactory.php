<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'employer_id' => Employer::factory(),
            'applicant_id' => Applicant::factory(),
            'employer_cost' => $this->faker->randomFloat(2, 10000, 200000),
            'applicant_cost' => $this->faker->randomFloat(2, 1000, 20000),
            'employer_deposit' => $this->faker->randomFloat(2, 0, 50000),
            'applicant_deposit' => $this->faker->randomFloat(2, 0, 5000),
            'status' => 'pending',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
