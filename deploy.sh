#!/bin/sh

DEST=ldp.ovh:/var/www/vinci/

RSYNC_OPTIONS="--checksum --recursive --verbose --delete-after"

rsync $RSYNC_OPTIONS . $DEST \
    --exclude .git \
    --exclude .gitattributes \
    --exclude .gitignore \
    --exclude cr \
    --exclude api/tests \
    --exclude api/tmp \
    --exclude api/conf \
    --exclude api/conf_dev \
    --exclude api/conf_it \
    --exclude api/conf_template \
    --exclude deploy.sh \
    --exclude git-hooks \
    --exclude googlescripts \
    --exclude README.md \
    --exclude sqlscripts \
    --exclude extra \
    --exclude launchdevenv.sh

