<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'account_id'   => Account::factory(),
            // Card 6b0363676e4f389b5b3fd002: derive from the parent. The FK is
            // declared first on purpose: expandAttributes() evaluates closures
            // against the definition captured BEFORE the first pass, so a
            // closure above its parent would see the unresolved factory.
            'agency_id'    => fn (array $a) => Account::find($a['account_id'] ?? null)?->agency_id ?? Agency::factory(),
            'user_id'      => User::factory(),
            'amount'       => $this->faker->randomFloat(2, 50, 20000),
            'date'         => $this->faker->date(),
            'payee'        => $this->faker->company(),
            'method'       => $this->faker->randomElement(['cash', 'bank_transfer', 'check', 'gcash', 'online']),
            'reference_no' => $this->faker->optional()->bothify('REF-####'),
            'notes'        => $this->faker->optional()->sentence(),
        ];
    }
}
