#!/bin/bash
cd "$(dirname "$0")"
INSTALL_CMD="[ -f /DATA/vendor/autoload.php ] || composer install --no-interaction --prefer-dist"
if [ $# -eq 0 ]; then
    docker-compose run --rm fulltest
    exit $?
fi
docker-compose run --rm fulltest sh -c "$INSTALL_CMD && exec \$@" sh "$@"
