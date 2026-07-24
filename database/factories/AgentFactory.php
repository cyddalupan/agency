<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id'       => Agency::factory(),
            'name'            => $this->faker->name(),
            'email'           => $this->faker->unique()->safeEmail(),
            'contact'         => $this->faker->phoneNumber(),
            'password'        => Hash::make('password'),
            'commission_rate' => $this->faker->randomFloat(2, 0, 20),
            'status'          => 'active',
        ];
    }
}
