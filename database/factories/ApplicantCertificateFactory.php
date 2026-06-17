<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantCertificateFactory extends Factory
{
    protected $model = \App\Models\ApplicantCertificate::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'applicant_id' => Applicant::factory(),
            'type' => $this->faker->randomElement(['certification', 'training', 'license']),
            'name' => $this->faker->sentence(3),
            'certificate_no' => $this->faker->bothify('CERT-####-????'),
            'certificate_name' => $this->faker->sentence(3),
            'issued_by' => $this->faker->company(),
            'institution' => $this->faker->company(),
            'issued_date' => $this->faker->date(),
            'date_obtained' => $this->faker->date(),
            'expiry_date' => $this->faker->optional()->date(),
            'file_path' => null,
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
