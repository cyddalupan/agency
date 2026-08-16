<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft deletes for expense requests and receivables so the deletion
     * reason (recorded in the history tables' `note` column) survives as
     * an audit trail.
     */
    public function up(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('receivables', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('receivables', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
