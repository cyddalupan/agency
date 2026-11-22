<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Services\GodMode\GodAgencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Card 6b036f25045ef95278e77e5e — hard delete for an EMPTY agency.
 *
 * The promise from 6b0344184136c21243a7af8b §1 was: "Hard delete exists only
 * as a console command, `--force`, and only for an agency with zero
 * dependents." This is that command. The god-mode screen archives; it never
 * deletes.
 *
 *   php artisan agencies:remove-empty 272              (dry run, prints the plan)
 *   php artisan agencies:remove-empty 272 --force=...  (deletes)
 *
 * "Empty" is not re-defined here: it is GodAgencyService::onlyEmpty() (no
 * users AND no applicants), and the dry-run table is GodAgencyService::
 * affectedPeople(). One definition, so the list filter, the confirmation copy
 * and this command can never disagree about what a delete would take.
 *
 * ── Audit trail: the decision ────────────────────────────────────────────
 * `audit_events.agency_id` is cascadeOnDelete (legacy) / nullable since
 * 2026_11_22_000001. Deleting the agency with the model would cascade its
 * audit rows away, i.e. delete the record that this delete happened, which is
 * the one record a destructive command most needs to leave. `activity_logs`
 * would survive but with a dangling agency_id.
 *
 * So: per agency, in the same transaction —
 *   1. its `audit_events` rows are re-homed to agency_id NULL (the god plane's
 *      own home per the parent card) and kept. Append-only evidence is never
 *      rewritten, only moved out of the tenant scope.
 *   2. `activity_logs` rows for the agency are deleted (one is written in
 *      their place below).
 *   3. a "deleted" audit event + "godmode.agency_deleted" activity row are
 *      written with agency_id NULL, subject_label = the agency name, and a
 *      summary of what was removed (id, subdomain, status, users/applicants/
 *      branches counts).
 *   4. the agency row is hard-deleted LAST, so any missed cascade is caught
 *      by the FK inside the transaction instead of mutating partial data.
 *
 * If the actor has no id (console, not a god user), actor_user_id stays NULL —
 * the same rule Tenant::bypassFor() follows. We do not invent a user id.
 *
 * ── Storage ──────────────────────────────────────────────────────────────
 * `agencies.logo` may name a file on the `public` disk. --force refuses when
 * that file exists unless --delete-files is passed, so a file is never left
 * orphaned silently, and by default nothing outside the database is removed.
 */
class RemoveEmptyAgency extends Command
{
    protected $signature = 'agencies:remove-empty
        {agency : Agency id to inspect (or delete with --force)}
        {--force= : Required to delete. Must repeat the agency name exactly, e.g. --force="Test Agency"}
        {--delete-files : Also delete the agency logo file from the public disk (off by default)}
        {--actor= : Id of the god user performing the delete, for the audit rows.}';

    protected $description = 'Hard delete an agency that has no users and no applicants. Dry run unless --force="<name>" is given.';

    public function handle(GodAgencyService $agencies): int
    {
        $id = (int) $this->argument('agency');

        $agency = Agency::withoutGlobalScopes()->find($id);

        if (! $agency) {
            $this->error("Agency {$id} does not exist.");

            return self::FAILURE;
        }

        // The one definition of "empty": the same query behind the list filter.
        $isEmpty = $agencies->onlyEmpty(Agency::withoutGlobalScopes()->whereKey($agency->id))->exists();

        if (! $isEmpty) {
            return $this->refuse($agency, $agencies);
        }

        $counts = $agencies->affectedPeople($agency);
        $dependentTables = $this->dependentTables($agency);

        $this->table(
            ['agency', 'subdomain', 'status', 'applicants', 'users', 'branches', 'other rows'],
            [[
                $agency->name,
                $agency->subdomain ?: '(none)',
                $agency->status,
                $counts['applicants'],
                $counts['users'],
                $counts['branches'],
                array_sum($dependentTables),
            ]]
        );

        if ($dependentTables !== []) {
            $this->newLine();
            $this->line('Other rows that would be removed:');
            foreach ($dependentTables as $table => $n) {
                $this->line("  - {$table}: {$n}");
            }
        }

        $logo = $this->logoFile($agency);
        if ($logo !== null) {
            $this->warn("Logo file exists: {$logo} (only removed with --delete-files).");
        }

        $auditRows = AuditEvent::withoutGlobalScopes()->where('agency_id', $agency->id)->count();
        $this->line("Audit rows for this agency: {$auditRows} (kept; re-homed to agency_id NULL).");

        $force = (string) $this->option('force');

        if ($force === '') {
            $this->newLine();
            $this->info('Dry run: nothing was removed.');
            $this->line("To delete, re-run with --force=\"{$agency->name}\" (exact name, including case).");

            return self::SUCCESS;
        }

        if ($force !== (string) $agency->name) {
            $this->error("--force does not match the agency name. Got \"{$force}\", expected \"{$agency->name}\".");

            return self::FAILURE;
        }

        if ($logo !== null && ! $this->option('delete-files')) {
            $this->error("Logo file {$logo} would be orphaned. Pass --delete-files to remove it, or clear the logo first.");

            return self::FAILURE;
        }

        $actorId = $this->option('actor') !== null && $this->option('actor') !== ''
            ? (int) $this->option('actor')
            : null;

        $this->delete($agency, $counts, $auditRows, $logo !== null, $actorId);

        if ($logo !== null) {
            Storage::disk('public')->delete($logo);
        }

        $this->info("Agency {$id} ({$agency->name}) deleted.");

        return self::SUCCESS;
    }

