<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'      => fake()->company(),
            'subdomain' => fake()->unique()->word(),
            'status'    => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'inactive']);
    }
}
