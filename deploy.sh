#!/bin/sh

DEST=ovh-1:/var/www/vinci/

RSYNC_OPTIONS="--times --recursive --verbose --delete-after"

rsync $RSYNC_OPTIONS . $DEST \
    --exclude .git \
    --exclude .gitattributes \
    --exclude .gitignore \
    --exclude api/tests \
    --exclude api/conf \
    --exclude api/conf_dev \
    --exclude api/conf_it \
    --exclude api/conf_template \
    --exclude deploy.sh \
    --exclude git-hooks \
    --exclude googlescripts \
    --exclude README.md \
    --exclude sqlscripts

