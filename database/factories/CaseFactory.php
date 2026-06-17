<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseFactory extends Factory
{
    protected $model = \App\Models\Cases::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'applicant_id' => Applicant::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'open',
            'priority' => 'normal',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn(array $attrs) => ['status' => 'closed']);
    }

    public function highPriority(): static
    {
        return $this->state(fn(array $attrs) => ['priority' => 'high']);
    }
}
