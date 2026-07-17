#!/bin/bash
cd "$(dirname "$0")"

if docker ps -a --format '{{.Names}}' | grep -q '^duckphp-test$'; then
    echo "duckphp-test already exists"
    exit 0
fi

docker-compose run -d --name duckphp-test --use-aliases fulltest tail -f /dev/null

# 启动 redis，后续 exec 不需要再启动
docker exec duckphp-test redis-server --requirepass 123456 --daemonize yes

echo "duckphp-test started"
