#!/bin/bash

# Configuration
#DB_NAME='vinci-prod'
#DB_USER='vinci-prod'
#DB_PASSWORD=
#DUMP_FILE='/home/nasldp/vinci-prod-dump.sql.gz.pgp'
#DUMP_FILE_GRP=nasldp

source $HOME/.mysqldumpvinci.conf

umask 0026
mysqldump -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} | gzip | gpg --yes --pinentry-mode loopback --symmetric --passphrase "${DB_PASSWORD}" --output $DUMP_FILE
chgrp ${DUMP_FILE_GRP} $DUMP_FILE

# To uncipher: gpg --decrypt --output dump.sql.gz ciphered.sql.gz.gpg
