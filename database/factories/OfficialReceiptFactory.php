<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficialReceiptFactory extends Factory
{
    protected $model = \App\Models\OfficialReceipt::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'payment_id' => Payment::factory(),
            'or_no' => 'OR-' . $this->faker->unique()->numerify('########'),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'issue_date' => $this->faker->date(),
            'issued_to' => $this->faker->randomElement(['employer', 'applicant', 'agent']),
            'issued_to_name' => $this->faker->name(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
