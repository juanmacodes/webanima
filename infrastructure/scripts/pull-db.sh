#!/usr/bin/env bash
set -euo pipefail

: "${PROD_DB_SSH:?Define PROD_DB_SSH}" 
: "${PROD_DB_NAME:?Define PROD_DB_NAME}" 

ssh "$PROD_DB_SSH" "wp db export - --add-drop-table" > backups/$(date +%Y%m%d-%H%M)-${PROD_DB_NAME}.sql
