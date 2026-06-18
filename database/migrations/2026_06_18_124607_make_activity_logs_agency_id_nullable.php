<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Drop the FK constraint
            $table->dropForeign(['agency_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            // Change to nullable
            $table->unsignedBigInteger('agency_id')->nullable()->change();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            // Re-add the FK constraint (now nullable)
            $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['agency_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            // Need to fill nulls first if rolling back
            DB::table('activity_logs')->whereNull('agency_id')->update(['agency_id' => 0]);
            $table->unsignedBigInteger('agency_id')->nullable(false)->change();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('agency_id')->references('id')->on('agencies')->cascadeOnDelete();
        });
    }
};
