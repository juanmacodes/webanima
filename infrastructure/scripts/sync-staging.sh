#!/usr/bin/env bash
set -euo pipefail

: "${SFTP_HOST:?Define SFTP_HOST}" 
: "${SFTP_USER:?Define SFTP_USER}" 
: "${SFTP_PATH:?Define SFTP_PATH}" 

rsync -avz --delete \
  wp-content/themes/anima-child \
  wp-content/plugins/anima-core \
  wp-content/plugins/anima-swiper-slider \
  wp-content/plugins/anima-world \
  "$SFTP_USER@$SFTP_HOST:$SFTP_PATH/wp-content/"
