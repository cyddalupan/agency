#!/bin/bash
# =============================================================================
# restore_rehearsal.sh — monthly restore rehearsal of the nightly archive
# =============================================================================
# Restores ONE database (super_landas) out of the nightly --all-databases
# archive into a scratch database, then compares per-table row counts against
# production and reports PASS / FAIL.
#
# Implements docs/restore-runbook.md. Read that before changing this file.
#
# SAFETY — this script is deliberately narrow. Do not loosen any of these:
#   * The super_landas section of the dump ends with `USE `super_landas`;` and
#     94 `DROP TABLE IF EXISTS` statements. Loading it verbatim wipes PROD.
#     Every CREATE DATABASE / USE line is deleted from the extracted section
#     and the load aborts if any survive. See build_load_file().
#   * The scratch database is always zz_rehearsal_<ym>; any other target name
#     aborts before the database is created.
#   * This script never runs mysqldump, never writes to /var/backups/mysql and
#     never writes to super_landas. It only reads the archive and reads counts.
#   * A trap drops every zz_ database this run created, on success and on
#     failure, and removes the extracted temp files (they hold live data).
#
# Usage:
#   scripts/restore_rehearsal.sh [--archive <zip>] [--dry-run] [--no-slack]
#                                [--min-free-mb <n>] [--db <name>]
#
# Exit: 0 PASS, 1 FAIL, 2 refused / unusable invocation, 3 interrupted.
# =============================================================================
set -uo pipefail

DB="super_landas"                 # SOURCE: the only database this script restores
SCRATCH_PREFIX="zz_rehearsal_"    # every scratch DB this script may create
TARGET=""                         # DESTINATION; default zz_rehearsal_<ym> set below
ARCHIVE_DIR="/var/backups/mysql"  # read-only for this script
CHANNEL_ID="${RESTORE_SLACK_CHANNEL:-C0ASLLPV0AZ}"  # #admin by default, same channel as the backup job
MIN_FREE_MB_DEFAULT=3072          # ~297 MB SQL + ~1 GB restored copy + margin
COMMAND_TIMEOUT=21600             # 6 h ceiling on the load (297 MB, 16 G box)

# Tolerance rule, documented in docs/restore-runbook.md ("Parity rule").
#   * Log/append-only tables: prod may have gained rows since the dump, so a
#     restore is allowed to be SHORTER than prod by at most LOG_DRIFT_CAP.
#   * Short-lived tables (queue/cache/session): same shape, tighter cap.
#   * Everything else (reference + business data): exact match required.
#   * restored > prod on ANY table is always a failure: rows in the archive
#     that production no longer has means the archive cannot be trusted.
LOG_DRIFT_CAP=5000
SHORTLIVED_DRIFT_CAP=2000
# A restore count above prod is tolerated only by this much, and only for the
# tables listed in RESTORE_CAN_EXCEED_PROD — deleting/live-purge tables and the
# audit log. Anywhere else, more rows in the archive than in prod is a FAIL.
EXCEED_CAP=2000
RESTORE_CAN_EXCEED_PROD="activity_logs audit_events"

WORKDIR=""                        # temp dir, created only with work to do
SCRATCH=""                        # resolved scratch DB name
SCRATCH_CREATED=0                 # 1 once CREATE DATABASE succeeded
LOAD_TABLES=0                     # tables loaded from the archive
EXIT_CODE=0
CLEANUP_DONE=0
LOAD_PID=""                 # background mysql load (signals are forwarded)
LOAD_RC=""

SLACK_TOKEN=""
LOG_DIR="${RESTORE_LOG_DIR:-}"

# ----------------------------------------------------------------- derived ---
# Monthly scratch name: zz_rehearsal_YYYYMM (Asia/Manila, like the backup job).
YM=$(TZ=Asia/Manila date '+%Y%m')
SCRATCH="${SCRATCH_PREFIX}${YM}"   # provisional; may be overridden by --db

usage() { sed -n '2,40p' "$0" | sed 's/^# \{0,1\}//'; exit 2; }

log()  {
  printf '%s %s\n' "[$(TZ=Asia/Manila date '+%F %T')]" "$*"
  log_file "$*"
}

# --- log file ----------------------------------------------------------------
if [ -z "$LOG_DIR" ]; then
  if [ -d /root/.openclaw/workspace/logs ]; then LOG_DIR=/root/.openclaw/workspace/logs; fi
fi
LOG_FILE=""
if [ -n "$LOG_DIR" ] && mkdir -p "$LOG_DIR" 2>/dev/null; then
  LOG_FILE="$LOG_DIR/restore_rehearsal.log"
fi
log_file() {  # append to the log file only (log() adds the console copy)
  [ -n "$LOG_FILE" ] && printf '%s %s\n' "[$(TZ=Asia/Manila date '+%F %T')]" "$*" >> "$LOG_FILE"
  return 0
}
note() { log "$*"; }

plain() {  # $1 = text to print without the log timestamp (dry-run output)
  printf '%s\n' "$1"
}

die_refuse() {  # $1 = reason → refused / unusable invocation
  log "REFUSED: $1"
  exit 2
}

# ------------------------------------------------------------------ args ----
DRY_RUN=0
NO_SLACK=0
SAMPLE_N=0                  # --sample N: sample-check N tables and stop
RESTORE_SAMPLED=""
DUMP_TS=""                  # dump time as DATETIME (UTC)
PK_DETAIL=""                # detail for the last pk_feature_check
POST_DUMP_GAP=0             # absent rows explained by created_at > dump
MIN_FREE_MB="$MIN_FREE_MB_DEFAULT"
ARCHIVE=""

