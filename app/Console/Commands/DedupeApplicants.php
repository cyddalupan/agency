<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Find (and optionally remove) duplicate applicants inside one agency.
 *
 * Duplicates are matched on LOWER(TRIM(first_name)) + LOWER(TRIM(last_name)),
 * which is the same normalisation the duplicate checker uses on the create
 * form. Groups that look like *different people* are never touched:
 *   - rows whose birthdates are present and differ
 *   - rows that differ in branch / agent / position / status / gender
 *
 * Safe by default:
 *   php artisan applicants:dedupe --agency=7                 (dry run, report only)
 *   php artisan applicants:dedupe --agency=7 --apply         (delete the extras)
 *
 * On --apply it first copies the whole applicant rows into
 * applicants_dup_backup_<YYYYmmdd_His>, then only deletes rows that have ZERO
 * child records (documents, passports, expenses, cases, ...). Anything with
 * dependent data is reported and left alone.
 */
class DedupeApplicants extends Command
{
    protected $signature = 'applicants:dedupe
        {--agency= : Agency id to scan (required)}
        {--apply : Delete the redundant rows (default is a dry run)}
        {--show=10 : How many sample groups to print}';

    protected $description = 'Find duplicate applicants in an agency and merge/remove the bare repeats.';

    /** Reused, case/space-insensitive grouping key. */
    private string $key = "LOWER(TRIM(first_name)) , LOWER(TRIM(last_name))";

    public function handle(): int
    {
        $agency = (int) $this->option('agency');
        if (! $agency) {
            $this->error('Pass --agency=<id>.');

            return self::FAILURE;
        }

        $groups = DB::select("
            SELECT LOWER(TRIM(first_name)) fn,
                   LOWER(TRIM(last_name)) ln,
                   COUNT(*) c,
                   MIN(id) keep_id,
                   COUNT(DISTINCT COALESCE(branch_id,0), COALESCE(agent_id,0), COALESCE(position_id,0),
                                        COALESCE(status_code,0), COALESCE(gender,'')) variants,
                   COUNT(DISTINCT COALESCE(DATE(birthdate), '1900-01-01')) birth_variants,
                   GROUP_CONCAT(id ORDER BY id) ids
            FROM applicants
            WHERE agency_id = ?
            GROUP BY LOWER(TRIM(first_name)), LOWER(TRIM(last_name))
            HAVING COUNT(*) > 1
            ORDER BY c DESC, fn
        ", [$agency]);

        if (empty($groups)) {
            $this->info('No duplicate applicants found.');

            return self::SUCCESS;
        }

        $auto = [];
        $skipped = [];
        foreach ($groups as $g) {
            $ids = array_map('intval', explode(',', $g->ids));
            // Different birthdates or different posting details => probably
            // genuinely different people. Leave them for a human.
            if ($g->birth_variants > 1 || $g->variants > 1) {
                $skipped[] = $g;
                continue;
            }
            $auto[] = $g;
        }

        $redundant = array_sum(array_map(fn ($g) => $g->c - 1, $auto));

        $this->line(sprintf(
            'Agency %d: %d duplicate groups, %d redundant rows. Auto-mergeable: %d groups / %d rows. Needs review: %d groups.',
            $agency, count($groups), array_sum(array_map(fn ($g) => $g->c - 1, $groups)),
            count($auto), $redundant, count($skipped)
        ));

        $show = (int) $this->option('show');
        if ($show > 0) {
            $this->newLine();
            $this->line('Sample auto-mergeable groups:');
            foreach (array_slice($auto, 0, $show) as $g) {
                $this->line(sprintf('  %s, %s — %d copies (ids %s), keep #%d', $g->ln, $g->fn, $g->c, $g->ids, $g->keep_id));
            }
            if ($skipped) {
                $this->newLine();
                $this->line('Needs review (details differ):');
                foreach (array_slice($skipped, 0, $show) as $g) {
                    $this->line(sprintf('  %s, %s — %d copies (ids %s)', $g->ln, $g->fn, $g->c, $g->ids));
                }
            }
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->comment('Dry run only. Re-run with --apply to delete the redundant rows.');

            return self::SUCCESS;
        }

        $childTables = $this->childTables();
        $backup = 'applicants_dup_backup_' . now()->format('Ymd_His');

        $deleted = 0;
        $protected = [];

        // Full copies of every row we are about to touch, so the merge is
        // reversible even without the nightly dump. Kept OUTSIDE the transaction:
        // CREATE TABLE ... AS SELECT causes an implicit commit in MySQL, which
        // would leave Laravel with "no active transaction" at commit time.
        $touchIds = [];
        foreach ($auto as $g) {
            $touchIds = array_merge($touchIds, array_map('intval', explode(',', $g->ids)));
        }
        DB::statement("CREATE TABLE {$backup} AS SELECT * FROM applicants WHERE id IN (" . implode(',', $touchIds) . ')');

        DB::transaction(function () use ($auto, $agency, $childTables, &$deleted, &$protected) {
            foreach ($auto as $g) {
                $ids = array_map('intval', explode(',', $g->ids));
                $keep = (int) $g->keep_id;
                foreach ($ids as $id) {
                    if ($id === $keep) {
                        continue;
                    }
                    if ($this->childCount($childTables, $id) > 0) {
                        $protected[] = $id;
                        continue;
                    }
                    DB::table('applicants')->where('id', $id)->where('agency_id', $agency)->delete();
                    $deleted++;
                }
            }
        });

        $this->newLine();
        $this->info("Deleted {$deleted} duplicate applicant(s). Backup table: {$backup}");
        if ($protected) {
            $this->warn('Left ' . count($protected) . ' row(s) with dependent records: ' . implode(', ', $protected));
        }
        if ($skipped) {
            $this->warn('Left ' . count($skipped) . ' group(s) for manual review (details differ).');
        }

        return self::SUCCESS;
    }

    /** @return array<int,string> */
    private function childTables(): array
    {
        return array_column(DB::select("
            SELECT TABLE_NAME t FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'applicant_id' AND TABLE_NAME <> 'applicants'
        "), 't');
    }

    private function childCount(array $tables, int $id): int
    {
        $total = 0;
        foreach ($tables as $t) {
            $total += (int) DB::table($t)->where('applicant_id', $id)->count();
        }

        return $total;
    }
}
