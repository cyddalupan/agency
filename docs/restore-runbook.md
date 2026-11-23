# Restore runbook — single database out of the nightly all-databases archive

Status: **first draft, proven 2026-11-23** against
`/var/backups/mysql/mysql_all_databases_20261123_030002.sql.zip`
(297,266,071 bytes uncompressed / 165,106,656 bytes zipped, 30–31 database
sections, `super_landas` = 94 tables).

This procedure restores **one** app database from the `--all-databases` dump.
It does **not** replay the archive verbatim, and it must never do so.

---

## Why the naive replay fails

`mysqldump --all-databases` writes the `mysql` **system schema first**
(line 21 of the dump, before every app database). A verbatim
`mysql < dump.sql` therefore tries to `CREATE TABLE mysql.columns_priv` and
dies with:

```
ERROR 3723: table 'columns_priv' may not be created in the reserved tablespace 'mysql'
```

The `FOREIGN_KEY_CHECKS=0` wrapper **is present** (line 16 opens it, line 46146
restores it) — the earlier "missing wrapper" claim was wrong and came from
loading a `sed`-extracted fragment whose header sat above the cut. So the fix
is *not* to add the wrapper; it is to **cut the section correctly, keeping the
real header**.

## The trap that will destroy production

The `super_landas` section itself contains:

```
CREATE DATABASE /*!32312 IF NOT EXISTS*/ `super_landas` ...;
USE `super_landas`;
```

If you load the section unmodified, `USE super_landas` switches the session to
**the production database** and the section's 94 `DROP TABLE IF EXISTS`
statements wipe it. The scratch database name you pass on the command line is
**discarded** the moment the `USE` runs.

**Therefore the section must be loaded with those two lines removed**, and the
database chosen on the command line. The section contains no
`super_landas.`-qualified identifiers (verified: 0), so removing `USE` is safe.

---

## Procedure (verified, exit 0)

```bash
set -euo pipefail

ARCHIVE=/var/backups/mysql/mysql_all_databases_20261123_030002.sql.zip
SCRATCH=zz_restore_proof            # must start with zz_
DB=super_landas                     # the database to restore

# 1. Disk headroom first (uncompressed SQL is ~300 MB).
df -h /

# 2. Extract the SQL outside the backup tree.
cd /tmp
unzip -p "$ARCHIVE" > /tmp/restore_proof.sql
wc -l /tmp/restore_proof.sql

# 3. Locate the section boundaries. The section starts at the
#    "-- Current Database: `$DB`" marker and ends immediately before the NEXT
#    "-- Current Database:" marker (the trailing "-- Dumping events/routines"
#    block belongs to this DB and must be included).
grep -n '^-- Current Database:' /tmp/restore_proof.sql

# 4. Build the proof file:
#    - dump header, lines 1..18 (includes the FOREIGN_KEY_CHECKS=0 guard)
#    - the section WITHOUT its CREATE DATABASE / USE lines
#    - dump footer, from the "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */" line
#      to the end (restores FK/unique/character-set guard variables)
START=39301      # line of "-- Current Database: `super_landas`"
END=42931        # line before next "-- Current Database:"
FOOT=46142       # line of the footer's first SET restoration

# Section minus the CREATE DATABASE line (START+3) and USE line (START+5).
sed -n "${START},${END}p" /tmp/restore_proof.sql \
  | awk 'NR!=4 && NR!=6' > /tmp/zz_section_clean.sql

{
  sed -n '1,18p' /tmp/restore_proof.sql
  echo
  echo '-- PROOF: db context set by the mysql client (scratch), not by the dump.'
  cat /tmp/zz_section_clean.sql
  echo
  sed -n "${FOOT},\$p" /tmp/restore_proof.sql
} > /tmp/zz_proof_load.sql

# 5. Sanity gates BEFORE loading (all must hold):
grep -c '^CREATE TABLE' /tmp/zz_proof_load.sql        # expect 94
grep -nE '^(USE|CREATE DATABASE)' /tmp/zz_proof_load.sql || echo "OK: no USE/CREATE"
grep -n 'FOREIGN_KEY_CHECKS' /tmp/zz_proof_load.sql   # expect 2 (line 16 open + footer restore)

# 6. Create the scratch DB and load. REDIRECT, never pipe.
mysql -e "CREATE DATABASE $SCRATCH CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql "$SCRATCH" < /tmp/zz_proof_load.sql 2> /tmp/zz_proof_load.err
echo "EXIT=$?"          # MUST be 0
cat /tmp/zz_proof_load.err   # MUST be empty

# 7. Verify: table count must equal production's.
mysql -N -e "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema='$SCRATCH' AND table_type='BASE TABLE';"   # 94
mysql -N -e "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema='$DB' AND table_type='BASE TABLE';"        # 94

# 8. Drop the scratch DB and confirm no zz_ databases remain.
mysql -e "DROP DATABASE $SCRATCH;"
mysql -N -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA
             WHERE SCHEMA_NAME LIKE 'zz%';"     # expect empty

# 9. Remove temp files (they contain live data).
rm -f /tmp/restore_proof.sql /tmp/zz_section_clean.sql \
      /tmp/zz_proof_load.sql /tmp/zz_proof_load.err
```

