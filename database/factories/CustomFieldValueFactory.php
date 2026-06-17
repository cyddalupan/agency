<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldValueFactory extends Factory
{
    protected $model = CustomFieldValue::class;

    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'custom_field_definition_id' => CustomFieldDefinition::factory(),
            'model_type' => 'Employer',
            'model_id' => \App\Models\Employer::factory(),
            'value' => $this->faker->sentence(),
        ];
    }
}
