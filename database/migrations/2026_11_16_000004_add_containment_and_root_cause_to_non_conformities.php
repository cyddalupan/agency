<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-16 (card 6afa0dad2729295c62765c76): narrative fields the screens need.
 *
 * QMS-15 gave the non-conformity a `description` and a `requirement_breached`,
 * but the detail screen this card specifies has two distinct things to record
 * that had nowhere to go:
 *
 *   - **containment** — what was done immediately to stop the problem getting
 *     worse, before the root cause was known. This is not the description: it
 *     is the emergency brake, and auditors ask for it separately.
 *   - **root cause** — the analysis of *why* it happened, which is the input to
 *     corrective action and is the single most commonly missing piece of a CAPA.
 *
 * Both are nullable and additive, so existing rows and the QMS-15 tests are
 * unaffected. They are separate columns rather than folded into `description`
 * because an auditor needs to read each on its own, and because a status of
 * `root_cause` is meaningless if the reasoning was never written down.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('non_conformities', function (Blueprint $table) {
            $table->text('containment')->nullable()->after('description');
            $table->text('root_cause')->nullable()->after('containment');
        });
    }

    public function down(): void
    {
        Schema::table('non_conformities', function (Blueprint $table) {
            $table->dropColumn(['containment', 'root_cause']);
        });
    }
};