while [ $# -gt 0 ]; do
  case "$1" in
    --dry-run)      DRY_RUN=1; shift ;;
    --no-slack)     NO_SLACK=1; shift ;;
    --archive)      [ $# -ge 2 ] || die_refuse "--archive needs a path"; ARCHIVE="$2"; shift 2 ;;
    --archive=*)    ARCHIVE="${1#*=}"; shift ;;
    --db)           [ $# -ge 2 ] || die_refuse "--db needs a name"; TARGET="$2"; shift 2 ;;
    --db=*)         TARGET="${1#*=}"; shift ;;
    --min-free-mb)  [ $# -ge 2 ] || die_refuse "--min-free-mb needs a number"; MIN_FREE_MB="$2"; shift 2 ;;
    --min-free-mb=*) MIN_FREE_MB="${1#*=}"; shift ;;
    --sample)       [ $# -ge 2 ] || die_refuse "--sample needs a number"; SAMPLE_N="$2"; shift 2 ;;
    --sample=*)     SAMPLE_N="${1#*=}"; shift ;;
    -h|--help)      usage ;;
    *) die_refuse "unknown argument: $1" ;;
  esac
done

case "$MIN_FREE_MB" in ''|*[!0-9]*) die_refuse "--min-free-mb must be an integer" ;; esac
case "$SAMPLE_N" in ''|*[!0-9]*) die_refuse "--sample must be an integer" ;; esac

# Default destination is the monthly scratch db, unless --db named one.
[ -z "$TARGET" ] && { TARGET="$SCRATCH"; }

# --- database target guard (AC4) ---------------------------------------------
# Two independent refusals: wildcard/odd characters, and the zz_rehearsal_
# prefix. super_landas is never a legal target here.
case "$TARGET" in
  *[!A-Za-z0-9_]*|'') die_refuse "scratch database name '$TARGET' contains non-word characters" ;;
esac
case "$TARGET" in
  "${SCRATCH_PREFIX}"[0-9]*) : ;;   # zz_rehearsal_<ym> and nothing else
  super_landas)
      die_refuse "refusing to restore into the production database '$TARGET'" ;;
  *)
      die_refuse "refusing to restore into '$TARGET': scratch databases must be ${SCRATCH_PREFIX}<YYYYMM>" ;;
esac
# The production database may only ever be the *source* of row counts.
PROD_DB="super_landas"
[ "$DB" = "$PROD_DB" ] || die_refuse "refusing to read from '$DB': the only supported source is '$PROD_DB'"


# Report lines are collected so Slack gets exactly one message.
REPORT=""
collect() { REPORT="${REPORT}$1"$'\n'; log "$1"; }

# ------------------------------------------------------------- cleanup ------
wait_for_load() {  # $1 = pid; sets LOAD_RC, returns it
  local pid="$1" rc i=0
  # The handler kills the child and cleanup() clears LOAD_PID; after that we
  # stop waiting and report the interrupt. Until then, retry: a plain wait that
  # returns 143 while the child lives means *we* were signalled, and only a
  # further wait lets the handler run.
  while kill -0 "$pid" 2>/dev/null && [ -n "${LOAD_PID:-}" ]; do
    i=$((i + 1))
    [ "$i" -gt 100 ] && break
    if wait "$pid"; then rc=0; else rc=$?; fi
    [ -n "$LOAD_RC" ] && { rc="$LOAD_RC"; break; }
  done
  [ -n "${LOAD_PID:-}" ] && { wait "$pid" 2>/dev/null; rc=$?; }
  LOAD_RC="$rc"
  return "$rc"
}

cleanup() {
  local rc=$?
  [ "$CLEANUP_DONE" -eq 1 ] && return 0
  CLEANUP_DONE=1
  # 0. Stop the load first, or it keeps writing into the database we drop.
  if [ -n "${LOAD_PID:-}" ] && kill -0 "$LOAD_PID" 2>/dev/null; then
    log "cleanup: stopping load (pid $LOAD_PID)"
    kill -TERM "$LOAD_PID" 2>/dev/null
    for _ in 1 2 3 4 5 6 7 8 9 10; do kill -0 "$LOAD_PID" 2>/dev/null || break; sleep 1; done
    kill -KILL "$LOAD_PID" 2>/dev/null
  fi
  # 1. Drop the scratch database(s). The trap runs even on kill/INTR/TERM.
  if [ "$SCRATCH_CREATED" -eq 1 ] || [ -n "$SCRATCH" ]; then
    local leftover
    leftover=$(mysql -N -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA
                            WHERE SCHEMA_NAME LIKE '${SCRATCH_PREFIX}${YM}%';" 2>/dev/null)
    if [ -n "$leftover" ]; then
      while read -r d; do
        case "$d" in
          "${SCRATCH_PREFIX}"*) log "cleanup: dropping leftover scratch db \`$d\`"; mysql -e "DROP DATABASE \`$d\`;" 2>&1 | sed 's/^/cleanup: /' ;;
          *) log "cleanup: WARNING not dropping non-zz database '$d' (should be impossible)" ;;
        esac
      done <<< "$leftover"
    fi
  fi
  # 2. Temp files hold a full copy of live data — remove them.
  if [ -n "$WORKDIR" ] && [ -d "$WORKDIR" ]; then
    find "$WORKDIR" -xdev -type f -delete 2>/dev/null
    rmdir "$WORKDIR" 2>/dev/null || log "cleanup: WARNING could not remove $WORKDIR"
  fi
  # 3. Final assertion: no zz_ database of ours may remain.
  local remain
  remain=$(mysql -N -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA
                        WHERE SCHEMA_NAME LIKE '${SCRATCH_PREFIX}%';" 2>/dev/null)
  if [ -n "$remain" ]; then
    log "cleanup: FAILED — scratch database(s) still present: $(echo "$remain" | tr '\n' ' ')"
  else
    log "cleanup: no ${SCRATCH_PREFIX}% database remains"
  fi
  return "$rc"
}