    private function refuse(Agency $agency, GodAgencyService $agencies): int
    {
        $counts = $agencies->affectedPeople($agency);

        $this->error("Refusing: agency {$agency->id} ({$agency->name}) is not empty.");

        $this->table(['applicants', 'users', 'branches'], [[
            $counts['applicants'],
            $counts['users'],
            $counts['branches'],
        ]]);

        $this->line('Archive it instead, or remove the dependents deliberately. This command never cascades away a tenant.');
        $this->line('Anything with a user would strand staff accounts: users.agency_id is nullOnDelete.');

        return self::FAILURE;
    }

    /**
     * Rows in other agency_id tables that this agency still owns. Empty agency
     * normally means none; anything listed here is cascade-removed with the
     * agency and is shown so the operator sees it before typing --force.
     *
     * @return array<string,int>
     */
    private function dependentTables(Agency $agency): array
    {
        $skip = ['agencies', 'applicants', 'users', 'branches', 'audit_events', 'activity_logs'];
        $rows = [];

        // SQLite (tests) has no information_schema; ask its schema instead.
        if (DB::getDriverName() === 'sqlite') {
            $tables = [];
            foreach (DB::select("select name from sqlite_master where type = 'table' order by name") as $t) {
                $columns = array_map(fn ($c) => $c->name, DB::select("pragma table_info('{$t->name}')"));
                if (in_array('agency_id', $columns, true)) {
                    $tables[] = (object) ['table_name' => $t->name];
                }
            }
        } else {
            $tables = DB::select(
                'select table_name from information_schema.columns where table_schema = database() and column_name = ? order by table_name',
                ['agency_id']
            );
        }

        foreach ($tables as $t) {
            $table = $t->table_name;

            if (in_array($table, $skip, true)) {
                continue;
            }

            $n = DB::table($table)->where('agency_id', $agency->id)->count();
            if ($n > 0) {
                $rows[$table] = $n;
            }
        }

        return $rows;
    }

    private function logoFile(Agency $agency): ?string
    {
        $logo = trim((string) $agency->logo);

        if ($logo === '') {
            return null;
        }

        return Storage::disk('public')->exists($logo) ? $logo : null;
    }

    private function delete(Agency $agency, array $counts, int $auditRows, bool $removeLogo, ?int $actorId): void
    {
        DB::transaction(function () use ($agency, $counts, $auditRows, $removeLogo, $actorId) {
            $payload = [
                'id'             => $agency->id,
                'name'           => $agency->name,
                'subdomain'      => $agency->subdomain,
                'status'         => $agency->status,
                'applicants'     => $counts['applicants'],
                'users'          => $counts['users'],
                'branches'       => $counts['branches'],
                'audit_rows_kept' => $auditRows,
                'logo_removed'   => $removeLogo,
                'deleted_at'     => now()->toIso8601String(),
            ];

            // 1. Keep the agency's audit trail; move it out of the tenant scope.
            //    `updated_at` does not exist on this table (append-only).
            AuditEvent::withoutGlobalScopes()
                ->where('agency_id', $agency->id)
                ->update(['agency_id' => null]);

            // 2. activity_logs cascades; replace them with the record below.
            DB::table('activity_logs')->where('agency_id', $agency->id)->delete();

            // 3. One row that says the delete happened, and what it took.
            AuditEvent::withoutGlobalScopes()->create([
                'agency_id'     => null,
                'actor_user_id' => $actorId,
                'action'        => 'deleted',
                'subject_type'  => Agency::class,
                'subject_id'    => $agency->id,
                'subject_label' => mb_substr((string) $agency->name, 0, 255),
                'before'        => ['status' => $agency->status, 'subdomain' => $agency->subdomain],
                'after'         => $payload,
                'ip_address'    => null,
                'user_agent'    => 'artisan agencies:remove-empty',
                'created_at'    => now(),
            ]);

            DB::table('activity_logs')->insert([
                'agency_id'    => null,
                'user_id'      => $actorId,
                'subject_type' => Agency::class,
                'subject_id'   => $agency->id,
                'action'       => 'godmode.agency_deleted',
                'description'  => "Agency {$agency->name} was hard-deleted from the console (".json_encode($payload).').',
                'metadata'     => json_encode(['channel' => 'console']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // 4. The agency goes last: a missed cascade fails inside this
            //    transaction instead of leaving a half-deleted tenant.
            Agency::withoutGlobalScopes()->whereKey($agency->id)->delete();
        });
    }
}
