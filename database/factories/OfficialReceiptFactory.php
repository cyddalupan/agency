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
            'payment_id' => Payment::factory(),
            // Card 6b0363676e4f389b5b3fd002: derive from the parent. The FK is
            // declared first on purpose: expandAttributes() evaluates closures
            // against the definition captured BEFORE the first pass, so a
            // closure above its parent would see the unresolved factory.
            'agency_id' => fn (array $a) => Payment::find($a['payment_id'] ?? null)?->agency_id ?? Agency::factory(),
            'or_no' => 'OR-' . $this->faker->unique()->numerify('########'),
            'amount' => $this->faker->randomFloat(2, 1000, 100000),
            'issue_date' => $this->faker->date(),
            'issued_to' => $this->faker->randomElement(['employer', 'applicant', 'agent']),
            'issued_to_name' => $this->faker->name(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
