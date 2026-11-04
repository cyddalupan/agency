<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CYD 2026-11-04 — Main Office / Main Branch users can now tag each expense line
 * with its own Branch (they file for several branches at once). Branch-locked
 * users don't need it: their branch is always the request's branch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_request_items', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('expense_request_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('expense_request_items', function (Blueprint $table) {
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
