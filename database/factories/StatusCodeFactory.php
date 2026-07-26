<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StatusCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numberBetween(1, 999),
            'label' => fake()->word(),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
