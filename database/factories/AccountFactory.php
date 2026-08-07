<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'parent_id' => null,
            'name'      => $this->faker->words(2, true),
            'type'      => $this->faker->randomElement(['income', 'expense']),
            'is_active' => true,
        ];
    }
}
