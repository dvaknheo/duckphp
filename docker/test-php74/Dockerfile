FROM php:7.4.33-alpine

# 安装测试所需的系统工具及 Redis 服务端
RUN apk add --no-cache git unzip curl redis

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /DATA
