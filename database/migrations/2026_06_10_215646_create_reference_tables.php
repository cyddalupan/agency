<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === Shared Reference Tables (shared across all agencies) ===

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->nullable();
            $table->string('nationality')->nullable();
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('religions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('civil_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('status_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique();
            $table->string('label');
            $table->string('label_saudi')->nullable(); // Saudi-specific label
            $table->text('description')->nullable();
            $table->string('color')->nullable(); // display color
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_codes');
        Schema::dropIfExists('civil_statuses');
        Schema::dropIfExists('religions');
        Schema::dropIfExists('nationalities');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('countries');
    }
};
