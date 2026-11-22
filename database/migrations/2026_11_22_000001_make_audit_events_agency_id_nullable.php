<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Card 6b0341cf42a2071fc8b38ea5 (§4.5): `audit_events.agency_id` nullable.
 *
 * A god-mode event (login, logout, account creation) is a control-plane event:
 * it belongs to the operator and to no agency. Rather than a second
 * `godmode_events` table — a second spine, a second screen, a second export —
 * the existing column becomes nullable and the rows carry NULL. The
 * `super_admin` tenant-scope bypass already reaches them, and the Audit Trail
 * shows them to `super_admin` only.
 *
 * No backfill: existing rows keep their agency, and no id is invented for
 * history that never had one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_events', function (Blueprint $table) {
            $table->foreignId('agency_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Reverting would have to delete or re-attribute control-plane rows;
        // this migration is not reversed in place.
        Schema::table('audit_events', function (Blueprint $table) {
            $table->foreignId('agency_id')->nullable(false)->change();
        });
    }
};
