<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'company_no' => $this->faker->unique()->numerify('CMP-####'),
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'contact' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'status' => 'active',
        ];
    }
}
