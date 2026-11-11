#!/usr/bin/env bash
# Dump only the clinic_ tables (plus nothing from reserve-a-lab) to backups/.
# Credentials: use ~/.my.cnf or MYSQL_* options; do not put passwords in this file.
#   tools/clinic_backup.sh [database]    (default: dbadmin)
set -euo pipefail
DB="${1:-dbadmin}"
OUT_DIR="$(dirname "$0")/../backups"
mkdir -p "$OUT_DIR"
OUT="$OUT_DIR/clinic_$(date +%Y-%m-%d_%H-%M-%S).sql"
TABLES=$(mysql -N -e "SHOW TABLES LIKE 'clinic\_%'" "$DB")
# shellcheck disable=SC2086
mysqldump --single-transaction "$DB" $TABLES > "$OUT"
chmod 600 "$OUT"
echo "Wrote $OUT"
