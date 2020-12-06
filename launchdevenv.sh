#!/bin/sh

SCRIPTS_DIR="$(dirname $0)"

chmod a+w api/tmp
chmod a+w api/tests/tmp

docker-compose -f "${SCRIPTS_DIR}/extra/docker/docker-compose.yml" up

#xdg-open http://localhost:8080/

