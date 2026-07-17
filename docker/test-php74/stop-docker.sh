#!/bin/bash
cd "$(dirname "$0")"

if docker ps --format '{{.Names}}' | grep -q '^duckphp-test$'; then
    docker stop duckphp-test
    echo "duckphp-test stopped"
else
    echo "duckphp-test is not running"
fi
