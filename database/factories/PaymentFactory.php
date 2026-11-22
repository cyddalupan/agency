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
            'bill_id' => Bill::factory(),
            // Card 6b0363676e4f389b5b3fd002: the payment belongs to the bill's
            // agency; a bill-less payment still gets its own.
            //
            // ORDER MATTERS. Laravel's expandAttributes() resolves every
            // factory attribute in a first pass, then evaluates the closures
            // in a second pass against the definition array as it stood BEFORE
            // the first pass. A closure on an attribute declared above its
            // parent therefore receives the unresolved BillFactory object, not
            // an id. Declare the FK first and read it here.
            'agency_id' => fn (array $a) => Bill::find($a['bill_id'] ?? null)?->agency_id ?? Agency::factory(),
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
