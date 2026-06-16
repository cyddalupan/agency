<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'bill_id' => Bill::factory(),
            'amount' => $this->faker->randomFloat(2, 500, 100000),
            'category' => $this->faker->randomElement(['employer_cost', 'applicant_cost', 'deposit', 'commission']),
            'type' => $this->faker->randomElement(['cash', 'bank_transfer', 'check', 'gcash', 'online']),
            'reference_no' => $this->faker->optional()->bothify('REF-####-????'),
            'status' => 'pending',
            'payment_date' => $this->faker->optional()->date(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
