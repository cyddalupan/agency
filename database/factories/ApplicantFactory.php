<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\StatusCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id'   => Agency::factory(),
            'first_name'  => fake()->firstName(),
            'last_name'   => fake()->lastName(),
            'birthdate'   => fake()->date(max: '-18 years'),
            'gender'      => fake()->randomElement(['male', 'female']),
            'contact'     => fake()->phoneNumber(),
            'email'       => fake()->safeEmail(),
            'status_code' => 0,
            'status'      => 'active',
        ];
    }

    public function withStatus(int $code): static
    {
        return $this->state(fn(array $attrs) => ['status_code' => $code]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'inactive']);
    }
}
