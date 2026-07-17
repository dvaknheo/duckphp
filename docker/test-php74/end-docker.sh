#!/bin/bash
cd "$(dirname "$0")"

if docker ps --format '{{.Names}}' | grep -q '^duckphp-test$'; then
    docker stop duckphp-test
fi

if docker ps -a --format '{{.Names}}' | grep -q '^duckphp-test$'; then
    docker rm duckphp-test
    echo "duckphp-test removed"
else
    echo "duckphp-test not found"
fi
