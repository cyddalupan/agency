<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentDeductionFactory extends Factory
{
    protected $model = AgentDeduction::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'user_id'   => User::factory(),
            'agent_id'  => Agent::factory(),
            'applicant_id' => null,
            'date'      => $this->faker->date(),
            'account'   => $this->faker->randomElement(AgentDeduction::ACCOUNTS),
            'amount'    => $this->faker->randomFloat(2, 100, 50000),
            'particular' => $this->faker->sentence(),
        ];
    }
}
