<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing foreign key first
        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
        });

        // Make it nullable and add back the FK
        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('agency_id')->nullable()->change();
            $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('agency_id')->nullable(false)->change();
            $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
        });
    }
};