on_signal() {
  local sig="$1"
  log "INTERRUPTED (SIG${sig}) — stopping the load and cleaning up"
  trap - EXIT INT TERM HUP
  # Kill the load before cleanup: an EXIT trap's exit status must not depend
  # on a killed child, and dropping the DB while mysql still writes is wrong.
  if [ -n "${LOAD_PID:-}" ] && kill -0 "$LOAD_PID" 2>/dev/null; then
    kill -TERM "$LOAD_PID" 2>/dev/null
    for _ in $(seq 1 20); do kill -0 "$LOAD_PID" 2>/dev/null || break; sleep 0.5; done
    kill -KILL "$LOAD_PID" 2>/dev/null
  fi
  LOAD_PID=""          # cleanup must not wait on it again
  LOAD_RC=143          # report the interrupt, not the child's status
  cleanup
  exit 3
}

trap cleanup EXIT
trap 'on_signal INT'  INT
trap 'on_signal TERM' TERM
trap 'on_signal HUP'  HUP

# ------------------------------------------------------------- helpers ------
post_slack() {  # $1 = text; exactly one message per run, unless --no-slack
  [ "$NO_SLACK" -eq 1 ] && { log "slack: suppressed (--no-slack)"; return 0; }
  if [ -z "$SLACK_TOKEN" ]; then
    SLACK_TOKEN=$(grep -oP '"botToken"\s*:\s*"[^"]+"' /root/.openclaw/openclaw.json 2>/dev/null \
                    | head -1 | sed 's/"botToken": "//;s/"//')
  fi
  if [ -z "$SLACK_TOKEN" ]; then log "slack: no bot token found; message not sent"; return 1; fi
  # chat.postMessage and conversations.history need different scopes. A
  # channel_not_found here is usually a missing chat:write on a valid
  # channel, so say which it was instead of failing quietly.
  if [ "${SLACK_AUTHCHECK:-1}" = "1" ]; then
    local who; who=$(curl -s --max-time 15 -X POST https://slack.com/api/auth.test \
                      -H "Authorization: Bearer $SLACK_TOKEN")
    if ! echo "$who" | jq -e '.ok == true' >/dev/null 2>&1; then
      log "slack: auth.test failed: $(echo "$who" | head -c 200)"; return 1
    fi
  fi
  local resp
  resp=$(curl -s --max-time 30 -X POST "https://slack.com/api/chat.postMessage" \
           -H "Authorization: Bearer $SLACK_TOKEN" -H "Content-Type: application/json" \
           -d "$(jq -n --arg ch "$CHANNEL_ID" --arg txt "$1" '{channel:$ch,text:$txt,mrkdwn:true}')")
  if echo "$resp" | jq -e '.ok == true' >/dev/null 2>&1; then
    log "slack: posted to channel ${CHANNEL_ID}"
    return 0
  fi
  log "slack: POST FAILED to ${CHANNEL_ID}: $(echo "$resp" | head -c 300)"
  return 1
}

free_mb_of() {  # $1 = path → MiB available on its filesystem
  df -Pm "$1" 2>/dev/null | awk 'NR==2{print $4+0}'
}

# Find the line number of a section marker; empty if absent.
grep_first_line() {  # $1 = file, $2 = fixed pattern
  grep -n -F -- "$2" "$1" 2>/dev/null | head -1 | cut -d: -f1
}

# ------------------------------------------------------------------ start ---
log "=== restore rehearsal started (${DRY_RUN:+}dry_run=$DRY_RUN db=$DB) ==="

# Work only with a zip archive and refuse anything from the live backup dir
# as a *write* target is irrelevant here: we never write there at all. Reads
# are fine, so --archive may point into /var/backups/mysql.
# If no archive was named, resolutions happen in resolve_archive().
resolve_archive() {
  # No --archive given: use the newest managed archive (cron passes nothing).
  if [ -z "$ARCHIVE" ]; then
    ARCHIVE=$(ls -1t "${ARCHIVE_DIR}"/mysql_all_databases_*.sql.zip 2>/dev/null | head -1)
    [ -n "$ARCHIVE" ] || die_refuse "no archive found in $ARCHIVE_DIR"
  fi
}

# cron passes no --archive: default to the newest managed archive, then check.
resolve_archive
[ -f "$ARCHIVE" ] || die_refuse "archive not found: $ARCHIVE"
[ -r "$ARCHIVE" ] || die_refuse "archive not readable: $ARCHIVE"

# Parse -- Dump completed on ... (used to explain drift; not required to pass)
dump_completed() {  # $1 = sql file
  grep -m1 '^-- Dump completed on' "$1" 2>/dev/null | sed 's/^-- Dump completed on[[:space:]]*//'
}

# Validate the archive: zip test, then find the dump header and the section.
# Sets globals: DUMP_COMPLETED, HEADER_END (line), SEC_START, SEC_END, FOOT_START.
locate_layout() {  # $1 = extracted sql path
  local f="$1"
  HEADER_END=$(grep_first_line "$f" '/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;')
  [ -n "$HEADER_END" ] || { log "FAIL: dump header (SQL_NOTES guard) not found"; return 1; }

  SEC_START=$(grep_first_line "$f" "-- Current Database: \`${DB}\`")
  [ -n "$SEC_START" ] || { log "FAIL: no section for database \`${DB}\` in the archive"; return 1; }

  # The section ends at the next "-- Current Database:" marker, or at EOF.
  local next_marker
  next_marker=$(awk -v s="$SEC_START" 'NR>s && /^-- Current Database:/{print NR; exit}' "$f")
  if [ -n "$next_marker" ]; then
    SEC_END=$((next_marker - 1))
  else
    SEC_END=$(wc -l < "$f")
  fi

  # Footer: first restoration of a dump-level GLOBAL/TIME_ZONE variable AFTER
  # the section. Everything from there to EOF restores the guard variables.
  FOOT_START=$(awk -v s="$SEC_END" 'NR>s && /^\/\*!40103 SET TIME_ZONE=@OLD_TIME_ZONE \*\/;/{print NR; exit}' "$f")
  [ -n "$FOOT_START" ] || { log "FAIL: dump footer (TIME_ZONE restore) not found"; return 1; }

  DUMP_COMPLETED=$(dump_completed "$f")
  # Dump time as a comparable DATETIME. The dump writes TIME_ZONE='+00:00'
  # and its footer says the same clock; MySQL's system_time_zone on this host
  # is also UTC, so prod's stored created_at values are UTC too. That is
  # verified by the fact that the newest prod row and the newest dumped row
  # come from the same wall clock (runbook, "Row-count parity").
  DUMP_TS=""
  if [ -n "$DUMP_COMPLETED" ]; then
    DUMP_TS=$(TZ=UTC date -d "$DUMP_COMPLETED" '+%Y-%m-%d %H:%M:%S' 2>/dev/null)
  fi
  return 0
}