### Notes

- Run as root; the server uses root socket auth (`/root/.my.cnf` absent,
  `db_backup.sh` relies on the same). No password is on disk.
- `mysql < file` **only**. A pipe (`cat file | mysql`) loses the dump header's
  `SET FOREIGN_KEY_CHECKS=0` guard ordering guarantees and is not used here.
- The footer's `SET GLOBAL INNODB_STATS_AUTO_RECALC=@OLD_...` line is a
  `GLOBAL` setting; it resets to the pre-dump value. Harmless, but note it
  needs no elevated privilege beyond what root already has.
- Section boundaries are line numbers **for this specific archive**. Always
  re-derive them with step 3 for a different dump.
- The archive is 165 MB zipped / ~297 MB SQL; the extracted file must not live
  under `/var/backups/mysql`.

---

## Row-count parity expectations

A restore taken at 03:00 compared against live production shows **drift**, not
failure. On 2026-11-23, 87/94 tables matched exactly; the 7 that differed were
all append-only or log tables (`expense_requests` +9, `expense_request_items`
+12, `expense_request_histories` +9, `applicant_documents` −1,
`applicant_passports` −1, `activity_logs` −42, `audit_events` −534), explained
by inserts after 03:00 and log pruning afterwards. **Compare against the dump
timestamp (`-- Dump completed on ...`), not "now".** Reference tables matched
exactly: `users` 86, `agencies` 20, `branches` 17, `employers` 84.

## Schema objects are subject to migration timing

The archive carries the schema **as it existed at dump time**. An index added
by a migration after the dump will not be present in a restore of an older
archive. Run pending migrations on the restored copy before asserting on them.

---

## Scripted rehearsal

`scripts/restore_rehearsal.sh` implements this procedure. Monthly, from root
crontab. It restores `super_landas` from the newest archive into
`zz_rehearsal_<YYYYMM>`, compares row counts and keys against production, posts
one PASS/FAIL to `#admin` (`C0ASLLPV0AZ`), and drops everything on exit.

```bash
# plan only: resolves the archive, prints the scratch DB name, the section
# boundaries, the 94 tables it will compare, the system schemas it skips and
# the parity rule. Creates and drops nothing. Exit 0.
scripts/restore_rehearsal.sh --dry-run

# real run (this is what cron runs, via $ARCHIVE)
scripts/restore_rehearsal.sh --archive /var/backups/mysql/mysql_all_databases_20261123_030002.sql.zip

# options
  --archive <zip>     archive to rehearse (default: newest in /var/backups/mysql)
  --dry-run           print the plan, exit 0, touch nothing
  --no-slack          do not post (for manual runs; cron never passes this)
  --min-free-mb <n>   disk guard before extracting (default 3072)
  --sample <n>        also print n named tables with prod vs restored counts
  --db <name>         scratch DB name; must be zz_rehearsal_<digits>
```

Exit codes: `0` PASS, `1` FAIL, `2` refused / unusable invocation, `3` interrupted.

### What the script refuses to do

These are assertions in the code, not conventions.

- **It will not load the section with `CREATE DATABASE` / `USE` in it.** The
  section is rebuilt from its own `-- Current Database:` marker to the next
  one, with every `CREATE DATABASE`, `USE` and `IF NOT EXISTS` line deleted;
  `validate_load_file()` then greps the result and refuses to load if any
  survive, or if the `FOREIGN_KEY_CHECKS` guard is missing. The section has no
  `super_landas.`-qualified identifiers (0 verified), so dropping `USE` is safe.
- **It will not restore into anything but `zz_rehearsal_<digits>`.** The
  target guard runs before any database is created; `super_landas` is refused
  by name, as is any other schema, and non-word characters (quote/semicolon
  injection) are refused separately.
- **It never runs `mysqldump`, never writes to `/var/backups/mysql`, and never
  writes to `super_landas`.** The archive is opened read-only; production is
  queried with `COUNT(*)` and PK joins only.
- **`--dry-run` creates and drops nothing.** It reads the zip into a temp dir
  to derive the section, then exits 0.

### Cleanup

A `trap` drops every `zz_rehearsal_*` database; a second trap handler on
INT/TERM/HUP stops the `mysql` load first, then runs the same cleanup. Verified
by sending SIGTERM mid-load: the scratch DB and the ~297 MB temp files were
gone within two seconds, no `mysql` process was left holding the archive.

**Why the load is backgrounded.** A foreground `mysql < file` makes bash wait
inside the child: bash marks the child as waited-for, TERM is handled by the
child (or ignored), the trap never runs, and the scratch DB leaks. The script
therefore starts the load with `timeout --foreground ... &`, waits explicitly,
and forwards signals to the child. This was not theoretical — the foreground
version was tested and leaked exactly that way.

A leftover `zz_rehearsal_<ym>` (only possible after a hard kill) is dropped and
recreated at start, so a second run never trips over the first.

### Disk

