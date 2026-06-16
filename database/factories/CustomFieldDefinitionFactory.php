<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CustomFieldDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomFieldDefinitionFactory extends Factory
{
    protected $model = CustomFieldDefinition::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'agency_id' => Agency::factory(),
            'model_type' => 'Employer',
            'name' => ucfirst($name),
            'key' => Str::slug($name),
            'type' => $this->faker->randomElement(['text', 'textarea', 'number', 'date', 'select', 'checkbox', 'url']),
            'options' => null,
            'required' => false,
            'order' => 0,
        ];
    }

    public function ofType(string $modelType): static
    {
        return $this->state(fn() => ['model_type' => $modelType]);
    }

    public function select(array $options): static
    {
        return $this->state(fn() => [
            'type' => 'select',
            'options' => $options,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn() => ['required' => true]);
    }
}
