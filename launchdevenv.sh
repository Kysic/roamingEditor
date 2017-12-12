#!/bin/sh

SCRIPTS_DIR="$(dirname $0)"

docker-compose -f "${SCRIPTS_DIR}/docker/docker-compose.yml" up

