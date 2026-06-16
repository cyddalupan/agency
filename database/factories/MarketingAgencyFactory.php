<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketingAgencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'contact' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'commission_rate' => $this->faker->randomFloat(2, 1, 50),
            'status' => 'active',
        ];
    }
}
