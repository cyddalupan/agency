<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            // Card 6b0363676e4f389b5b3fd002: the employer lives in the position's agency.
            'employer_id' => fn (array $a) => Employer::factory()->create(['agency_id' => $a['agency_id']])->id,
            'name' => $this->faker->jobTitle(),
            'gender_preference' => $this->faker->randomElement(['male', 'female', 'any']),
            'salary' => $this->faker->randomFloat(2, 10000, 100000),
            'total_slots' => $this->faker->numberBetween(1, 50),
            'occupied' => 0,
            'status' => 'open',
        ];
    }
}
