#!/bin/bash
# 在 Docker (PHP 7.4) 环境下跑测试

cd "$(dirname "$0")"

# 安装依赖的公共逻辑
INSTALL_CMD="cp composer.lock.docker composer.lock && ([ -f /DATA/vendor/autoload.php ] || composer install --no-interaction --prefer-dist)"

# 如果没有传参数，跑 fulltest（使用 docker-compose.yml 里默认的 command）
if [ $# -eq 0 ]; then
    docker-compose run --rm fulltest
    exit $?
fi

# 有参数时，先确保依赖已安装，再执行传入的命令
docker-compose run --rm fulltest sh -c "$INSTALL_CMD && exec \$@" sh "$@"
