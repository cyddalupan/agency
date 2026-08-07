<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_omas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->date('released_date')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });

        Schema::create('applicant_owwas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->date('released_date')->nullable();
            $table->date('local_flight_date')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_owwas');
        Schema::dropIfExists('applicant_omas');
    }
};
