<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Bill;
use App\Models\Employer;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            // Card 6b0363676e4f389b5b3fd002: employer and applicant must live in
            // the bill's own agency or TenantScope hides them from the bill.
            // expandAttributes() evaluates these closures against the already
            // expanded definition, so a test's explicit agency_id wins and is
            // what the children follow. An explicit employer_id/applicant_id
            // override still wins over the closure.
            'employer_id' => fn (array $a) => Employer::factory()->create(['agency_id' => $a['agency_id']])->id,
            'applicant_id' => fn (array $a) => Applicant::factory()->create(['agency_id' => $a['agency_id']])->id,
            'employer_cost' => $this->faker->randomFloat(2, 10000, 200000),
            'applicant_cost' => $this->faker->randomFloat(2, 1000, 20000),
            'employer_deposit' => $this->faker->randomFloat(2, 0, 50000),
            'applicant_deposit' => $this->faker->randomFloat(2, 0, 5000),
            'status' => 'pending',
            'due_date' => $this->faker->optional()->date(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
