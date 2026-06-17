<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantWorkExperienceFactory extends Factory
{
    protected $model = \App\Models\ApplicantWorkExperience::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'applicant_id' => Applicant::factory(),
            'company' => $this->faker->company(),
            'position' => $this->faker->jobTitle(),
            'from_date' => $this->faker->date(),
            'to_date' => $this->faker->date(),
            'date_from' => $this->faker->date(),
            'date_to' => $this->faker->date(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'responsibilities' => $this->faker->optional()->paragraph(),
        ];
    }
}