The extracted SQL is ~297 MB; the restored copy plus indexes is ~1 GB. The
guard aborts before extracting if `/tmp` has less than `--min-free-mb`
(default 3072 MiB). Extraction happens in a private `mktemp -d` under `/tmp`,
never under `/var/backups/mysql`, and the directory is deleted on exit.

---

## Parity rule

PASS/FAIL is decided per table, on two independent checks.

**Count parity.**

| Table class | Rule |
|---|---|
| Reference + business data (default) | `prod - restore <= 5000`; restore may be *below* — production gains rows after the dump |
| `activity_logs`, `audit_events` (live-purged logs) | as above, and restore may *exceed* prod by up to 2000 |
| `jobs`, `failed_jobs`, `job_batches`, `cache`, `cache_locks`, `sessions`, `report_generated_logs` | drift up to 2000 either way — short-lived rows |
| Any other table | a restore with *more* rows than prod is a FAIL |

Why prod may be smaller: production deletes rows after the dump. On
2026-11-23 `expense_requests`, its items and histories, and the applicant
identity tables had each lost a handful of rows since 03:00 (IDs contiguous —
`40528`, `40529` — so deletions, not ID gaps). `activity_logs` is pruned
(e.g. `id >= 3` removed).

**Feature parity (the test that matters).** For every table:

1. `prod.max(id) <= restore.max(id)` — production's newest row is in the
   archive.
2. **No row production still has is missing from the restore** — a PK
   left-join `prod LEFT JOIN restore WHERE restore.id IS NULL`, bounded by a
   per-table timeout. An unanswered check is a FAIL, not a pass.

Every absent row is then classified by `created_at` against the dump time:

- `created_at > dump` → the row was created after the dump. Expected, listed
  under "rows created after the dump (expected absent)".
- `created_at <= dump` or `NULL` → production had it before the dump and the
  archive lost it. **FAIL**: `UNEXPLAINED: <n> rows ... absent (e.g. id X)`.

This is what distinguishes drift from loss. Count tolerance alone cannot: one
of the 32 rows missing on 2026-11-23 was a single `applicant_documents` row
flagged `1` vs prod — inside every count tolerance, and a real signal that
would have been invisible to a count-only rule. All 32 were
`created_at > 03:01:06` and therefore drift.

The check set on 2026-11-23:

```
PARITY OK 94/94 tables
pk gap checked: 32 rows across tables where prod > restore
  applicant_documents         gap 1  all created after the dump (expected)
  applicant_passports         gap 1  all created after the dump (expected)
  expense_request_histories   gap 9  all created after the dump (expected)
  expense_request_items       gap 12 all created after the dump (expected)
  expense_requests            gap 9  all created after the dump (expected)
SAMPLE CHECK (5 tables): users 86/86, agencies 20/20, employers 84/84,
  branches 17/17, applicants 4952/4952
```

**Negative test.** Deleting one row from two restored tables before the
compare made the run FAIL with the right diagnosis — `users ... 1 pre-dump rows
of prod absent from the restore (id 1)`, `activity_logs ... 71 pre-dump rows`.
A rehearsal that cannot fail proves nothing.

`activity_logs` and `audit_events` keep more rows in the archive than in prod
(archive `6373` vs prod `5847` for `audit_events`) because prod prunes them.
That is an expected direction for those two tables only.

### Time zone

`created_at` comparisons assume the archive's `03:01:06` timestamp is the same
clock as `created_at` in production. Verified 2026-11-23: the archive's newest
`expense_requests` row has the same `created_at` as prod's newest *dumped* row,
and `@@system_time_zone` is UTC, matching the dump's `TIME_ZONE='+00:00'`. If
that ever stops holding, every row would be misclassified — re-check this
before trusting a PASS/FAIL after a server move.

---

## Scheduling

Proposed line for root crontab, **not applied** (AC8 is gated on AC1–AC5, and
crontab edits are not part of this change):

```cron
# Monthly restore rehearsal (04:30 Asia/Manila, 18:30 UTC on the 1st) —
# after the 03:00 backup, posts one PASS/FAIL to #admin
30 18 1 * * cd /var/www/landas-fixit && TZ=Asia/Manila bash scripts/restore_rehearsal.sh >> /root/.openclaw/workspace/logs/restore_rehearsal_cron.log 2>&1
```

The script also appends to `logs/restore_rehearsal.log` by default (override
with `RESTORE_LOG_DIR`).

## Known limits

- `mysql` system schemas (`mysql`, `information_schema`, `performance_schema`,
  `sys`) are skipped. A rehearsal cannot tell you the system schema would
  restore; it never needed to (the backup job's structural check covers the
  archive, and the system schema is not rebuilt on restore by design).
- The archive can be up to ~30 days old before it is rehearsed. Undetected
  archive rot between rehearsals is the accepted trade-off (monthly cadence,
  85% full disk).
- If the load is interrupted *while this shell is itself killed* (`kill -9`),
  no trap can run; the next run drops the leftover `zz_rehearsal_<ym>` at
  start. A `kill -9` of the whole process group is the one case that can leave
  the ~297 MB temp dir under `/tmp`; it is named `zz_rehearsal.*` so it is easy
  to find and remove.
