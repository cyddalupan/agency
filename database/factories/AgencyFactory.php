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

    /**
     * An agency with the QMS feature switched on (card 6afb2e83).
     *
     * QMS is off by default everywhere except the agencies in
     * ModuleAccess::QMS_DEFAULT_ENABLED_AGENCIES, so a test that exercises a
     * QMS screen has to ask for it explicitly rather than inheriting it from
     * whichever agency happened to be created.
     */
    public function withQms(): static
    {
        return $this->state(fn(array $attrs) => [
            'settings' => [\App\Support\ModuleAccess::QMS_SETTINGS_KEY => true],
        ]);
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
