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
            'commission_id' => Commission::factory(),
            // Card 6b0363676e4f389b5b3fd002: derive from the parent. The FK is
            // declared first on purpose: expandAttributes() evaluates closures
            // against the definition captured BEFORE the first pass, so a
            // closure above its parent would see the unresolved factory.
            'agency_id' => fn (array $a) => Commission::find($a['commission_id'] ?? null)?->agency_id ?? Agency::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'payment_date' => $this->faker->date(),
            'reference_no' => $this->faker->optional()->bothify('CP-####-????'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
