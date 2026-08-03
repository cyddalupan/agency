<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies');
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('proficiency')->nullable(); // beginner|intermediate|expert
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_languages');
    }
};
