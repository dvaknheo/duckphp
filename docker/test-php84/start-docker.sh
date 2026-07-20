#!/bin/bash
cd "$(dirname "$0")"

if docker ps -a --format '{{.Names}}' | grep -q '^duckphp-test84$'; then
    echo "duckphp-test84 already exists"
    exit 0
fi

docker-compose run -d --name duckphp-test84 --use-aliases fulltest tail -f /dev/null

# 启动 redis，后续 exec 不需要再启动
docker exec duckphp-test84 redis-server --requirepass 123456 --daemonize yes

echo "duckphp-test84 started"
