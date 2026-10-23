<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * (Cyd 2026-10-23) One expense request can hold several transactions (line
 * items), but they get approved one at a time — so each line item needs its own
 * status. The request keeps a rolled-up status for the dashboards/reports.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_request_items', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('payment');
        });

        // Backfill: every existing line item inherits its request's status.
        // Portable across MySQL + SQLite (tests), so no UPDATE ... JOIN.
        DB::table('expense_requests')
            ->select('id', 'status')
            ->orderBy('id')
            ->chunk(500, function ($requests) {
                foreach ($requests as $request) {
                    DB::table('expense_request_items')
                        ->where('expense_request_id', $request->id)
                        ->update(['status' => $request->status ?: 'pending']);
                }
            });
    }

    public function down(): void
    {
        Schema::table('expense_request_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
