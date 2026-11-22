<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValueFactory extends Factory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        return [
            // Card 6b0363676e4f389b5b3fd002: derive from the owning definition;
            // a value with no definition still gets its own agency.
            'agency_id' => fn (array $a) => CustomFieldDefinition::find($a['custom_field_definition_id'] ?? null)?->agency_id ?? Agency::factory(),
            'custom_field_definition_id' => CustomFieldDefinition::factory(),
            'model_type' => 'Employer',
            'model_id' => \App\Models\Employer::factory(),
            'value' => $this->faker->sentence(),
        ];
    }

    /**
     * Card 6b0363676e4f389b5b3fd002: the value belongs to a real model row.
     *
     * `model_id` is polymorphic, so the definition cannot derive it. This state
     * sets the type/id pair explicitly and takes the agency from the same row,
     * so the value and its subject always agree on the tenant.
     */
    public function forModel(Model $model): static
    {
        return $this->state(fn () => [
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'agency_id' => $model->agency_id,
        ]);
    }
}
