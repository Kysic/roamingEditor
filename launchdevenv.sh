#!/bin/sh

SCRIPTS_DIR="$(dirname $0)"

docker-compose -f "${SCRIPTS_DIR}/extra/docker/docker-compose.yml" up

