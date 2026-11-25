# DDL audit trail — what it is, why, and how to read it

Card `6b03ebf0d602a48e11939bbe`. Corrected 2026-11-25: the original card asked
for the MySQL `audit_log` plugin. **That plugin does not exist in Community
Edition** — there is no `.so` on this box and no apt package provides one, so
`INSTALL PLUGIN` could only fail. `general_log` was the other option and was
rejected: measured ~0.23 GB/day and it writes tenant PII (passports, NBI
records) in plaintext.

## The premise that was wrong

> "DROP TABLE logs no row images in the binlog, so replay could only prove
> *that* it happened, never *who*."

DDL is **statement-logged regardless of `binlog_format=ROW`**. Every
`DROP TABLE`, `DROP DATABASE`, `TRUNCATE`, `ALTER`, `RENAME` is a `Query` event
with the full SQL text, a timestamp and a `thread_id`. The binlog recorded all
four 23–25 Nov wipes the whole time. We were blind because nothing read it.

## What runs

| Piece | Where | What |
|---|---|---|
| `landas-ddl-audit` | `/usr/local/bin/` | Reads the binlogs, prints DDL against `super_landas`, alerts on destructive statements |
| `landas-thread-map` | `/usr/local/bin/` | Snapshots live sessions into `ops.thread_map` every minute |
| `/etc/cron.d/landas-ddl-audit` | cron | 00:10 UTC daily, `--lookback 26`, appends to the log |
| `/etc/cron.d/landas-thread-map` | cron | every minute |
| `/etc/logrotate.d/landas-ddl-audit` | logrotate | size 50M, rotate 8, compress |
| `ops.thread_map` | MySQL | thread_id → user@host + schema, 30-day retention |

Install / re-install: `sudo scripts/install_ddl_audit.sh` (idempotent, backs up
anything it replaces to `/root/ops-backups/`).

**Nothing here edits `my.cnf` or restarts mysqld.**

## Reading the log

```bash
sudo /usr/local/bin/landas-ddl-audit --since "2026-11-23 01:00:00" --until "2026-11-23 01:30:00"
sudo /usr/local/bin/landas-ddl-audit --db zz_my_probe --since "2026-11-25 10:00:00"
sudo grep "DDL-AUDIT ALERT" /var/log/mysql/ddl-audit.log
```

Output columns, tab-separated:

```
timestamp (UTC)   thread_id   user            DESTRUCTIVE|NORMAL   statement
```

`DESTRUCTIVE` = `DROP`, `TRUNCATE`, or a database `RENAME`. Exit code is **2**
if any destructive statement was seen, 0 otherwise — so a cron wrapper can
route on it.

Alerts are grouped **per thread per minute**, not per statement: a
`migrate:fresh` is ~90 drops in one connection over ~30 seconds, and 90 alert
lines hide the event instead of reporting it.

## Attribution — read this before trusting the user column

`thread_id` is the only hard evidence. A minute snapshot maps it to
`user@host`. Two caveats, deliberately visible in the output:

* A connection that opens and dies inside one minute may never be snapshotted.
  The watcher then falls back to the nearest snapshot **only if that snapshot
  saw the same thread working in the same database**, within 90 seconds. That
  match is printed as `approx:<user>`.
* No match at all prints `unknown`. That is not a bug; pretending otherwise
  would be a guess dressed as evidence.

## Limits

* The binlog holds what retention allows (`binlog_expire_logs_seconds`). We do
  not purge binlogs from this script — that is a decision for Cyd, and the
  binlog is what let us recover every wipe so far.
* A scan decodes at most 512 MB per run. The daily cron only looks at files
  touched in the last 26 hours. A wider `--since` decodes everything and prints
  a warning if it truncates.
* `ops.thread_map` is our own schema. Nothing in `super_landas` is touched, and
  the watcher is read-only against the tables.

## Disable

```bash
sudo rm -f /etc/cron.d/landas-ddl-audit /etc/cron.d/landas-thread-map \
           /etc/logrotate.d/landas-ddl-audit \
           /usr/local/bin/landas-ddl-audit /usr/local/bin/landas-thread-map
sudo mysql -e "DROP TABLE IF EXISTS ops.thread_map"
```

The log file is additive; delete it if it is never wanted again.
