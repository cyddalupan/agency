<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('status_transitions')) {
            Schema::create('status_transitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('from_code');
                $table->unsignedSmallInteger('to_code');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['from_code', 'to_code']);
                $table->foreign('from_code')->references('code')->on('status_codes')->cascadeOnDelete();
                $table->foreign('to_code')->references('code')->on('status_codes')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status_transitions');
    }
};
