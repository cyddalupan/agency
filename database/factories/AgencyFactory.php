<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AgencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'           => fake()->company(),
            'subdomain'      => fake()->unique()->word(),
            'address'        => fake()->streetAddress(),
            'city'           => fake()->city(),
            'email'          => fake()->unique()->companyEmail(),
            'contact_person' => fake()->name(),
            'num_branches'   => fake()->numberBetween(1, 10),
            'status'         => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'inactive']);
    }

    public function pending(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'pending']);
    }
}
