<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CustomFieldDefinition extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'agency_id',
        'model_type',
        'name',
        'key',
        'type',
        'options',
        'required',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'order' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'custom_field_definition_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $field) {
            if (empty($field->key)) {
                $field->key = Str::slug($field->name);
            }
        });
    }
}
