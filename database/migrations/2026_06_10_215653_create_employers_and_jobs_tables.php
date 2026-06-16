<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('company_no')->nullable();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->decimal('commission', 10, 2)->nullable()->default(0);
            $table->decimal('agent_commission', 10, 2)->nullable()->default(0);
            $table->string('commission_type')->nullable(); // percentage, fixed
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('agency_id');
        });

        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('name')->nullable();
            $table->text('content')->nullable();
            $table->string('gender_preference')->nullable(); // male, female, any
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('salary_currency', 3)->nullable()->default('PHP');
            $table->integer('total_slots')->default(0);
            $table->integer('occupied')->default(0);
            $table->string('status')->default('open'); // open, closed, filled
            $table->timestamps();

            $table->index('agency_id');
            $table->index('employer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_positions');
        Schema::dropIfExists('employers');
    }
};
