<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee Settings (same card): the agency maintains its own list of
 * Positions/Designations, Departments and Job Descriptions. These feed the
 * dropdowns on the Employee form and do not touch the shared reference tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agency_id')->index();
            // position | department | job_description
            $table->string('type', 32);
            $table->string('name', 191);
            $table->text('description')->nullable(); // used by job_description rows
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['agency_id', 'type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_settings');
    }
};
