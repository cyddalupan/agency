<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cyd 2026-11-01 (card 124) — Admins may delete transactions from the Agent
 * Report Deductions and Payments tabs. Deleting a deduction must stay
 * auditable, so instead of a hard delete the row is soft-deleted and stamped
 * with who deleted it and why.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_deductions', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
            $table->text('delete_reason')->nullable()->after('deleted_by');
        });
    }

    public function down(): void
    {
        Schema::table('agent_deductions', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'deleted_by', 'delete_reason']);
        });
    }
};
