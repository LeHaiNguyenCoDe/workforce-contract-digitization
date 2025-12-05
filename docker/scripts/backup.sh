#!/bin/bash

# Database Backup Script
# This script backs up the MySQL database daily

set -e

BACKUP_DIR="/backup"
DB_HOST="mysql"
DB_USER="root"
DB_NAME="workforce_prod"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.sql.gz"
LOG_FILE="$BACKUP_DIR/backup.log"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Log function
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

log "Starting database backup..."

# Perform backup
if mysqldump -h "$DB_HOST" -u "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_FILE"; then
    log "✅ Backup completed successfully: $BACKUP_FILE"
    
    # Keep only last 7 days of backups
    find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +7 -delete
    log "Cleaned up old backups"
else
    log "❌ Backup failed!"
    exit 1
fi

# Verify backup
if [ -f "$BACKUP_FILE" ]; then
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log "Backup size: $SIZE"
else
    log "❌ Backup file not found!"
    exit 1
fi

log "Backup process completed"

