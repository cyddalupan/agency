<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\Applicant;
use App\Models\StartingBalance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StartingBalanceFactory extends Factory
{
    protected $model = StartingBalance::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'user_id'   => User::factory(),
            'agent_id'  => Agent::factory(),
            'applicant_id' => null,
            'date'      => $this->faker->date(),
            'account'   => StartingBalance::ACCOUNT,
            'amount'    => $this->faker->randomFloat(2, 100, 500000),
            'particular' => $this->faker->sentence(),
        ];
    }
}
