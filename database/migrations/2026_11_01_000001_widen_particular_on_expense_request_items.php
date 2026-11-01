<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cyd 2026-11-01 — bulk upload 500 on a long Particular:
 *   SQLSTATE[22001] String data, right truncated: 1406 Data too long for
 *   column 'particular' at row 1
 *
 * `expense_request_items.particular` was varchar(255) while the same field on
 * receivables is already TEXT, so a genuine description (client notes pasted
 * from email/sheet, easily 300+ chars) blew up the import with a 500.
 * Widen it to TEXT so long particulars are stored instead of crashing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_request_items', function (Blueprint $table) {
            $table->text('particular')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('expense_request_items', function (Blueprint $table) {
            $table->string('particular', 255)->nullable()->change();
        });
    }
};
