<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['agency_id', 'key']);
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('model_type'); // applicant, employer, job
            $table->string('field_name');
            $table->string('field_label');
            $table->string('field_type')->default('text'); // text, textarea, select, date, file
            $table->text('options')->nullable(); // JSON for select options
            $table->boolean('required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('agency_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('settings');
    }
};
