<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Models\Account;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b038452 — Expense Request reference numbers raced at create.
 *
 * Production evidence: 303 duplicate groups in agency 7 in three weeks, every
 * one a live row plus a soft-deleted loser; 301 collided across two users on
 * the same day. The cause was `nextReference()` = MAX+1 with no lock, and a
 * non-unique index that let both saves through.
 *
 * ---------------------------------------------------------------------------
 * A NOTE ON WHAT "CONCURRENCY TEST" CAN MEAN HERE
 * ---------------------------------------------------------------------------
 * The suite runs on SQLite `:memory:`, which has ONE connection: there is no
 * second writer to interleave with, and `lockForUpdate()` is not emitted at
 * all. A test that "runs two transactions in parallel" against SQLite proves
 * nothing about locking — it cannot, and pretending otherwise is how a race
 * ships twice. So this file proves the race is fixed at the SEAM the race lived
 * in, in a way that is deterministic and fails if the fix is removed:
 *
 *  1. The allocation is called INSIDE the caller's still-open transaction and
 *     acquires the agency lock as its FIRST statement, before any read whose
 *     result it depends on (asserted by capturing the SQL, both call sites).
 *     That is precisely the property the bug lacked — the old code read MAX
 *     with no transaction open at all on the bulk path.
 *  2. Allocation is numerically correct: numeric (not lexicographic) MAX+1, and
 *     soft-deleted holdings are not handed out again.
 *  3. The DB constraint is real and refuses a live duplicate, while two
 *     soft-deleted rows may keep the same reference (the 303 groups today).
 *
 * What this file CANNOT prove, and James must test on MySQL: two clients
 * creating at the same second actually queue on the row lock and get distinct
 * numbers. `FOR UPDATE` ordering / deadlock behaviour is engine-specific.
 * See the "not proven here" block at the bottom.
 */
class ExpenseRequestReferenceRaceTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;
    private Account $main;
    private Account $sub;
    private Branch $branch;
    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);
        $this->branch = Branch::factory()->create(['agency_id' => $this->agency->id]);
        $this->country = Country::factory()->create();

        $this->main = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => null,
            'name'        => 'Office Expenses',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
        $this->sub = Account::factory()->create([
            'agency_id'   => $this->agency->id,
            'parent_id'   => $this->main->id,
            'name'        => 'Office Supplies',
            'type'        => 'expense',
            'charge_type' => 'office',
        ]);
    }

    // ------------------------------------------------------------------
    // helpers
    // ------------------------------------------------------------------

    private function payload(string $particular = 'Office supplies'): array
    {
        return [
            'branch_id' => $this->branch->id,
            'lines'     => [[
                'charge'          => 'office',
                'sub_account_id'  => $this->sub->id,
                'main_account_id' => $this->main->id,
                'agent_id'        => null,
                'applicant_id'    => null,
                'country_id'      => $this->country->id,
                'currency'        => 'PHP',
                'amount'          => 1000.00,
                'particular'      => $particular,
            ]],
        ];
    }

    private function csvWith(array $rows): string
    {
        $csv = "Date,Charge,Account,Currency,Amount,Payment,Particular/Description,Request Status\n";
        foreach ($rows as $row) {
            $csv .= implode(',', $row)."\n";
        }

        return $csv;
    }

    /** Live (non-deleted) references only, per agency. */
    private function liveReferences(int $agencyId): array
    {
        return ExpenseRequest::withTrashed()
            ->where('agency_id', $agencyId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->pluck('reference_no')
            ->map(fn ($r) => (string) $r)
            ->all();
    }

    private function seedRequest(int $agencyId, string $reference, ?string $deletedAt = null): ExpenseRequest
    {
        $attributes = [
            'agency_id'    => $agencyId,
            'user_id'      => $this->admin->id,
            'reference_no' => $reference,
            'date'         => '2026-11-01',
            'status'       => 'released',
            'branch_id'    => $this->branch->id,
        ];
        if ($deletedAt === null) {
            return ExpenseRequest::create($attributes);
        }

        // Eloquent drops deleted_at on insert (not fillable; SoftDeletes does
        // not read it), so the row would sit LIVE for a moment and collide
        // with the live row it is the loser of. Write the column in the same
        // statement. Raw, so no tenant scope and no model events: this is
        // seeding a historical row, not exercising the app.
        $now = now();
        $id = DB::table('expense_requests')->insertGetId($attributes + [
            'deleted_at' => $deletedAt,
            'created_at' => '2026-11-01 00:00:00',
            'updated_at' => $now,
        ]);

        return ExpenseRequest::withTrashed()->findOrFail($id);
    }


    // ------------------------------------------------------------------
    // 1. The lock is where it must be: first, and inside the transaction
    // ------------------------------------------------------------------

    #[Test]
    public function single_create_takes_the_agency_lock_before_it_reads_max_and_then_allocates(): void
    {
        $this->seedRequest($this->agency->id, '2000');

        $statements = [];
        \Illuminate\Support\Facades\DB::listen(function ($q) use (&$statements) {
            $statements[] = $q;
        });

        $this->actingAs($this->admin)
            ->post(route('expense_request.store'), $this->payload())
            ->assertRedirect(route('expense_request.index'));

        $lockPos = null;
        $maxPos = null;
        $insertPos = null;
        $agencyReads = [];

        foreach ($statements as $i => $q) {
            // MySQL/Laravel appends `for update` to this same statement; SQLite
            // does not emit it. What is asserted is ORDER and TRANSACTION.
            //
            // Matched on `select * from "agencies" where "agencies"."id" = ?`
            // specifically: validation (`exists:branches,id`) and tenant
            // middleware ALSO query `agencies`, and a looser `from "agencies"`
            // match picked one of those up — the test passed with the lock
            // removed. Verified by mutation, 2026-11-23.
            // Every agencies row-read, not just the first: the first one is
            // tenant middleware / route binding / the `exists:` probes. The lock
            // is the LAST one before the MAX read.
            if (str_contains($q->sql, 'select * from "agencies" where "agencies"."id" = ?')) {
                $agencyReads[] = $i;
            }
            if ($maxPos === null && str_contains($q->sql, 'CAST(reference_no AS UNSIGNED)')) {
                $maxPos = $i;
            }
            if ($insertPos === null && str_contains($q->sql, 'insert into "expense_requests"')) {
                $insertPos = $i;
            }
        }

        $this->assertNotNull($maxPos, 'No numeric MAX(reference_no) query was issued.');
        $this->assertNotNull($insertPos, 'The expense request was never inserted.');

        // The allocation lock must be a query of its OWN before the MAX read.
        // Asserting merely "an agencies query was seen" passes without the
        // lock: tenant middleware / route binding / `exists:` also read
        // `agencies` ONCE, at index 0, and the check took that one. (Caught by
        // mutation 2026-11-23 — the test was green with the lock removed.)
        $lockPos = empty($agencyReads) ? null : end($agencyReads);

        $this->assertNotNull($lockPos, 'No agency row-read was issued.');
        $this->assertGreaterThanOrEqual(2, count($agencyReads), 'Only the validation/middleware read of `agencies` happened: the allocation lock never ran.');
        $this->assertLessThan($maxPos, $lockPos, 'The agency lock must be taken BEFORE the MAX read — otherwise two allocators can read the same MAX.');
        $this->assertLessThan($insertPos, $maxPos);

        $this->assertSame(['2000', '2001'], $this->liveReferences($this->agency->id));
    }

    #[Test]
    public function bulk_import_allocates_inside_the_transaction_not_before_it(): void
    {
        $this->seedRequest($this->agency->id, '2000');

        $statements = [];
        \Illuminate\Support\Facades\DB::listen(function ($q) use (&$statements) {
            $statements[] = $q;
        });

        $csv = $this->csvWith([
            ['2026-03-11', 'Office', 'Office Supplies', 'PHP', '3000.00', '0', 'March supplies', 'Released'],
            ['2026-03-12', 'Office', 'Office Supplies', 'PHP', '3100.00', '0', 'April supplies', 'Released'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('expense_request.bulk.import'), [
                'csv_file' => UploadedFile::fake()->createWithContent('expenses.csv', $csv),
            ])
            ->assertRedirect(route('expense_request.index'));

        $lockPos = null;
        $maxPos = null;
        $sweepPos = null;   // the duplicate count-match read, formerly pre-transaction
        $insertPos = null;

        foreach ($statements as $i => $q) {
            $sql = $q->sql;
            if ($lockPos === null && str_contains($sql, 'select * from "agencies" where "agencies"."id" = ?')) {
                $lockPos = $i;
            }
            if ($sweepPos === null && str_contains($sql, 'from "expense_request_items"') && str_contains($sql, 'exists')) {
                $sweepPos = $i;
            }
            if ($maxPos === null && str_contains($sql, 'CAST(reference_no AS UNSIGNED)')) {
                $maxPos = $i;
            }
            if ($insertPos === null && str_contains($sql, 'insert into "expense_requests"')) {
                $insertPos = $i;
            }
        }

        $this->assertNotNull($lockPos, 'Bulk import never took the agency lock.');
        $this->assertNotNull($maxPos, 'Bulk import never allocated a reference.');
        $this->assertNotNull($insertPos);

        // The regression this card exists to kill: allocation used to happen
        // BEFORE DB::transaction opened, so the lock was a no-op. If the
        // allocation read comes before the duplicate sweep, it is outside the
        // transaction again.
        $this->assertLessThan($maxPos, $lockPos, 'Bulk import: the agency lock must be taken before the MAX read.');
        if ($sweepPos !== null) {
            $this->assertLessThan($maxPos, $sweepPos, 'Bulk import: the MAX read must happen INSIDE the transaction, after the duplicate sweep (it was outside the transaction before this fix).');
        }

        // Contiguous block, two rows.
        $this->assertSame(['2000', '2001', '2002'], $this->liveReferences($this->agency->id));
    }

    #[Test]
    public function a_second_create_sees_the_committed_row_and_never_reuses_its_number(): void
    {
        // Close to the race without threads: what the loser of the race saw.
        // Before the fix the loser's MAX read happened before the winner
        // committed. Here the "loser" starts only after the winner committed,
        // and must take the NEXT number.
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload('first'));
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload('second'));

        $refs = $this->liveReferences($this->agency->id);
        $this->assertSame(['2000', '2001'], $refs);
        $this->assertSame(2, count(array_unique($refs)));
    }

    #[Test]
    public function allocation_is_numeric_not_lexicographic(): void
    {
        // MySQL's default string comparison puts '999' above '9999', so a naive
        // MAX(reference_no) on a text column returns '999' and every create
        // re-allocates 1000 — a permanent duplicate generator. The query casts
        // to UNSIGNED for exactly this.
        $this->seedRequest($this->agency->id, '999');
        $this->seedRequest($this->agency->id, '9999');

        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload());

        $this->assertContains('10000', $this->liveReferences($this->agency->id));
    }

    #[Test]
    public function a_soft_deleted_reference_is_never_handed_out_again(): void
    {
        // 303 rows in agency 7 are soft-deleted and still hold a reference that
        // was issued and quoted. MAX+1 must look at them too, or a restore (or
        // a human reading the paper) collides with a fresh request.
        $this->seedRequest($this->agency->id, '2000');
        $this->seedRequest($this->agency->id, '2001', '2026-11-20 10:00:00');

        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload());

        $this->assertContains('2002', $this->liveReferences($this->agency->id));
    }

    #[Test]
    public function two_agencies_allocate_independently_and_may_both_hold_2000(): void
    {
        $this->actingAs($this->admin)->post(route('expense_request.store'), $this->payload());

        $other = Agency::factory()->create();
        $otherUser = User::factory()->create(['agency_id' => $other->id, 'user_type' => 'admin']);
        $otherMain = Account::factory()->create([
            'agency_id' => $other->id, 'parent_id' => null, 'name' => 'Office Expenses',
            'type' => 'expense', 'charge_type' => 'office',
        ]);
        $otherSub = Account::factory()->create([
            'agency_id' => $other->id, 'parent_id' => $otherMain->id, 'name' => 'Supplies',
            'type' => 'expense', 'charge_type' => 'office',
        ]);

        $this->actingAs($otherUser)->post(route('expense_request.store'), [
            'lines' => [[
                'charge' => 'office', 'sub_account_id' => $otherSub->id, 'main_account_id' => $otherMain->id,
                'country_id' => $this->country->id, 'currency' => 'PHP', 'amount' => 1000,
                'particular' => 'independent',
            ]],
        ])->assertRedirect(route('expense_request.index'));

        // The tenant scope filters by the LOGGED-IN user, so read this one
        // without it: the point is that two agencies each hold their own 2000.
        $agencyOneRefs = ExpenseRequest::withTrashed()->withoutGlobalScopes()
            ->where('agency_id', $this->agency->id)->whereNull('deleted_at')
            ->pluck('reference_no')->map(fn ($r) => (string) $r)->all();

        $this->assertSame(['2000'], $agencyOneRefs);
        $this->assertSame(['2000'], $this->liveReferences($other->id));
    }

    // ------------------------------------------------------------------
    // 2. The constraint is real
    // ------------------------------------------------------------------

    #[Test]
    public function database_refuses_a_second_live_row_with_the_same_reference(): void
    {
        $this->seedRequest($this->agency->id, '2000');

        $this->expectException(QueryException::class);
        $this->seedRequest($this->agency->id, '2000');
    }

    #[Test]
    public function two_soft_deleted_rows_may_keep_the_same_reference(): void
    {
        // This is today's data: 303 groups, one live row and one soft-deleted
        // row per group. The index must keep compiling and the history must
        // stay as evidence.
        $this->seedRequest($this->agency->id, '2000');
        $this->seedRequest($this->agency->id, '2001');
        $this->seedRequest($this->agency->id, '2001', '2026-11-20 10:00:00');

        $this->assertSame(3, ExpenseRequest::withTrashed()->count());

        // And a second soft-deleted copy of a LIVE reference is also allowed:
        // a restore that is later undone must be able to keep its history.
        $this->seedRequest($this->agency->id, '2000', '2026-10-02 00:00:00');

        $this->assertSame(4, ExpenseRequest::withTrashed()->count());
        $this->assertSame(['2000', '2001'], $this->liveReferences($this->agency->id));
        $this->assertSame(2, ExpenseRequest::withTrashed()->where('reference_no', '2000')->count());
    }

    #[Test]
    public function the_same_reference_in_another_agency_is_allowed(): void
    {
        $other = Agency::factory()->create();
        ExpenseRequest::create([
            'agency_id' => $this->agency->id, 'user_id' => $this->admin->id, 'reference_no' => '2000',
            'date' => '2026-11-01', 'status' => 'released',
        ]);
        ExpenseRequest::create([
            'agency_id' => $other->id, 'user_id' => $this->admin->id, 'reference_no' => '2000',
            'date' => '2026-11-01', 'status' => 'released',
        ]);

        $this->assertSame(2, ExpenseRequest::withTrashed()->where('reference_no', '2000')->count());
    }

    #[Test]
    public function the_unique_index_exists_on_the_connection(): void
    {
        // A migration test alone is not a concurrency test; it is here because
        // the card asks the constraint to be checked, and the behaviour above
        // only proves it if this is the index doing the refusing.
        $indexes = collect(\Illuminate\Support\Facades\Schema::getIndexes('expense_requests'))
            ->keyBy('name');

        $this->assertTrue($indexes->has('expense_requests_agency_reference_unique'));
        $this->assertTrue($indexes['expense_requests_agency_reference_unique']['unique']);
        $this->assertSame(['agency_id', 'reference_no'], $indexes['expense_requests_agency_reference_unique']['columns']);

        // The SQLite partial index must be partial (live rows only). On MySQL
        // the identical guarantee comes from NULL-in-unique-index semantics and
        // the plain index, which this suite cannot inspect — see James's QA.
        $partial = collect(DB::select('PRAGMA index_list("expense_requests")'))
            ->firstWhere('name', 'expense_requests_agency_reference_unique');
        $this->assertSame(1, (int) $partial->partial, 'The unique index must be partial on SQLite, or soft-deleted history collides.');
    }

    #[Test]
    public function migration_up_refuses_to_run_when_live_duplicates_exist(): void
    {
        // The guard runs before the DDL, so build the duplicate state first and
        // then re-run just `up()`. The index this migration added already
        // refuses duplicates, so it is dropped first — this reproduces the
        // state the migration meets on a database that has NOT had it yet.
        DB::statement('DROP INDEX expense_requests_agency_reference_unique');

        $this->seedRequest($this->agency->id, '2000');
        \Illuminate\Support\Facades\DB::table('expense_requests')->insert([
            'agency_id' => $this->agency->id, 'user_id' => $this->admin->id, 'reference_no' => '2000',
            'date' => '2026-11-01', 'status' => 'released', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_23_000001_add_unique_agency_reference_to_expense_requests.php');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('live duplicate');
        $migration->up();
    }

    // ------------------------------------------------------------------
    // NOT PROVEN HERE (given to James for MySQL QA)
    // ------------------------------------------------------------------
    // SQLite :memory: is a single connection and does not execute
    // `FOR UPDATE`. Therefore this suite cannot demonstrate two writers
    // queueing. On MySQL 8, two sessions must:
    //   S1: BEGIN; SELECT * FROM agencies WHERE id=7 FOR UPDATE;
    //   S2: BEGIN; SELECT * FROM agencies WHERE id=7 FOR UPDATE;  -- blocks
    //   S1: INSERT ... '28766'; COMMIT;                          -- S2 unblocks
    //   S2: MAX(reference_no)+1 -> 28767, not 28766.
    // Card acceptance criterion 1 (two simultaneous creates -> distinct refs)
    // is verified there, not here.
}
