#!/bin/bash
cd "$(dirname "$0")"

if docker ps --format '{{.Names}}' | grep -q '^duckphp-test84$'; then
    docker stop duckphp-test84
fi

if docker ps -a --format '{{.Names}}' | grep -q '^duckphp-test84$'; then
    docker rm duckphp-test84
    echo "duckphp-test84 removed"
else
    echo "duckphp-test84 not found"
fi
