<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('model_type'); // employer, applicant, bill, etc.
            $table->string('name'); // human-readable label
            $table->string('key'); // machine name (slug)
            $table->string('type'); // text, textarea, number, date, select, checkbox, url
            $table->json('options')->nullable(); // for select: ["Option A", "Option B"]
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['agency_id', 'model_type', 'key']);
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_definition_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->morphs('model');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(
                ['custom_field_definition_id', 'model_type', 'model_id'],
                'cfv_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
    }
};
