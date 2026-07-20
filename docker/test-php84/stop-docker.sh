#!/bin/bash
cd "$(dirname "$0")"

if docker ps --format '{{.Names}}' | grep -q '^duckphp-test84$'; then
    docker stop duckphp-test84
    echo "duckphp-test84 stopped"
else
    echo "duckphp-test84 is not running"
fi
