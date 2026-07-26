<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement(['applicant_report', 'statistics', 'transactions']),
            'config' => [
                'columns' => ['name', 'status', 'country', 'created_at'],
                'group_by' => null,
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'date_preset' => null,
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attrs) => ['is_active' => false]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn(array $attrs) => ['type' => $type]);
    }
}
