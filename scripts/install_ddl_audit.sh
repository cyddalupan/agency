#!/usr/bin/env bash
# =============================================================================
# install_ddl_audit.sh — install the LANDAS binlog DDL watcher
# =============================================================================
# Card 6b03ebf0d602a48e11939bbe. The MySQL audit_log plugin is Enterprise-only
# and general_log logs tenant PII, so the audit trail is built from the binary
# log that MySQL already writes. Nothing here changes my.cnf or restarts mysqld.
#
# Installs:
#   /usr/local/bin/landas-ddl-audit           grep binlogs for DDL on super_landas
#   /usr/local/bin/landas-thread-map          1-minute thread_id -> user snapshot
#   /etc/cron.d/landas-ddl-audit              daily 00:10 UTC run
#   /etc/cron.d/landas-thread-map             every minute
#   /etc/logrotate.d/landas-ddl-audit         size 50M, rotate 8, compress
#   ops.thread_map                            table for the snapshots
#   /var/log/mysql/ddl-audit.log              created if missing
#
# Idempotent. Safe to re-run. Does NOT delete binlogs and does NOT touch
# super_landas.
#
# Usage: sudo scripts/install_ddl_audit.sh
# =============================================================================
set -euo pipefail

[ "$(id -u)" -eq 0 ] || { echo "run as root (sudo)" >&2; exit 1; }

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
STAMP="$(date -u +%Y%m%d-%H%M%S)"

backup() {  # backup <path> — keep the old file outside the deploy tree
    local p="$1"
    [ -e "$p" ] || return 0
    local b="/root/ops-backups/${STAMP}-$(echo "$p" | tr '/' '_')"
    mkdir -p /root/ops-backups
    cp -a "$p" "$b"
    echo "backed up $p -> $b"
}

install -m 0755 "$HERE/landas-ddl-audit"  /usr/local/bin/landas-ddl-audit.tmp
install -m 0755 "$HERE/landas-thread-map" /usr/local/bin/landas-thread-map.tmp
mv -f /usr/local/bin/landas-ddl-audit.tmp  /usr/local/bin/landas-ddl-audit
mv -f /usr/local/bin/landas-thread-map.tmp /usr/local/bin/landas-thread-map

backup /etc/cron.d/landas-ddl-audit
backup /etc/cron.d/landas-thread-map
backup /etc/logrotate.d/landas-ddl-audit

install -m 0644 -o root -g root "$HERE/landas-ddl-audit.cron"    /etc/cron.d/landas-ddl-audit
install -m 0644 -o root -g root "$HERE/landas-thread-map.cron"   /etc/cron.d/landas-thread-map
install -m 0644 -o root -g root "$HERE/landas-ddl-audit.logrotate" /etc/logrotate.d/landas-ddl-audit

# The snapshots live in their own schema, never alongside tenant data.
mysql <<'SQL'
CREATE DATABASE IF NOT EXISTS ops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS ops.thread_map (
  snap_ts     DATETIME    NOT NULL,
  thread_id   BIGINT UNSIGNED NOT NULL,
  user_host   VARCHAR(255) DEFAULT NULL,
  db_used     VARCHAR(64)  DEFAULT NULL,
  cmd         VARCHAR(32)  DEFAULT NULL,
  PRIMARY KEY (snap_ts, thread_id),
  KEY idx_thread (thread_id, snap_ts),
  KEY idx_snap (snap_ts)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL

[ -e /var/log/mysql/ddl-audit.log ] || : > /var/log/mysql/ddl-audit.log
chown root:adm /var/log/mysql/ddl-audit.log
chmod 0640 /var/log/mysql/ddl-audit.log

# One immediate snapshot so attribution works before the first cron tick.
/usr/local/bin/landas-thread-map || true

echo "installed. verify with:"
echo "  sudo /usr/local/bin/landas-ddl-audit --since \"\$(date -u -d '-2 hours' '+%F %T')\""
echo "  sudo mysql -N -e \"SELECT COUNT(*) FROM ops.thread_map WHERE snap_ts > NOW() - INTERVAL 5 MINUTE\""
