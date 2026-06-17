<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantReferenceFactory extends Factory
{
    protected $model = \App\Models\ApplicantReference::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'applicant_id' => Applicant::factory(),
            'name' => $this->faker->name(),
            'position' => $this->faker->jobTitle(),
            'company' => $this->faker->company(),
            'contact' => $this->faker->phoneNumber(),
            'relation' => $this->faker->randomElement(['Colleague', 'Manager', 'Supervisor', 'Client']),
        ];
    }
}
