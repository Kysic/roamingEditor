#!/bin/bash

# Configuration
#DB_NAME=
#DB_USER=
#DB_PASSWORD=
#GPG_RECIPIENT=
#DUMP_FILE=
#DUMP_FILE_GRP=

source $HOME/.mysqldumpvinci.conf

umask 0026
mysqldump -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} | gzip | gpg --batch --yes --encrypt --recipient ${GPG_RECIPIENT} --output $DUMP_FILE
chgrp ${DUMP_FILE_GRP} $DUMP_FILE

# To uncipher: gpg --decrypt --output dump.sql.gz ciphered.sql.gz.gpg

