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
            'agency_id'    => Agency::factory(),
            'account_id'   => Account::factory(),
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
