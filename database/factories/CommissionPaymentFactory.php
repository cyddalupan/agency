<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Commission;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionPaymentFactory extends Factory
{
    protected $model = \App\Models\CommissionPayment::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'commission_id' => Commission::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'payment_date' => $this->faker->date(),
            'reference_no' => $this->faker->optional()->bothify('CP-####-????'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
