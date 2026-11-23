<?php

use App\Support\Db\ExpenseRequestLiveReferenceKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Card 6b038452 — expense request reference numbers raced at create.
 *
 * `nextReference()` was MAX+1 with no lock, so two operators in one agency got
 * the same number: 303 duplicate groups in agency 7 in three weeks, each one a
 * live row plus a soft-deleted loser. The controller now serialises allocation
 * (lock on the agency row + numeric MAX + withTrashed); this index is the last
 * line of defence if some future code path forgets the lock.
 *
 * Why it compiles against production data, measured 2026-11-23:
 *  - All 303 groups have exactly one live row and one soft-deleted row.
 *  - The constraint is "one LIVE row per (agency_id, reference_no)"; any
 *    number of soft-deleted rows may keep the same reference.
 *  - Nobody's issued reference changes: no renumbering, no hard delete, no
 *    reference-change log. The deleted losers stay as evidence.
 *
 * How each engine expresses "live rows only":
 *  - SQLite supports partial indexes: `WHERE deleted_at IS NULL`. Verified on
 *    SQLite 3.45.1 — rejects a second live row, accepts a deleted copy.
 *  - MySQL 8 has NO partial indexes. Its NULL-in-unique-index rule ("NULLs are
 *    distinct") is NOT a substitute: `(agency_id, reference_no)` does not
 *    contain `deleted_at`, so a live row (deleted_at NULL) and a soft-deleted
 *    row are identical on the indexed columns and the plain index FAILS with
 *    ERROR 1062 on the 303 existing groups. Tested on MySQL 8.0.46, 2026-11-23.
 *    Also tested and rejected: `UNIQUE (agency_id, reference_no, deleted_at)`
 *    builds but constrains nothing — two live rows both have NULL deleted_at,
 *    which MySQL treats as distinct, and a second live duplicate inserts fine.
 *    What works (verified on 8.0.46, all three cases) is the functional-index
 *    form: `(agency_id, (IF(deleted_at IS NULL, reference_no, NULL)))`, where
 *    soft-deleted rows all yield a NULL key and are therefore unconstrained.
 *    MySQL 8.0.13+ supports functional indexes directly, so no extra column
 *    is added to a 27k-row table.
 *
 * The guard below is deliberate. The DDL's own failure condition is two LIVE
 * rows sharing a reference — a real data defect the index must not paper over.
 * Assert it here so the deploy fails with a named count instead of a raw DDL
 * error, and print which references are affected.
 */
return new class extends Migration
{
    private const INDEX = 'expense_requests_agency_reference_unique';

    private const OLD_INDEX = 'expense_requests_agency_id_reference_no_index';

    public function up(): void
    {
        $liveDuplicates = DB::table('expense_requests')
            ->whereNull('deleted_at')
            ->select('agency_id', 'reference_no', DB::raw('COUNT(*) AS c'))
            ->groupBy('agency_id', 'reference_no')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->get();

        if ($liveDuplicates->isNotEmpty()) {
            $sample = $liveDuplicates
                ->map(fn ($r) => "agency {$r->agency_id} ref {$r->reference_no} x{$r->c}")
                ->implode(', ');

            throw new RuntimeException(
                'Cannot add the unique (agency_id, reference_no) index: live duplicate '
                . 'references exist. Resolve them first; do NOT rename issued references. '
                . 'First offenders: ' . $sample
            );
        }

        $this->addUniqueIndex();
    }

    public function down(): void
    {
        if ($this->isSqlite()) {
            DB::statement('DROP INDEX ' . self::INDEX);
        } else {
            // MySQL: drop only the functional unique index. We chose the
            // expression form over a stored generated column, so there is no
            // column to remove. (If someone switches to the generated-column
            // variant, down() must also drop that column.)
            Schema::table('expense_requests', function (Blueprint $table) {
                $table->dropUnique(self::INDEX);
            });
        }

        if (! $this->isSqlite()) {
            // Put back the non-unique index the create migration declared,
            // under the name it had before `up()` dropped it.
            Schema::table('expense_requests', function (Blueprint $table) {
                $table->index(['agency_id', 'reference_no'], self::OLD_INDEX);
            });
        }
    }

    private function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    private function addUniqueIndex(): void
    {
        if ($this->isSqlite()) {
            DB::statement(
                'CREATE UNIQUE INDEX ' . self::INDEX
                . ' ON expense_requests (agency_id, reference_no) WHERE deleted_at IS NULL'
            );

            return;
        }

        // The original non-unique index (2026_08_05_054153, name
        // expense_requests_agency_id_reference_no_index) is fully covered by
        // the functional one's agency_id prefix plus the equality lookup;
        // leaving both keeps a redundant index on a 27k-row table that takes a
        // write on every insert.
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropIndex(self::OLD_INDEX);
        });

        // Functional key part. Laravel's Blueprint has no API for this on the
        // MySQL grammar that would produce it, so the DDL is literal.
        DB::statement(
            'ALTER TABLE expense_requests ADD UNIQUE INDEX ' . self::INDEX
            . ' (' . ExpenseRequestLiveReferenceKey::mysqlKeyParts() . ')'
        );
    }
};
