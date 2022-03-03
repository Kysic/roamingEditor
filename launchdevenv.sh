#!/bin/sh

SCRIPTS_DIR="$(dirname $0)"

chmod a+w api/tmp
chmod a+w api/tests/tmp

docker-compose -p "vinci" -f "${SCRIPTS_DIR}/extra/docker/docker-compose.yml" up

echo "http://localhost:8080/"

#xdg-open http://localhost:8080/