# Produce the load file: header + section WITHOUT CREATE DATABASE/USE + footer.
# This is where the production-wipe trap is defused.
build_load_file() {  # $1 = source sql, $2 = output sql
  local src="$1" out="$2"
  log "build: header 1-${HEADER_END}, section ${SEC_START}-${SEC_END} (CREATE/USE stripped), footer ${FOOT_START}-EOF"
  {
    sed -n "1,${HEADER_END}p" "$src"
    plain "-- REHEARSAL: database context comes from the mysql client, not the dump"
    sed -n "${SEC_START},${SEC_END}p" "$src" \
      | grep -avE '^CREATE DATABASE |^USE |^/\*!32312 IF NOT EXISTS\*/ `|^-- *Create database' || true
    plain ""
    sed -n "${FOOT_START},\$p" "$src"
  } > "$out"
}

# Sanity gates on the built file. Any failure refuses to load (returns 1).
validate_load_file() {  # $1 = load sql
  local f="$1" prog=""

  # Gate 1 (the important one): no USE / CREATE DATABASE lines may survive.
  if grep -aqnE '^[[:space:]]*(USE|CREATE[[:space:]]+DATABASE)\b' "$f"; then
    log "REFUSED: the load file still contains USE/CREATE DATABASE lines:"
    grep -anE '^[[:space:]]*(USE|CREATE[[:space:]]+DATABASE)\b' "$f" | head -5 | sed 's/^/    /'
    return 1
  fi
  # Gate 2: the load file must not name the production database in a USE-like
  # construct that survives (belt and braces; the section has no qualified
  # identifiers, so a bare `super_landas` outside comments is unexpected).
  if grep -aqnE '^[[:space:]]*USE[[:space:]]+`?super_landas' "$f"; then
    log "REFUSED: a USE super_landas line survives"
    return 1
  fi
  # Gate 3: the FK guard must be opened in the header and closed in the footer.
  local fk_open fk_close
  fk_open=$(grep -ac 'FOREIGN_KEY_CHECKS=0' "$f")
  fk_close=$(grep -ac 'FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS' "$f")
  if [ "$fk_open" -lt 1 ] || [ "$fk_close" -lt 1 ]; then
    log "FAIL: FOREIGN_KEY_CHECKS guard missing (open=$fk_open close=$fk_close)"
    return 1
  fi
  # Gate 4: the DD|DML statements must parse-guard against NOT be empty and
  # there must be at least one CREATE TABLE (an empty/short file is a failure).
  local ct
  ct=$(grep -ac '^CREATE TABLE ' "$f")
  if [ "$ct" -lt 1 ]; then log "FAIL: no CREATE TABLE statements in the load file"; return 1; fi
  LOAD_TABLES="$ct"
  return 0
}

