<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sponsor>
 */
class SponsorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agency_id'      => Agency::factory(),
            'id_number'      => 'SPONSOR-' . fake()->unique()->randomNumber(6),
            'company_name'   => fake()->company(),
            'contact_person' => fake()->name(),
            'email'          => fake()->unique()->companyEmail(),
            'contact_no'     => '+63' . fake()->numerify('9#########'),
            'viber'          => '+63' . fake()->numerify('9#########'),
            'address'        => fake()->streetAddress(),
            'city'           => fake()->city(),
            'status'         => 'active',
        ];
    }
}
