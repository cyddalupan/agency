<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantEducationFactory extends Factory
{
    protected $model = \App\Models\ApplicantEducation::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'applicant_id' => Applicant::factory(),
            'level' => $this->faker->randomElement(['College', 'Vocational', 'High School']),
            'school' => $this->faker->company(),
            'degree' => $this->faker->randomElement(['BS Computer Science', 'BS Business Administration', 'BS Accountancy']),
            'course' => $this->faker->randomElement(['Computer Science', 'Business Management', 'Accounting']),
            'year_start' => $this->faker->numberBetween(2000, 2015),
            'year_end' => $this->faker->numberBetween(2016, 2025),
            'year_graduated' => $this->faker->numberBetween(2016, 2025),
            'remarks' => $this->faker->sentence(),
        ];
    }
}
