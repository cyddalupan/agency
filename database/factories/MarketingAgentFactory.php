<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\MarketingAgency;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketingAgentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'marketing_agency_id' => MarketingAgency::factory(),
            'name' => $this->faker->name(),
            'contact' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'status' => 'active',
        ];
    }
}