# Per-table row counts for a schema. Emits "table<TAB>rows" one per line to a
# file. Tables that error are reported with `ERR:<msg>` so a FAIL is visible.
collect_row_counts() {  # $1 = schema, $2 = output file
  local schema="$1" out="$2" t
  : > "$out"
  while read -r t; do
    [ -z "$t" ] && continue
    local n
    n=$(mysql -N -e "SELECT COUNT(*) FROM \`${schema}\`.\`${t}\`;" 2>/dev/null) || n="ERR"
    printf '%s\t%s\n' "$t" "${n}" >> "$out"
  done < <(mysql -N -e "SELECT table_name FROM information_schema.tables
                        WHERE table_schema='${schema}' AND table_type='BASE TABLE'
                        ORDER BY table_name;")
}


# --- feature parity: is every row prod still has also in the archive? ------
# Compares primary keys. Returns 0 ok, or prints "ROWS_MISSING:<n> ..." .
# Uses a LEFT JOIN over an indexed key, so it is a PK range scan, not a full
# scan with a temp table. Time-bounded per table; timing out is a FAIL because
# an unanswered check is not a pass.
pk_feature_check() {  # $1 = table, $2 = timeout seconds, $3 = prod row count
  local t="$1" to="$2" has_pk missing t0
  has_pk=$(mysql -N -e "SELECT COUNT(*) FROM information_schema.statistics
                        WHERE table_schema='${PROD_DB}' AND table_name='${t}'
                          AND index_name='PRIMARY';")
  if [ "$has_pk" -eq 0 ]; then
    # No primary key: fall back to the newest-id rule where an id exists.
    if [ -n "$(mysql -N -e "SELECT 1 FROM information_schema.columns
                            WHERE table_schema='${PROD_DB}' AND table_name='${t}' AND column_name='id';")" ]; then
      local pi ri
      pi=$(mysql -N -e "SELECT COALESCE(MAX(id),0) FROM \`${PROD_DB}\`.\`${t}\`;")
      ri=$(mysql -N -e "SELECT COALESCE(MAX(id),0) FROM \`${SCRATCH}\`.\`${t}\`;")
      [ "$pi" -le "$ri" ] && return 0
      echo "ROWS_MISSING:prod newest id ${pi} > restored newest id ${ri}"
      return 1
    fi
    return 0   # nothing to key on; count parity stood
  fi
  # Only absent rows can be missing, so an empty prod table needs no query
  # (and COUNT(*)=0 is not a failure — the table restored empty correctly).
  local pcount="${3:-}"
  [ -n "${WORKDIR:-}" ] && [ -f "$WORKDIR/pk_detail" ] && : > "$WORKDIR/pk_detail"
  if [ -z "$pcount" ] || [ "$pcount" = "0" ]; then PK_DETAIL=""; return 0; fi
  # Split the gap by when prod created the row:
  #   created_at/timezone > DUMP_TS  -> created after the dump: expected absent
  #   otherwise                      -> production had it before the dump:
  #                                     the archive lost it => FAIL
  if has_col "$t" created_at && [ -n "$DUMP_TS" ]; then
    missing=$(timeout "$to" mysql -N -e "
        SELECT
          COALESCE(SUM(p.created_at > '${DUMP_TS}'),0),
          COALESCE(SUM(p.created_at IS NULL OR p.created_at <= '${DUMP_TS}'),0),
          COALESCE(MIN(CASE WHEN p.created_at <= '${DUMP_TS}' THEN p.id END),0)
        FROM \`${PROD_DB}\`.\`${t}\` p
        LEFT JOIN \`${SCRATCH}\`.\`${t}\` r ON r.id = p.id
        WHERE r.id IS NULL\G" 2>/dev/null) || { PK_DETAIL=""; echo "TIMEOUT_OR_ERROR"; return 1; }
    # \G under -N prints no labels: the first line is the row banner, the
    # three values follow one per line. Take them positionally.
    missing=$(printf '%s\n' "$missing" | sed '1d' | awk 'NF{printf "%s ", $1}')
  else
    # No created_at: every absent row is unexplained.
    missing=$(timeout "$to" mysql -N -e "
        SELECT 0, COUNT(*), COALESCE(MIN(p.id),0)
        FROM \`${PROD_DB}\`.\`${t}\` p
        LEFT JOIN \`${SCRATCH}\`.\`${t}\` r ON r.id = p.id
        WHERE r.id IS NULL\G" 2>/dev/null) || { PK_DETAIL=""; echo "TIMEOUT_OR_ERROR"; return 1; }
    # \G under -N prints no labels: the first line is the row banner, the
    # three values follow one per line. Take them positionally.
    missing=$(printf '%s\n' "$missing" | sed '1d' | awk 'NF{printf "%s ", $1}')
  fi
  # read(1) collapses runs of whitespace, so parse by position explicitly:
  #   missing = "<post> <pre> <minid> ". An empty group would shift fields.
  local post pre minid
  post=$(printf '%s' "${missing:-}" | awk '{print $1+0}')
  pre=$(printf '%s'  "${missing:-}" | awk '{print $2+0}')
  minid=$(printf '%s' "${missing:-}" | awk '{print $3+0}')
  case "${post}${pre}${minid}" in *[!0-9]*) PK_DETAIL=""; echo "TIMEOUT_OR_ERROR"; return 1 ;; esac
  if [ "$pre" -eq 0 ] && [ "$post" -gt 0 ]; then
    PK_DETAIL="gap ${post} all created after the dump (expected)"
    printf '%s' "$PK_DETAIL" > "$WORKDIR/pk_detail"
    return 0
  fi
  if [ "$pre" -eq 0 ] && [ "$post" -eq 0 ]; then
    PK_DETAIL=""
    return 0
  fi
  PK_DETAIL="UNEXPLAINED: ${pre} rows created at/before the dump are absent (e.g. id ${minid}); ${post} absent rows are post-dump"
  printf '%s' "$PK_DETAIL" > "$WORKDIR/pk_detail"
  echo "ROWS_MISSING:${pre} pre-dump rows of prod absent from the restore (id ${minid}); ${post} post-dump"
  return 1
}

has_col() {  # $1 = table in prod, $2 = column name
  [ -n "$(mysql -N -e "SELECT 1 FROM information_schema.columns
                       WHERE table_schema='${PROD_DB}' AND table_name='$1' AND column_name='$2' LIMIT 1;")" ]
}

# Sample checks for --sample N: named reference + business tables, printed
# explicitly (the proof-card acceptance was "5 sampled tables").
sample_check() {  # $1 = n
  local n="$1" t p r out=""
  out="SAMPLE CHECK (${n} tables):"
  for t in users agencies employers branches applicants expense_requests payments; do
    [ "$n" -le 0 ] && break
    case " $RESTORE_SAMPLED " in *" $t "*) continue ;; esac
    p=$(mysql -N -e "SELECT COUNT(*) FROM \`${PROD_DB}\`.\`${t}\`;" 2>/dev/null) || continue
    r=$(mysql -N -e "SELECT COUNT(*) FROM \`${SCRATCH}\`.\`${t}\`;" 2>/dev/null) || continue
    RESTORE_SAMPLED="$RESTORE_SAMPLED $t"
    out="${out}"$'\n'"    ${t}: prod=${p} restored=${r} $([ "$p" = "$r" ] && echo OK || echo DIFF)"
    n=$((n - 1))
  done
  printf '%s' "$out"
}

# ------------------------------------------------------------------- main ---
main() {
  # Resolve archive once; --dry-run then prints the plan and exits 0.
  resolve_archive

  local free_mb
  free_mb=$(free_mb_of /tmp)
  local scratch_mb="$MIN_FREE_MB"

  if [ "$DRY_RUN" -eq 1 ]; then
    plain "=============================================================="
    plain " restore rehearsal — DRY RUN (nothing will be created or dropped)"
    plain "=============================================================="
    plain " archive          : $ARCHIVE"
    plain " archive size     : $(stat -c%s "$ARCHIVE" 2>/dev/null) bytes (zipped)"
    plain " source database  : $DB  (read only)"
    plain " production db    : $PROD_DB  (SELECT COUNT(*) only)"
    plain " scratch database : $SCRATCH"
    plain " min free space   : ${MIN_FREE_MB} MiB (free now: ${free_mb} MiB on /tmp)"
    plain " slack            : $([ "$NO_SLACK" -eq 1 ] && echo suppressed || echo "one PASS/FAIL to #admin ($CHANNEL_ID)")"
    plain ""
    plain " will skip system schemas (--all-databases includes them; replaying them fails 3723):"
    plain "   mysql, information_schema, performance_schema, sys"
    plain " will load exactly one section: \`$DB\` (CREATE DATABASE / USE stripped)"
    plain ""
    # Derive the layout without loading anything (read from the zip).
    local tmpd drc=0
    tmpd=$(mktemp -d /tmp/zz_rehearsal_dryrun.XXXXXX) || { log "FAIL: mktemp"; exit 1; }
    WORKDIR="$tmpd"
    if ! unzip -tqq "$ARCHIVE" >/dev/null 2>&1; then
      plain " NOTE: archive failed zip integrity check — real run would FAIL"
      drc=1
    else
      unzip -p "$ARCHIVE" > "$tmpd/dump.sql" 2>/dev/null || { plain " NOTE: could not extract archive"; drc=1; }
      if [ -s "$tmpd/dump.sql" ] && locate_layout "$tmpd/dump.sql"; then
        plain " archive dump completed on: ${DUMP_COMPLETED:-unknown}"
        plain " section lines     : ${SEC_START}-${SEC_END}"
        plain " header lines      : 1-${HEADER_END}"
        plain " footer lines      : ${FOOT_START}-EOF"
        plain ""
        plain " tables that will be compared (per-table row counts, prod vs restored):"
        # Table list from the section's CREATE TABLE statements (no DB needed).
        local list
        list=$(sed -n "${SEC_START},${SEC_END}p" "$tmpd/dump.sql" \
                 | grep -aoE '^CREATE TABLE `[^`]+`' | sed 's/^CREATE TABLE `//;s/`$//' | sort -u)
        local n; n=$(printf '%s\n' "$list" | grep -c .)
        plain "   ${n} tables"
        printf '%s\n' "$list" | sed 's/^/     - /'
        plain ""
        plain "parity rule (see docs/restore-runbook.md, \"Parity rule\"):"
        plain "  1. table set identical (no table only in prod, none only in the archive)"
        plain "  2. every table: prod.max(id) <= restore.max(id)  [newest prod row present]"
        plain "  3. every table: no row production still has is missing from the restore"
        plain "  4. counts: |prod - restore| <= ${LOG_DRIFT_CAP} on any table;"
    plain "     prod MAY be smaller (log pruning, deletes since the dump) but only"
    plain "     ${RESTORE_CAN_EXCEED_PROD} may exceed prod, by <= ${EXCEED_CAP}"
    plain "  5. short-lived tables (jobs/cache/sessions): drift <= ${SHORTLIVED_DRIFT_CAP}"
    plain "  6. rows created after the dump are expected to be absent if prod has no"
    plain "     more than the count tolerance's worth of them; each absent row is"
    plain "     labelled (created after dump / created before dump: UNEXPLAINED)."
      else
        plain " NOTE: could not locate the \`$DB\` section (archive looks unusable)"
        drc=1
      fi
    fi
    plain ""
    plain " exit: 0 (dry run — nothing changed)"
    return "$drc"
  fi

  # --- real run -------------------------------------------------------------
  # 1. Disk guard BEFORE extracting (AC: abort cleanly if insufficient).
  if [ "$free_mb" -lt "$MIN_FREE_MB" ]; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z') — insufficient disk"
    collect "  /tmp has ${free_mb} MiB free, need ${MIN_FREE_MB} MiB. Aborted before extracting."
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi
  log "disk: ${free_mb} MiB free on /tmp (need ${MIN_FREE_MB})"

  # 2. Temp workspace OUTSIDE the backup tree.
  WORKDIR=$(mktemp -d /tmp/zz_rehearsal.XXXXXXXX) || { log "FAIL: mktemp"; EXIT_CODE=1; return 1; }
  chmod 700 "$WORKDIR"
  local sql="$WORKDIR/dump.sql" load="$WORKDIR/load.sql" err="$WORKDIR/load.err"

  # 3. Zip integrity — a truncated archive must fail here, not mid-load.
  if ! unzip -tqq "$ARCHIVE" >/dev/null 2>&1; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  archive fails zip integrity check: $(basename "$ARCHIVE")"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi
  log "archive zip integrity: OK"

  # 4. Extract.
  if ! unzip -p "$ARCHIVE" > "$sql" 2>"$WORKDIR/unzip.err"; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  unzip failed: $(head -c 300 "$WORKDIR/unzip.err")"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi
  log "extracted: $(wc -l < "$sql") lines, $(stat -c%s "$sql") bytes"

  # 5. Locate layout.
  if ! locate_layout "$sql"; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  could not locate \`$DB\` section in $(basename "$ARCHIVE")"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi

  # 6. Build + validate the stripped load file.
  build_load_file "$sql" "$load"
  if ! validate_load_file "$load"; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  safety gate rejected the load file (USE/CREATE DATABASE or FK guard)"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi
  log "load file: ${LOAD_TABLES} CREATE TABLE, no USE/CREATE DATABASE, FK guard intact"

  # 7. Create the scratch DB. A database with OUR monthly name can only be a
  #    leftover from an interrupted run of this script (the backup job never
  #    uses the zz_ prefix); drop it and start clean, which also makes this
  #    idempotent. Anything outside ${SCRATCH_PREFIX}<ym> was refused above.
  if mysql -N -e "SELECT 1 FROM information_schema.schemata WHERE schema_name='${SCRATCH}';" | grep -q 1; then
    log "scratch database \`${SCRATCH}\` exists (leftover from an interrupted run) — dropping it"
    SCRATCH_CREATED=1
    if ! mysql -e "DROP DATABASE \`${SCRATCH}\`;" 2>"$WORKDIR/drop.err"; then
      collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
      collect "  could not drop leftover \`${SCRATCH}\`: $(head -c 300 "$WORKDIR/drop.err")"
      EXIT_CODE=1
      return "$EXIT_CODE"
    fi
  fi
  if ! mysql -e "CREATE DATABASE \`${SCRATCH}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>"$WORKDIR/create.err"; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  could not create scratch database \`$SCRATCH\`: $(head -c 300 "$WORKDIR/create.err")"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi
  SCRATCH_CREATED=1
  log "created scratch database \`$SCRATCH\` (will be dropped on exit)"

  # 8. Re-assert: the DB we are about to load into is the scratch one.
  if [ "$(mysql -N -e "SELECT DATABASE();" "$SCRATCH" 2>/dev/null)" != "$SCRATCH" ]; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  could not select \`$SCRATCH\`"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi

  # 9. Load. REDIRECT, never pipe — the FK guard lives in the file header and a
  #    pipe loses the client's session-level parsing guarantees.
  log "loading ${LOAD_TABLES} tables into \`$SCRATCH\` ..."
  # Run the load in the background so this shell stays in charge of signals.
  # A foreground child makes bash wait inside the child: INT/TERM are handled
  # by it (or ignored) and this script's trap never runs — verified: a TERM
  # during the load left the scratch DB behind. Backgrounded + wait + forward
  # proves the trap fires. See "SIGTERM during the load" in the runbook.
  local t0; t0=$(date +%s)
  LOAD_PID=""
  timeout --foreground "$COMMAND_TIMEOUT" mysql "$SCRATCH" < "$load" 2> "$err" &
  LOAD_PID=$!
  wait_for_load "$LOAD_PID"
  local load_rc=$?
  LOAD_PID=""
  local secs=$(( $(date +%s) - t0 ))
  if [ "$load_rc" -ne 0 ]; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  mysql load exited $load_rc after ${secs}s"
    if [ -s "$err" ]; then
      collect "  error: $(head -c 600 "$err")"
    fi
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi
  if [ -s "$err" ]; then
    # Warnings are tolerable; errors are not (non-zero rc caught above).
    log "load stderr (warnings): $(head -c 400 "$err" | tr '\n' ' ')"
  fi
  log "load finished in ${secs}s, exit 0"

  # 10. Table-count check inside the scratch DB.
  local restored_tables
  restored_tables=$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables
                                 WHERE table_schema='${SCRATCH}' AND table_type='BASE TABLE';")
  local prod_tables
  prod_tables=$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables
                             WHERE table_schema='${PROD_DB}' AND table_type='BASE TABLE';")
  if [ "$restored_tables" -lt 1 ]; then
    collect "FAIL restore-rehearsal $(TZ=Asia/Manila date '+%F %H:%M %Z')"
    collect "  restored 0 tables (load reported success but database is empty)"
    EXIT_CODE=1
    return "$EXIT_CODE"
  fi

  # 11. Row-count parity (AC3).
  #
  # Two questions, because they catch different disasters:
  #
  #   (a) COUNT parity — is the archive even shaped like production? Table set
  #       identical, and every table's count within tolerance. Catches a
  #       truncated archive, a bad section cut, a half-finished load.
  #
  #   (b) FEATURE parity — prod's newest row is present in the restore
  #       (prod.max(id) <= restore.max(id)), and for every table, no row that
  #       production still has is missing from the archive (checked by primary
  #       key on a per-table time budget). This is the test that catches
  #       "everything loads fine but one table restored stale/partial", and it
  #       is the one the runbook's earlier proof used.
  #
  # PROD is allowed to be smaller than the archive. Production prunes
  # activity_logs, and rows are deleted from expense_requests and the applicant
  # identity tables; the archive legitimately holds rows prod no longer has.
  # That is not a failure — but a large excess IS flagged, because an archive
  # holding far more than prod without an explanation means the dump is not
  # the dump we think it is.
  local prod_counts="$WORKDIR/prod.counts" restored_counts="$WORKDIR/restored.counts"
  collect_row_counts "$PROD_DB"    "$prod_counts"
  collect_row_counts "$SCRATCH"    "$restored_counts"

  local pass=0 fail=0 total=0 details="" info=""
  local -A notes_by_table=()
  local -A R=() P=()
  while IFS=$'\t' read -r t n; do R["$t"]="$n"; done < "$restored_counts"
  while IFS=$'\t' read -r t n; do P["$t"]="$n"; done < "$prod_counts"

  local tables
  tables=$( { printf '%s\n' "${!P[@]}"; printf '%s\n' "${!R[@]}"; } | grep -v '^$' | sort -u )

  # Gap to investigate: how many rows restoration would need to check.
  local gap_total=0
  while read -r t; do
    [ -z "$t" ] && continue
    local p="${P[$t]-MISSING}" r="${R[$t]-MISSING}"
    case "$p$r" in *[!0-9]*) continue ;; esac
    [ "$p" -gt "$r" ] && gap_total=$((gap_total + p - r))
  done <<< "$tables"
  log "parity: primary-key gap over all tables = ${gap_total} rows (bounded work)"

  # Per-table PK check budget: ~2 min each, at least 1 h total if tables large.
  local pk_time_budget=120
  [ "$gap_total" -gt 50000 ] && pk_time_budget=300

  while read -r t; do
    [ -z "$t" ] && continue
    total=$((total + 1))
    local p="${P[$t]-MISSING}" r="${R[$t]-MISSING}" ok=1 note=""
    if [ "$p" = "MISSING" ]; then
      ok=0; note="absent in prod"
    elif [ "$r" = "MISSING" ]; then
      ok=0; note="absent in restore"
    elif [ "$r" = "ERR" ] || [ "$p" = "ERR" ]; then
      ok=0; note="count error"
    else
      # --- (a) count parity ------------------------------------------------
      case "$t" in
        jobs|failed_jobs|job_batches|cache|cache_locks|sessions|report_generated_logs)
          # Short-lived tables: churn freely, but only by a bounded amount.
          if [ "$r" -le "$p" ] && [ $((p - r)) -le "$SHORTLIVED_DRIFT_CAP" ]; then
            :
          elif [ "$r" -gt "$p" ] && [ $((r - p)) -le "$EXCEED_CAP" ]; then
            :
          else
            ok=0; note="count drift beyond ${SHORTLIVED_DRIFT_CAP}/${EXCEED_CAP} (prod=${p} restored=${r})"
          fi
          ;;
        *)
          if [ "$r" -eq "$p" ]; then
            :
          elif [ "$r" -lt "$p" ] && [ $((p - r)) -le "$LOG_DRIFT_CAP" ]; then
            : # prod gained rows after the dump — expected
          elif [ "$r" -gt "$p" ]; then
            case " $RESTORE_CAN_EXCEED_PROD " in
              *" $t "*)
                [ $((r - p)) -le "$EXCEED_CAP" ] \
                  || { ok=0; note="exceeds prod by $((r - p)) > cap ${EXCEED_CAP}"; } ;;
              *)
                ok=0; note="restore has $((r - p)) more rows than prod — not an append-only/purge table" ;;
            esac
          else
            ok=0; note="prod has $((p - r)) more rows than the archive (cap ${LOG_DRIFT_CAP})"
          fi
          ;;
      esac

      # --- (b) feature parity, only if (a) passed --------------------------
      if [ "$ok" -eq 1 ]; then
        local pk_ok
        PK_DETAIL=""; : > "$WORKDIR/pk_detail"
        if ! pk_ok=$(pk_feature_check "$t" "$pk_time_budget" "$p"); then
          case "$pk_ok" in
            ROWS_MISSING:*) ok=0; note="${pk_ok#ROWS_MISSING:}" ;;
            DDL_ERROR)      ok=0; note="primary-key check failed to run" ;;
            *)              ok=0; note="${pk_ok:-primary-key check failed}" ;;
          esac
        fi
        # pk_feature_check runs in a command substitution (its own subshell),
        # so it hands the wording back through a file, not a variable.
        [ -s "$WORKDIR/pk_detail" ] && notes_by_table["$t"]=$(cat "$WORKDIR/pk_detail")
      fi
    fi

    if [ "$ok" -eq 1 ]; then
      pass=$((pass + 1))
      [ -n "${notes_by_table[$t]:-}" ] && info="${info}$(printf '  %-42s %s' "$t" "${notes_by_table[$t]}")"$'\n'
    else
      fail=$((fail + 1))
      details="${details}$(printf '  %-42s prod=%-9s restored=%-9s %s' "$t" "$p" "$r" "$note")"$'\n'
    fi
  done <<< "$tables"

  # --- sample mode (runbook's "5 tables" acceptance) -----------------------
  local sample_line=""
  if [ "$SAMPLE_N" -gt 0 ]; then
    sample_line=$(sample_check "$SAMPLE_N")
  fi

  # 12. Verdict.
  local stamp; stamp=$(TZ=Asia/Manila date '+%F %H:%M %Z')
  local arch; arch=$(basename "$ARCHIVE")
  collect "restore-rehearsal $( [ "$fail" -eq 0 ] && echo PASS || echo FAIL ) — ${stamp}"
  collect "  archive           : ${arch}"
  collect "  dump completed on : ${DUMP_COMPLETED:-unknown}"
  collect "  scratch db        : \`$SCRATCH\` (dropped at exit)"
  collect "  tables restored   : ${restored_tables} (prod ${prod_tables}, load file ${LOAD_TABLES})"
  collect "  load              : exit 0 in ${secs}s"
  collect "  pk gap checked    : ${gap_total} rows across tables where prod > restore"
  if [ -n "$info" ]; then
    collect "  rows created after the dump (expected absent):"
    collect "$info"
  fi
  if [ -n "$sample_line" ]; then collect "$sample_line"; fi
  if [ "$fail" -eq 0 ]; then
    collect "  PARITY OK ${pass}/${total} tables"
  else
    collect "  PARITY FAIL ${fail}/${total} tables (${pass} ok):"
    collect "$details"
  fi

  if [ "$fail" -eq 0 ]; then EXIT_CODE=0; else EXIT_CODE=1; fi
  return "$EXIT_CODE"
}

main
RC=$?

# One Slack message per real run (dry-run never posts, never drops).
if [ "$DRY_RUN" -eq 0 ]; then
  if [ "$RC" -eq 0 ]; then
    post_slack "✅ *Restore rehearsal PASS* — $(TZ=Asia/Manila date '+%F %H:%M %Z')
\`\`\`
${REPORT}
\`\`\`"
  else
    post_slack "❌ *Restore rehearsal FAIL* (exit ${RC}) — $(TZ=Asia/Manila date '+%F %H:%M %Z')
\`\`\`
${REPORT}
\`\`\`"
  fi
else
  log "dry run: no slack message, no database changed"
fi

log "=== restore rehearsal finished (exit ${RC}) ==="
exit "$RC"
