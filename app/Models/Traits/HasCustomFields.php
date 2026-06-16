<?php

namespace App\Models\Traits;

use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    /**
     * Get the custom field values for this model.
     */
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    /**
     * Get the custom field definitions available for this model type.
     */
    public function customFieldDefinitions()
    {
        $modelType = static::getCustomFieldModelType();

        return CustomFieldDefinition::where('agency_id', $this->agency_id)
            ->where('model_type', $modelType)
            ->orderBy('order')
            ->orderBy('name');
    }

    /**
     * Eager-load custom field values for a collection.
     */
    public function loadCustomFieldValues(): void
    {
        if (! $this->relationLoaded('customFieldValues')) {
            $this->load('customFieldValues.definition');
        }
    }

    /**
     * Get the value for a specific field key.
     */
    public function getCustomField(string $key): mixed
    {
        $this->loadCustomFieldValues();

        $value = $this->customFieldValues
            ->first(fn ($v) => $v->definition?->key === $key);

        return $value?->value;
    }

    /**
     * Set a custom field value.
     */
    public function setCustomField(string $key, mixed $value): void
    {
        $definition = CustomFieldDefinition::where('agency_id', $this->agency_id)
            ->where('model_type', static::getCustomFieldModelType())
            ->where('key', $key)
            ->first();

        if (! $definition) {
            return;
        }

        $this->customFieldValues()->updateOrCreate(
            ['custom_field_definition_id' => $definition->id],
            ['value' => $value]
        );
    }

    /**
     * Sync an array of [key => value] pairs from a request.
     */
    public function syncCustomFields(array $data): void
    {
        $definitions = $this->customFieldDefinitions()->get();

        foreach ($definitions as $definition) {
            $value = $data[$definition->key] ?? null;

            if ($value === '' || $value === null) {
                $this->customFieldValues()
                    ->where('custom_field_definition_id', $definition->id)
                    ->delete();
            } else {
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                $this->customFieldValues()->updateOrCreate(
                    ['custom_field_definition_id' => $definition->id],
                    ['value' => (string) $value]
                );
            }
        }
    }

    /**
     * Define the model type key used in custom_field_definitions.
     */
    public static function getCustomFieldModelType(): string
    {
        return (new \ReflectionClass(static::class))->getShortName();
    }
}
