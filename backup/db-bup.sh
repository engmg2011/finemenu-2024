#!/bin/bash

DATE=$(date +%F)
DAY=$(date +%u)
DAY_OF_MONTH=$(date +%d)

BACKUP_DIR="$HOME/backups/mysql"
TMP_FILE="$BACKUP_DIR/backup-$DATE.sql"
FINAL_FILE="$TMP_FILE.gz"

mkdir -p $BACKUP_DIR

# Dump database excluding telescope tables
# Dump database excluding telescope tables
mysqldump \
--single-transaction \
--quick \
--lock-tables=false \
--no-tablespaces \
"$MYSQL_DB" \
--ignore-table="$MYSQL_DB.telescope_entries" \
--ignore-table="$MYSQL_DB.telescope_entries_tags" \
--ignore-table="$MYSQL_DB.telescope_monitoring" \
> "$TMP_FILE"

gzip $TMP_FILE

# -----------------------------
# Cleanup Logic
# -----------------------------

find $BACKUP_DIR -name "*.gz" | while read file
do
    filename=$(basename "$file")
    filedate=$(echo $filename | grep -oE '[0-9]{4}-[0-9]{2}-[0-9]{2}')

    file_ts=$(date -d "$filedate" +%s)
    now_ts=$(date +%s)

    age_days=$(( (now_ts - file_ts) / 86400 ))

    weekday=$(date -d "$filedate" +%u)
    monthday=$(date -d "$filedate" +%d)

    keep=false

    # Keep last 3 days
    if [ $age_days -le 3 ]; then
        keep=true
    fi

    # Keep one weekly backup (Sunday)
    if [ "$weekday" = "7" ] && [ $age_days -le 14 ]; then
        keep=true
    fi

    # Keep first backup of month
    if [ "$monthday" = "01" ]; then
        keep=true
    fi

    if [ "$keep" = false ]; then
        rm -f "$file"
    fi
done
